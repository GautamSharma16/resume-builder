<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        return view('admin.templates.index', ['templates' => Template::latest()->get()]);
    }

    public function create()
    {
        return view('admin.templates.create', ['template' => new Template()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image')->store('template-previews', 'public');
        }
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);
        $data['created_by'] = $request->user()->id;
        Template::create($data);

        return redirect()->route('admin.templates.index')->with('status', 'Template created.');
    }

    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $this->validated($request);
        if ($request->hasFile('preview_image')) {
            if ($template->preview_image) {
                Storage::disk('public')->delete($template->preview_image);
            }
            $data['preview_image'] = $request->file('preview_image')->store('template-previews', 'public');
        }

        $template->update($data);

        return redirect()->route('admin.templates.index')->with('status', 'Template updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:resume,cover_letter'],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'html' => ['nullable', 'string'],
            'preview_image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
