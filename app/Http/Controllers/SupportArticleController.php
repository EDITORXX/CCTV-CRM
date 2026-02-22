<?php

namespace App\Http\Controllers;

use App\Models\SupportArticle;
use Illuminate\Http\Request;

class SupportArticleController extends Controller
{
    public function index()
    {
        $articles = SupportArticle::with('creator')->latest()->get();
        return view('support-articles.index', compact('articles'));
    }

    public function create()
    {
        $brands = SupportArticle::whereNotNull('brand')
            ->distinct()->pluck('brand')->sort()->values();
        return view('support-articles.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:faq,guide',
            'brand' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['created_by'] = auth()->id();

        SupportArticle::create($validated);

        return redirect()->route('support-articles.index')->with('success', 'Article created.');
    }

    public function edit(SupportArticle $supportArticle)
    {
        $brands = SupportArticle::whereNotNull('brand')
            ->distinct()->pluck('brand')->sort()->values();
        return view('support-articles.edit', compact('supportArticle', 'brands'));
    }

    public function update(Request $request, SupportArticle $supportArticle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:faq,guide',
            'brand' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $supportArticle->update($validated);

        return redirect()->route('support-articles.index')->with('success', 'Article updated.');
    }

    public function destroy(SupportArticle $supportArticle)
    {
        $supportArticle->delete();
        return redirect()->route('support-articles.index')->with('success', 'Article deleted.');
    }
}
