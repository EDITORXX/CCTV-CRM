@extends('layouts.app')

@section('title', 'Help Center')

@section('styles')
<style>
    .support-hero {
        background: linear-gradient(135deg, var(--bs-primary) 0%, #4f46e5 100%);
        border-radius: .75rem;
        color: #fff;
    }
    .video-card {
        transition: transform .15s ease, box-shadow .15s ease;
        cursor: pointer;
    }
    .video-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12) !important;
    }
    .video-thumb {
        position: relative;
        overflow: hidden;
        border-radius: .5rem .5rem 0 0;
        background: #000;
    }
    .video-thumb img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        opacity: .85;
    }
    .video-thumb .play-icon {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%,-50%);
        font-size: 2.5rem;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,.4);
    }
    .guide-card {
        transition: box-shadow .15s ease;
    }
    .guide-card:hover {
        box-shadow: 0 .25rem 1rem rgba(0,0,0,.1) !important;
    }
</style>
@endsection

@section('content')

{{-- Hero / Search --}}
<div class="support-hero p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <h3 class="fw-bold mb-1"><i class="bi bi-life-preserver me-2"></i>Help Center</h3>
            <p class="mb-0 opacity-75">Search FAQs, guides, and video tutorials to solve problems quickly</p>
        </div>
        <div class="col-lg-6">
            <form action="{{ route('support.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0" name="q" value="{{ $search }}"
                           placeholder="Search for help... e.g. date time setting, mobile app">
                </div>
                <select name="brand" class="form-select bg-white border-0" style="max-width:180px;">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ $brand === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light fw-semibold">Search</button>
                @if($search || $brand)
                    <a href="{{ route('support.index') }}" class="btn btn-outline-light">Clear</a>
                @endif
            </form>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#faqTab" type="button">
            <i class="bi bi-question-circle me-1"></i> FAQs
            <span class="badge bg-primary ms-1">{{ $faqs->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guidesTab" type="button">
            <i class="bi bi-journal-text me-1"></i> Guides
            <span class="badge bg-primary ms-1">{{ $guides->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#videosTab" type="button">
            <i class="bi bi-play-circle me-1"></i> Videos
            <span class="badge bg-primary ms-1">{{ $videos->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- FAQ Tab --}}
    <div class="tab-pane fade show active" id="faqTab">
        @if($faqs->count())
        <div class="accordion" id="faqAccordion">
            @foreach($faqs as $faq)
            <div class="accordion-item border-0 shadow-sm mb-2 rounded">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                        {{ $faq->title }}
                        @if($faq->brand)
                            <span class="badge bg-secondary ms-2 fw-normal">{{ $faq->brand }}</span>
                        @endif
                    </button>
                </h2>
                <div id="faq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        {!! $faq->content !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-question-circle fs-1 d-block mb-2"></i>
            <p>No FAQs found{{ $search ? ' for "' . e($search) . '"' : '' }}.</p>
        </div>
        @endif
    </div>

    {{-- Guides Tab --}}
    <div class="tab-pane fade" id="guidesTab">
        @if($guides->count())
        <div class="row g-3">
            @foreach($guides as $guide)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm guide-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0">{{ $guide->title }}</h6>
                            @if($guide->brand)
                                <span class="badge bg-secondary ms-2 text-nowrap">{{ $guide->brand }}</span>
                            @endif
                        </div>
                        @if($guide->category)
                            <span class="badge bg-light text-dark mb-2">{{ $guide->category }}</span>
                        @endif
                        <div class="text-muted small guide-preview" style="max-height:80px;overflow:hidden;">
                            {!! Str::limit(strip_tags($guide->content), 200) !!}
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-3" type="button"
                                data-bs-toggle="modal" data-bs-target="#guideModal{{ $guide->id }}">
                            Read More <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Guide Detail Modal --}}
            <div class="modal fade" id="guideModal{{ $guide->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">{{ $guide->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @if($guide->brand)
                                <span class="badge bg-secondary mb-3">{{ $guide->brand }}</span>
                            @endif
                            @if($guide->category)
                                <span class="badge bg-light text-dark mb-3">{{ $guide->category }}</span>
                            @endif
                            <div class="mt-2">{!! $guide->content !!}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
            <p>No guides found{{ $search ? ' for "' . e($search) . '"' : '' }}.</p>
        </div>
        @endif
    </div>

    {{-- Videos Tab --}}
    <div class="tab-pane fade" id="videosTab">
        @if($videos->count())
        <div class="row g-3">
            @foreach($videos as $video)
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm video-card h-100" data-bs-toggle="modal" data-bs-target="#videoModal{{ $video->id }}">
                    <div class="video-thumb">
                        @if($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-dark" style="height:160px;">
                                <i class="bi bi-camera-video text-white fs-1"></i>
                            </div>
                        @endif
                        <div class="play-icon"><i class="bi bi-play-circle-fill"></i></div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-1 small">{{ Str::limit($video->title, 55) }}</h6>
                        <div class="d-flex gap-1 flex-wrap">
                            @if($video->brand)
                                <span class="badge bg-secondary" style="font-size:.7rem;">{{ $video->brand }}</span>
                            @endif
                            @if($video->category)
                                <span class="badge bg-light text-dark" style="font-size:.7rem;">{{ $video->category }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Video Player Modal --}}
            <div class="modal fade" id="videoModal{{ $video->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0">
                            <h6 class="modal-title text-white fw-semibold">{{ $video->title }}</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            @if($video->is_youtube)
                                <div class="ratio ratio-16x9">
                                    <iframe src="{{ $video->embed_url }}" allowfullscreen
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                </div>
                            @else
                                <video controls class="w-100" style="max-height:70vh;">
                                    <source src="{{ asset('storage/' . $video->video_url) }}" type="video/mp4">
                                    Your browser does not support video playback.
                                </video>
                            @endif
                        </div>
                        @if($video->description)
                        <div class="modal-footer border-0">
                            <p class="text-white-50 small mb-0 w-100">{{ $video->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-camera-video fs-1 d-block mb-2"></i>
            <p>No videos found{{ $search ? ' for "' . e($search) . '"' : '' }}.</p>
        </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Stop YouTube playback when modal closes
    $(document).on('hidden.bs.modal', function (e) {
        $(e.target).find('iframe').each(function() {
            var src = $(this).attr('src');
            $(this).attr('src', '');
            $(this).attr('src', src);
        });
        $(e.target).find('video').each(function() {
            this.pause();
        });
    });
</script>
@endsection
