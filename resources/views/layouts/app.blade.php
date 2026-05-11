<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="dark" data-header-styles="dark" data-menu-styles="dark" data-toggled="close">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Super Admin Panel')</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="icon" href="{{ asset('theme-assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">
    <link id="style" href="{{ asset('theme-assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme-assets/icon-fonts/icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('theme-assets/app-b83ce806.css') }}">
    <script src="{{ asset('theme-assets/main.js') }}"></script>
</head>
<body>
<div class="page">
    <header class="app-header">
        <div class="main-header-container container-fluid">
            <div class="header-content-left">
                <div class="header-element">
                    <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                </div>
            </div>
            <div class="header-content-right">
                <div class="header-element header-theme-mode">
                    <a href="javascript:void(0);" class="header-link layout-setting">
                        <span class="light-layout"><i class="bx bx-moon header-link-icon"></i></span>
                        <span class="dark-layout"><i class="bx bx-sun header-link-icon"></i></span>
                    </a>
                </div>
                <div class="header-element">
                    <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <div class="me-sm-2 me-0">
                                <span class="avatar avatar-sm bg-primary-transparent avatar-rounded">
                                    <i class="bx bx-user fs-16"></i>
                                </span>
                            </div>
                            <div class="d-sm-block d-none">
                                <p class="fw-semibold mb-0 lh-1">Super Admin</p>
                                <span class="op-7 fw-normal d-block fs-11">{{ session('super_admin_email') }}</span>
                            </div>
                        </div>
                    </a>
                    <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex">
                                    <i class="ti ti-logout fs-18 me-2 op-7"></i>Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <aside class="app-sidebar sticky" id="sidebar">
        <div class="main-sidebar-header">
            <a href="{{ route('dashboard') }}" class="header-logo">
                <img src="{{ asset('theme-assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                <img src="{{ asset('theme-assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
                <img src="{{ asset('theme-assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
                <img src="{{ asset('theme-assets/images/brand-logos/toggle-dark.png') }}" alt="logo" class="toggle-dark">
            </a>
        </div>
        <div class="main-sidebar" id="sidebar-scroll">
            <nav class="main-menu-container nav nav-pills flex-column sub-open">
                <ul class="main-menu">
                    <li class="slide__category"><span class="category-name">Main</span></li>
                    <li class="slide">
                        <a href="{{ route('dashboard') }}" class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bx bx-home side-menu__icon"></i>
                            <span class="side-menu__label">Dashboard</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{ route('admins.index') }}" class="side-menu__item {{ request()->routeIs('admins.*') ? 'active' : '' }}">
                            <i class="bx bx-user-plus side-menu__icon"></i>
                            <span class="side-menu__label">Admins</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{ route('integrations.google-drive.index') }}" class="side-menu__item {{ request()->routeIs('integrations.google-drive.*') ? 'active' : '' }}">
                            <i class="bx bxl-google side-menu__icon"></i>
                            <span class="side-menu__label">Google Drive</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{ route('integrations.jellyfin.index') }}" class="side-menu__item {{ request()->routeIs('integrations.jellyfin.*') ? 'active' : '' }}">
                            <i class="bx bx-tv side-menu__icon"></i>
                            <span class="side-menu__label">Jellyfin</span>
                        </a>
                    </li>

                    <li class="slide__category"><span class="category-name">Modules</span></li>
                    @foreach(config('modules') as $moduleKey => $moduleName)
                        <li class="slide">
                            <a href="{{ route('modules.show', $moduleKey) }}" class="side-menu__item {{ request()->routeIs('modules.show') && request()->route('module') === $moduleKey ? 'active' : '' }}">
                                <i class="bx bx-grid-alt side-menu__icon"></i>
                                <span class="side-menu__label">{{ $moduleName }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

    <div class="main-content app-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('theme-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme-assets/defaultmenu-8e822bf3.js') }}"></script>
<script src="{{ asset('theme-assets/sticky.js') }}"></script>
<script>
    (function () {
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
        if (typeof window.history.replaceState === 'function') {
            window.history.replaceState(null, '', window.location.href);
        }
    })();
</script>
</body>
</html>
