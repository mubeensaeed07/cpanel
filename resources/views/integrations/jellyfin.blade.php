@extends('layouts.app')

@section('title', 'Jellyfin Integration')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Jellyfin Integration</h1>
            <div class="text-muted fs-13">Laravel will trigger scan/sync while Jellyfin handles metadata and media indexing.</div>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            @if($connection)
                <div class="alert alert-success">
                    <div><strong>Connected server:</strong> {{ $connection->server_name ?: 'Jellyfin' }}</div>
                    <div class="small mt-1">URL: {{ $connection->server_url }}</div>
                    <div class="small">User ID: {{ $connection->user_id }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('integrations.jellyfin.save') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Jellyfin Server URL</label>
                    <input type="url" name="server_url" class="form-control" required value="{{ old('server_url', $connection?->server_url) }}" placeholder="http://127.0.0.1:8096">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jellyfin User ID</label>
                    <input type="text" name="user_id" class="form-control" required value="{{ old('user_id', $connection?->user_id) }}" placeholder="Jellyfin user id">
                </div>
                <div class="col-12">
                    <label class="form-label">Jellyfin API Key</label>
                    <input type="text" name="api_key" class="form-control" required placeholder="{{ $connection ? 'Leave same or replace' : 'Paste API key' }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Connection</button>
                    @if($connection)
                        <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('jellyfin-disconnect').submit();">Disconnect</button>
                    @endif
                </div>
            </form>
            @if($connection)
                <form id="jellyfin-disconnect" method="POST" action="{{ route('integrations.jellyfin.disconnect') }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
@endsection
