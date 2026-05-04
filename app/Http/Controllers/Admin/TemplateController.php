<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\PdfConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function __construct(private readonly PdfConversionService $pdf) {}

    // ── List ──────────────────────────────────────────────────────────────
    public function index()
    {
        return view('admin.templates.index', [
            'templates' => Template::latest()->get(),
        ]);
    }

    // ── Create (blank form) ───────────────────────────────────────────────
    public function create()
    {
        return view('admin.templates.create', ['template' => new Template()]);
    }

    // ── Store ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Optional preview image
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image')
                ->store('template-previews', 'public');
        }

        // PDF upload → convert to HTML (overrides manually typed HTML)
        if ($request->hasFile('pdf_file')) {
            $request->validate([
                'pdf_file' => ['file', 'mimetypes:application/pdf', 'max:20480'],
            ]);

            $pdfStorePath  = $request->file('pdf_file')->store('template-pdfs', 'public');
            $data['pdf_path'] = $pdfStorePath;
            $data['html']     = $this->pdf->pdfToHtml(
                Storage::disk('public')->path($pdfStorePath)
            );
        }

        $data['slug']       = Str::slug($data['name']) . '-' . Str::random(5);
        $data['created_by'] = $request->user()->id;

        Template::create($data);

        return redirect()->route('admin.templates.index')
            ->with('status', 'Template created.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────
    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    // ── Update (PATCH — matches your existing route resource) ─────────────
    public function update(Request $request, Template $template)
    {
        $data = $this->validated($request);

        if ($request->hasFile('preview_image')) {
            if ($template->preview_image) {
                Storage::disk('public')->delete($template->preview_image);
            }
            $data['preview_image'] = $request->file('preview_image')
                ->store('template-previews', 'public');
        }

        // Replace PDF → re-convert
        if ($request->hasFile('pdf_file')) {
            $request->validate([
                'pdf_file' => ['file', 'mimetypes:application/pdf', 'max:20480'],
            ]);

            if ($template->pdf_path) {
                Storage::disk('public')->delete($template->pdf_path);
            }

            $pdfStorePath     = $request->file('pdf_file')->store('template-pdfs', 'public');
            $data['pdf_path'] = $pdfStorePath;
            $data['html']     = $this->pdf->pdfToHtml(
                Storage::disk('public')->path($pdfStorePath)
            );
        }

        $template->update($data);

        return redirect()->route('admin.templates.index')
            ->with('status', 'Template updated.');
    }

    // ── Preview — streams HTML into an <iframe> ───────────────────────────
    public function preview(Template $template)
    {
        $html = $template->html ?? '<p style="font-family:sans-serif;padding:2rem">No HTML content yet.</p>';

        // If it contains Blade tags, try to render it with dummy data
        if (str_contains($html, '{{') || str_contains($html, '@foreach')) {
            try {
                $renderer = app(\App\Services\TemplateRenderService::class);

                if ($template->type === 'cover_letter') {
                    $html = (string) $renderer->renderCoverLetter($template);
                } else {
                    $html = (string) $renderer->renderResume($template);
                }
            } catch (\Throwable $e) {
                // If rendering fails (e.g. syntax error in generated Blade), show raw with error
                $html = '<div style="background:#fee2e2;padding:1rem;color:#991b1b;font-family:sans-serif">Preview Render Error: ' . $e->getMessage() . '</div>' . $html;
            }
        }

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }


    // ── Download — converts current (edited) HTML → PDF ──────────────────
    public function download(Template $template)
    {
        if (blank($template->html)) {
            return back()->with('error', 'This template has no HTML to export.');
        }

        try {
            $pdfBytes = $this->pdf->htmlToPdf($template->html);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $filename = Str::slug($template->name) . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($pdfBytes),
        ]);
    }

    // ── Shared validation ─────────────────────────────────────────────────
    private function validated(Request $request): array
    {
        return $request->validate([
            'type'          => ['required', 'in:resume,cover_letter'],
            'name'          => ['required', 'string', 'max:160'],
            'category'      => ['required', 'string', 'max:80'],
            'html'          => ['nullable', 'string'],
            'is_active'     => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}