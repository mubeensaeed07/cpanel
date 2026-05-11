@extends('layouts.admin_app')

@section('title', $moduleName)

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">{{ $moduleName }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Posts table</div>
                </div>
                <div class="card-body">
                    <p class="fs-15 mb-0">{{ $moduleKey === 'movies' ? 'movies' : ($moduleKey === 'series' ? 'series' : ($moduleKey === 'web_tv' ? 'web_tvs' : $moduleKey)) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Download links / files table</div>
                </div>
                <div class="card-body">
                    @php
                        $tableNames = [
                            'movies' => 'movie_download_links',
                            'series' => 'series_download_links',
                            'web_tv' => 'web_tv_download_links',
                            'wallpapers' => 'wallpaper_download_links',
                            'games' => 'game_download_links',
                            'software' => 'software_download_links',
                            'applications' => 'application_download_links',
                        ];
                    @endphp
                    <p class="fs-15 mb-0">{{ $tableNames[$moduleKey] }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
