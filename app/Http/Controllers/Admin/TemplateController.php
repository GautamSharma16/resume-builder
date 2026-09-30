<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function __construct(private readonly PdfConversionService $pdf) {}

    // ── List ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $type = $request->query('type');
        $type = in_array($type, ['resume', 'cover_letter'], true) ? $type : null;

        return view('admin.templates.index', [
            'templates' => Template::query()
                ->when($type, fn ($q) => $q->where('type', $type))
                ->where(function ($query) {
                    $query->where('type', '!=', 'resume')
                        ->orWhere('category', '!=', 'word');
                })
                ->latest()
                ->get(),
            'filterType' => $type,
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

        // Custom Profile Photo Placeholder inside template
        if ($request->hasFile('template_profile_image')) {
            $photoPath = $request->file('template_profile_image')->store('template-profile-photos', 'public');
            $photoUrl  = Storage::url($photoPath);

            $sampleData = $data['sample_data'] ?? [];
            $sampleData['profile_image']     = $photoUrl;
            $sampleData['profile_image_url'] = $photoUrl;
            $sampleData['profile_image_tag'] = '<img src="' . $photoUrl . '" class="tpl-profile-img" style="width:100%;height:100%;object-fit:cover;">';
            $sampleData['photo']             = $photoUrl;

            $data['sample_data'] = $sampleData;
        }

        // PDF upload → convert to HTML (overrides manually typed HTML)
        if ($request->hasFile('pdf_file')) {
            $request->validate([
                'pdf_file' => ['file', 'mimetypes:application/pdf', 'max:20480'],
            ]);

            $pdfStorePath  = $request->file('pdf_file')->store('template-pdfs', 'public');
            $data['pdf_path'] = $pdfStorePath;
            $data['html']     = $this->editableHtmlForUpload(
                $data,
                $this->pdf->pdfToHtml(
                    Storage::disk('public')->path($pdfStorePath)
                )
            );
        }

        $data['slug']       = Str::slug($data['name']) . '-' . Str::random(5);
        $data['created_by'] = $request->user()->id;

        Template::create($data);

        return redirect()->route('admin.templates.index', ['type' => $data['type'] ?? null])
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

        // Custom Profile Photo Placeholder inside template
        if ($request->hasFile('template_profile_image')) {
            $photoPath = $request->file('template_profile_image')->store('template-profile-photos', 'public');
            $photoUrl  = Storage::url($photoPath);

            $sampleData = $data['sample_data'] ?? ($template->sample_data ?? []);
            $sampleData['profile_image']     = $photoUrl;
            $sampleData['profile_image_url'] = $photoUrl;
            $sampleData['profile_image_tag'] = '<img src="' . $photoUrl . '" class="tpl-profile-img" style="width:100%;height:100%;object-fit:cover;">';
            $sampleData['photo']             = $photoUrl;

            $data['sample_data'] = $sampleData;
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
            $data['html']     = $this->editableHtmlForUpload(
                $data,
                $this->pdf->pdfToHtml(
                    Storage::disk('public')->path($pdfStorePath)
                )
            );
        }

        $template->update($data);

        return redirect()->route('admin.templates.index', ['type' => $template->type])
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
                    $html = (string) $renderer->renderResume($template, null, false);
                }
            } catch (\Throwable $e) {
                // If rendering fails (e.g. syntax error in generated Blade), show raw with error
                $html = '<div style="background:#fee2e2;padding:1rem;color:#991b1b;font-family:sans-serif">Preview Render Error: ' . $e->getMessage() . '</div>' . $html;
            }
        } else {
            $renderer = app(\App\Services\TemplateRenderService::class);
            if (blank(trim($html))) {
                $html = $template->type === 'cover_letter'
                    ? $renderer->editableCoverLetterTemplateHtml()
                    : $renderer->editableResumeTemplateHtml();
            }
            if ($template->type === 'cover_letter') {
                $html = (string) $renderer->renderCoverLetter($template, $renderer->getSampleDataForTemplate($template));
            } else {
                $html = (string) $renderer->renderResume($template, $renderer->getSampleDataForTemplate($template), false);
            }
        }

        return response(
            view('templates.rendered-document', ['html' => $html])->render()
        )->header('Content-Type', 'text/html; charset=UTF-8');
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

    // ── Delete ────────────────────────────────────────────────────────────
    public function destroy(Template $template)
    {
        if ($template->preview_image) {
            Storage::disk('public')->delete($template->preview_image);
        }

        if ($template->pdf_path) {
            Storage::disk('public')->delete($template->pdf_path);
        }

        $template->delete();

        return redirect()->route('admin.templates.index', ['type' => $template->type])
            ->with('status', 'Template deleted.');
    }

    // ── Shared validation ─────────────────────────────────────────────────
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type'              => ['required', 'in:resume,cover_letter'],
            'name'              => ['required', 'string', 'max:160'],
            'category'          => ['required', 'string', 'max:80'],
            'html'              => ['nullable', 'string'],
            'is_active'         => ['nullable', 'boolean'],
            'has_image'         => ['nullable', 'boolean'],
            'sample_data_json'          => ['nullable', 'string'],
            'preview_image'             => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'template_profile_image'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]) + ['is_active' => false, 'has_image' => false];

        if (($data['type'] ?? null) === 'resume' && ($data['category'] ?? null) === 'word') {
            abort(422, 'MS Word resume category has been removed.');
        }

        $allowedCategories = [
            'resume' => ['ats', 'fresher', 'experienced'],
            'cover_letter' => ['professional', 'modern', 'executive', 'fresher', 'career-change', 'minimal'],
        ];

        if (! in_array($data['category'], $allowedCategories[$data['type']] ?? [], true)) {
            abort(422, 'Selected category is not valid for this template type.');
        }

        $sampleData = null;
        if ($request->filled('sample_data_json')) {
            $decoded = json_decode($request->input('sample_data_json'), true);
            if (is_array($decoded)) {
                $sampleData = $decoded;
            }
        }

        unset($data['sample_data_json']);
        $data['sample_data'] = $sampleData;

        return $data;
    }

    private function editableHtmlForUpload(array $data, string $html): string
    {
        $renderer = app(TemplateRenderService::class);

        if (filled(trim($html))) {
            return $html;
        }

        if (($data['type'] ?? null) === 'cover_letter') {
            return $renderer->editableCoverLetterTemplateHtml();
        }

        return $renderer->editableResumeTemplateHtml();
    }
}
