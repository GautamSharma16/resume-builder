<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Template;
use App\Services\TemplateRenderService;

class PageController extends Controller
{
    public function templates(TemplateRenderService $renderer)
    {
        $templates = Template::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $rendered = [];
        foreach ($templates as $template) {
            if ($template->type === 'resume') {
                $rendered[$template->id] = (string) $renderer->renderResume($template);
            } else {
                $rendered[$template->id] = (string) $renderer->renderCoverLetter($template);
            }
        }

        return view('pages.templates', [
            'templates' => $templates,
            'rendered' => $rendered,
        ]);
    }

    public function interview()
    {
        return view('pages.interview', [
            'articles' => Article::where('is_published', true)->latest('published_at')->get(),
        ]);
    }
}
