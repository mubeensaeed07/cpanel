@extends('layouts.app')

@section('title', 'Google Drive Integration')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Google Drive Integration</h1>
            <div class="text-muted fs-13">Connect Super Admin Google Drive once, then map/fetch content into any admin panel.</div>
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
                <div class="alert alert-success mb-4">
                    <div><strong>Connected:</strong> {{ $connection->google_email ?? 'Google account' }}</div>
                    <div class="small mt-1">Name: {{ $connection->google_name ?? 'N/A' }}</div>
                    <div class="small">Connected at: {{ $connection->updated_at?->format('d M Y h:i A') }}</div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('integrations.google-drive.redirect') }}" class="btn btn-outline-primary">Reconnect Google Drive</a>
                    <form method="POST" action="{{ route('integrations.google-drive.disconnect') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Disconnect</button>
                    </form>
                </div>
            @else
                <div class="alert alert-warning mb-4">
                    Google Drive is not connected yet.
                </div>
                <a href="{{ route('integrations.google-drive.redirect') }}" class="btn btn-primary">Connect Google Drive</a>
            @endif

            <div class="mt-4 text-muted fs-13">
                <div>Required .env values:</div>
                <ul class="mb-0 mt-2">
                    <li><code>GOOGLE_DRIVE_CLIENT_ID</code></li>
                    <li><code>GOOGLE_DRIVE_CLIENT_SECRET</code></li>
                    <li><code>GOOGLE_DRIVE_REDIRECT_URI</code> (this route callback URL)</li>
                    <li><code>GOOGLE_DRIVE_ROOT_FOLDER_ID</code> (base folder to import)</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
