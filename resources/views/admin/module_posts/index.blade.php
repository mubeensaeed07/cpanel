@extends('layouts.admin_app')

@section('title', $title)

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">{{ $title }}</h1>
            <div class="text-muted fs-13">Manage entries and download links.</div>
        </div>
        <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary">Add {{ $entitySingular }}</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-nowrap table-bordered">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Links</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->title }}</td>
                            <td><code class="text-muted">{{ $record->slug }}</code></td>
                            <td><span class="badge bg-{{ $record->status === 'published' ? 'success' : 'secondary' }}-transparent">{{ $record->status }}</span></td>
                            <td>{{ $record->download_links_count }}</td>
                            <td class="text-end">
                                <a href="{{ route($routePrefix.'.edit', [$routeParam => $record->getKey()]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route($routePrefix.'.destroy', [$routeParam => $record->getKey()]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this entry and all its download links?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No entries yet. Click &ldquo;Add {{ $entitySingular }}&rdquo; to create one.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection
