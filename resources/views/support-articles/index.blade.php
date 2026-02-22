@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Knowledge Base</h4>
        <p class="text-muted mb-0">Manage FAQs and troubleshooting guides for customers</p>
    </div>
    <a href="{{ route('support-articles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Article
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="articlesTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ Str::limit($article->title, 50) }}</td>
                        <td>
                            @if($article->type === 'faq')
                                <span class="badge bg-info">FAQ</span>
                            @else
                                <span class="badge bg-primary">Guide</span>
                            @endif
                        </td>
                        <td>{{ $article->brand ?? '—' }}</td>
                        <td>{{ $article->category ?? '—' }}</td>
                        <td>
                            @if($article->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>{{ $article->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('support-articles.edit', $article) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('support-articles.destroy', $article) }}" method="POST"
                                      onsubmit="return confirm('Delete this article?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#articlesTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[6, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-journal-text fs-1 d-block mb-2"></i>No articles yet. <a href="{{ route('support-articles.create') }}">Create your first article</a>.</div>'
            }
        });
    });
</script>
@endsection
