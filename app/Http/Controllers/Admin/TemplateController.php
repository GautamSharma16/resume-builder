<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
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
        $template->update($this->validated($request));

        return redirect()->route('admin.templates.index')->with('status', 'Template updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:resume,cover_letter'],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'html' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
