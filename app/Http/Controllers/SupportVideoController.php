<?php

namespace App\Http\Controllers;

use App\Models\SupportVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportVideoController extends Controller
{
    public function index()
    {
        $videos = SupportVideo::with('creator')->latest()->get();
        return view('support-videos.index', compact('videos'));
    }

    public function create()
    {
        $brands = SupportVideo::whereNotNull('brand')
            ->distinct()->pluck('brand')->sort()->values();
        return view('support-videos.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'video_url' => 'required_without:video_file|nullable|url|max:500',
            'video_file' => 'required_without:video_url|nullable|file|mimes:mp4,webm,mov|max:102400',
            'thumbnail_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = collect($validated)->only(['title', 'description', 'brand', 'category'])->toArray();
        $data['is_published'] = $request->has('is_published');
        $data['created_by'] = auth()->id();

        if ($request->hasFile('video_file')) {
            $data['video_url'] = $request->file('video_file')->store('support-videos', 'public');
        } else {
            $data['video_url'] = $validated['video_url'];
        }

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = $request->file('thumbnail_file')->store('support-thumbnails', 'public');
        }

        SupportVideo::create($data);

        return redirect()->route('support-videos.index')->with('success', 'Video added.');
    }

    public function edit(SupportVideo $supportVideo)
    {
        $brands = SupportVideo::whereNotNull('brand')
            ->distinct()->pluck('brand')->sort()->values();
        return view('support-videos.edit', compact('supportVideo', 'brands'));
    }

    public function update(Request $request, SupportVideo $supportVideo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimes:mp4,webm,mov|max:102400',
            'thumbnail_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = collect($validated)->only(['title', 'description', 'brand', 'category'])->toArray();
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('video_file')) {
            if ($supportVideo->video_url && !str_starts_with($supportVideo->video_url, 'http')) {
                Storage::disk('public')->delete($supportVideo->video_url);
            }
            $data['video_url'] = $request->file('video_file')->store('support-videos', 'public');
        } elseif ($request->filled('video_url')) {
            if ($supportVideo->video_url && !str_starts_with($supportVideo->video_url, 'http')) {
                Storage::disk('public')->delete($supportVideo->video_url);
            }
            $data['video_url'] = $validated['video_url'];
        }

        if ($request->hasFile('thumbnail_file')) {
            if ($supportVideo->thumbnail) {
                Storage::disk('public')->delete($supportVideo->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail_file')->store('support-thumbnails', 'public');
        }

        $supportVideo->update($data);

        return redirect()->route('support-videos.index')->with('success', 'Video updated.');
    }

    public function destroy(SupportVideo $supportVideo)
    {
        if ($supportVideo->video_url && !str_starts_with($supportVideo->video_url, 'http')) {
            Storage::disk('public')->delete($supportVideo->video_url);
        }
        if ($supportVideo->thumbnail) {
            Storage::disk('public')->delete($supportVideo->thumbnail);
        }
        $supportVideo->delete();

        return redirect()->route('support-videos.index')->with('success', 'Video deleted.');
    }
}
