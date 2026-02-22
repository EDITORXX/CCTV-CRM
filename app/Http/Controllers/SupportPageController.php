<?php

namespace App\Http\Controllers;

use App\Models\SupportArticle;
use App\Models\SupportVideo;
use Illuminate\Http\Request;

class SupportPageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $brand = $request->input('brand');

        $articlesQuery = SupportArticle::published()->latest();
        $videosQuery = SupportVideo::published()->latest();

        if ($search) {
            $articlesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
            $videosQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brand) {
            $articlesQuery->where('brand', $brand);
            $videosQuery->where('brand', $brand);
        }

        $faqs = (clone $articlesQuery)->faqs()->get();
        $guides = (clone $articlesQuery)->guides()->get();
        $videos = $videosQuery->get();

        $brands = collect()
            ->merge(SupportArticle::published()->whereNotNull('brand')->distinct()->pluck('brand'))
            ->merge(SupportVideo::published()->whereNotNull('brand')->distinct()->pluck('brand'))
            ->unique()->sort()->values();

        return view('support.index', compact('faqs', 'guides', 'videos', 'brands', 'search', 'brand'));
    }
}
