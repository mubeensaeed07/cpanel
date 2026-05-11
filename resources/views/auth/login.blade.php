<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="dark" data-header-styles="dark" data-menu-styles="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sign In</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="icon" href="{{ asset('theme-assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">
    <link id="style" href="{{ asset('theme-assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme-assets/icon-fonts/icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('theme-assets/libs/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme-assets/app-b83ce806.css') }}">
    <script src="{{ asset('theme-assets/authentication-main.js') }}"></script>
</head>
<body class="bg-black">
    <div class="row authentication mx-0">
        <div class="col-xxl-7 col-xl-7 col-lg-12">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-6 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                    <div class="p-5">
                        <div class="mb-3">
                            <a href="{{ route('login') }}">
                                <img src="{{ asset('theme-assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="authentication-brand desktop-logo">
                                <img src="{{ asset('theme-assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="authentication-brand desktop-dark">
                            </a>
                        </div>
                        <p class="h5 fw-semibold mb-2">Sign In</p>
                        <p class="mb-3 text-muted op-7 fw-normal">Use Super Admin credentials or an admin account created by Super Admin.</p>

                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="{{ route('login.attempt') }}">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12 mt-0">
                                    <label for="email" class="form-label text-default">Email Address</label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" placeholder="Email address" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-xl-12 mb-3">
                                    <label for="password" class="form-label text-default d-block">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                               id="password" name="password" placeholder="Password" required>
                                        <button class="btn btn-light" type="button" onclick="createpassword('password',this)"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Sign In</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-5 col-xl-5 col-lg-5 d-xl-block d-none px-0">
            <div class="authentication-cover">
                <div class="aunthentication-cover-content rounded">
                    <div class="swiper keyboard-control">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="text-fixed-white text-center p-5 d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="mb-5">
                                            <img src="{{ asset('theme-assets/images/authentication/2.png') }}" class="authentication-image" alt="">
                                        </div>
                                        <h6 class="fw-semibold text-fixed-white">Super Admin Dashboard</h6>
                                        <p class="fw-normal fs-14 op-7">Manage module-wise admins and content from a single dark control panel.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="text-fixed-white text-center p-5 d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="mb-5">
                                            <img src="{{ asset('theme-assets/images/authentication/3.png') }}" class="authentication-image" alt="">
                                        </div>
                                        <h6 class="fw-semibold text-fixed-white">Permission Control</h6>
                                        <p class="fw-normal fs-14 op-7">Create multiple admins and assign access only to selected modules.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('theme-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme-assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('theme-assets/authentication-fa6f6b78.js') }}"></script>
    <script src="{{ asset('theme-assets/show-password.js') }}"></script>
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
