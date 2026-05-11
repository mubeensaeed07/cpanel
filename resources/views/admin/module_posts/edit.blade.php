@extends('layouts.admin_app')

@section('title', 'Edit '.$entitySingular)

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Edit {{ $entitySingular }}</h1>
            <div class="text-muted fs-13">{{ $record->title }}</div>
        </div>
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            <form action="{{ route($routePrefix.'.update', [$routeParam => $record->getKey()]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $record->title) }}" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" @selected(old('status', $record->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $record->status) === 'published')>Published</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (optional)</label>
                        <input type="text" name="slug" value="{{ old('slug', $record->slug) }}" class="form-control" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $record->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="mb-2">Download links</h6>
                    <p class="text-muted fs-13 mb-2">Saving replaces all links below. Empty URL rows are ignored.</p>
                    @include('admin.module_posts._download_rows', ['rows' => $downloadRows])
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
