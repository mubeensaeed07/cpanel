@extends('layouts.admin_app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Dashboard</h1>
            <div class="text-muted fs-13">Counts and activity are for your account only (content you created).</div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Assigned modules</p>
                            <h3 class="fw-semibold mb-0">{{ $stats['modules'] }}</h3>
                        </div>
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="bx bx-grid-alt fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Total entries</p>
                            <h3 class="fw-semibold mb-0">{{ number_format($stats['total_items']) }}</h3>
                        </div>
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="bx bx-layer fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h2 class="h6 fw-semibold mb-3 border-start border-primary border-3 ps-2">Your modules</h2>
        <div class="row g-3">
            @forelse($moduleCards as $card)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ $card['href'] }}" class="text-decoration-none">
                        <div class="card custom-card h-100 shadow-none border border-primary border-opacity-25">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-1 text-muted fs-13">{{ $card['name'] }}</p>
                                        <h3 class="fw-semibold mb-0 text-fixed-white">{{ number_format($card['count']) }}</h3>
                                        <span class="fs-12 text-primary">Open module →</span>
                                    </div>
                                    <span class="avatar avatar-md {{ $card['avatar_class'] }}">
                                        <i class="bx {{ $card['icon'] }} fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">No modules assigned. Contact Super Admin.</div>
                </div>
            @endforelse
        </div>
    </div>

    @if(count($chartDatasets))
        <div class="card custom-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="card-title mb-0">Monthly posting activity</div>
                <span class="fs-12 text-muted mb-0">{{ $chartRangeLabel }}</span>
            </div>
            <div class="card-body">
                <div style="height: 320px; position: relative;">
                    <canvas id="adminMonthlyChart"></canvas>
                </div>
            </div>
        </div>

        <script src="{{ asset('theme-assets/libs/chart.js/chart.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('adminMonthlyChart');
                if (!el || typeof Chart === 'undefined') {
                    return;
                }
                const tickColor = 'rgba(173, 181, 189, 0.9)';
                const gridColor = 'rgba(255, 255, 255, 0.06)';
                new Chart(el.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: @json($chartDatasets),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: tickColor, boxWidth: 12, padding: 16 },
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            },
                        },
                        scales: {
                            x: {
                                stacked: false,
                                ticks: { color: tickColor, maxRotation: 45, minRotation: 0 },
                                grid: { color: gridColor },
                            },
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                ticks: {
                                    color: tickColor,
                                    precision: 0,
                                },
                                grid: { color: gridColor },
                            },
                        },
                    },
                });
            });
        </script>
    @endif
@endsection
