<!DOCTYPE html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('/assets/') }}"
    data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Admin Login | E-Presensi</title>

    <meta name="description" content="Halaman Login Khusus Administrator" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts: Plus Jakarta Sans for premium modern look -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('/assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('/assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/vendor/css/rtl/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/css/demo.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('/assets/js/config.js') }}"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: radial-gradient(circle at 10% 20%, rgba(5, 59, 34, 0.15) 0%, rgba(11, 106, 58, 0.05) 90.2%), #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Abstract Premium Background Blobs */
        .blob-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #053b22 0%, #0b6a3a 100%);
            filter: blur(100px);
            opacity: 0.15;
            border-radius: 50%;
            top: -100px;
            right: -100px;
            z-index: 0;
        }

        .blob-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #0b6a3a 0%, #053b22 100%);
            filter: blur(120px);
            opacity: 0.12;
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            z-index: 0;
        }

        .login-container {
            z-index: 10;
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(5, 59, 34, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(5, 59, 34, 0.12);
        }

        .brand-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }

        .logo-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 8px 24px -6px rgba(5, 59, 34, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 68px;
            height: 68px;
            border: 1px solid rgba(5, 59, 34, 0.05);
            margin-bottom: 16px;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-title {
            font-size: 20px;
            font-weight: 800;
            color: #053b22;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .logo-subtitle {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-top: 4px;
        }

        /* Form styling updates */
        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 11px 16px;
            font-size: 14px;
            transition: all 0.25s ease;
            background-color: #ffffff;
            color: #1e293b;
        }

        .form-control:focus {
            border-color: #0b6a3a;
            box-shadow: 0 0 0 4px rgba(11, 106, 58, 0.1);
            background-color: #ffffff;
        }

        .input-group-text {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background-color: #ffffff;
            color: #64748b;
            transition: all 0.25s ease;
        }

        .input-group:focus-within .form-control {
            border-color: #0b6a3a;
        }

        .input-group:focus-within .input-group-text {
            border-color: #0b6a3a;
            box-shadow: 0 0 0 4px rgba(11, 106, 58, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #053b22 0%, #0b6a3a 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px -8px rgba(11, 106, 58, 0.4);
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -6px rgba(11, 106, 58, 0.5);
            background: linear-gradient(135deg, #0b6a3a 0%, #053b22 100%);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .form-check-input:checked {
            background-color: #0b6a3a;
            border-color: #0b6a3a;
        }

        .forgot-link {
            color: #0b6a3a;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            color: #053b22;
            text-decoration: underline;
        }

        .footer-credit {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <!-- Background elements -->
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    @php
        $general_setting = \App\Models\Pengaturanumum::where('id', 1)->first();
    @endphp

    <div class="login-container">
        <div class="card login-card">
            <div class="card-body p-4 p-sm-5">
                <!-- Logo & Brand -->
                <div class="brand-logo-container">
                    <div class="logo-box">
                        @if (!empty($general_setting->logo) && Storage::disk('public')->exists('logo/' . $general_setting->logo))
                            <img src="{{ asset('storage/logo/' . $general_setting->logo) }}" alt="Logo" />
                        @else
                            <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="Default Logo" />
                        @endif
                    </div>
                    <h4 class="logo-title">{{ $general_setting->nama_aplikasi ?? 'GAWE V3' }}</h4>
                    <span class="logo-subtitle">Portal Login Administrator</span>
                </div>

                <!-- Custom validation feedback -->
                <x-alert-error :messages="$errors->get('id_user')" class="mb-3" />

                <!-- Form -->
                <form id="formAuthentication" class="mb-2" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="id_user" class="form-label">Email or Username</label>
                        <input type="text" class="form-control" id="id_user" name="id_user" placeholder="Masukkan email atau username" autofocus required />
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <label class="form-label" for="password">Password</label>
                            <a href="javascript:;" class="forgot-link">
                                <small>Lupa Password?</small>
                            </a>
                        </div>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="••••••••••••" aria-describedby="password"
                                autocomplete="current-password" required />
                            <span class="input-group-text cursor-pointer" id="togglePassword"><i class="ti ti-eye-off"></i></span>
                        </div>
                    </div>

                    <div class="mb-4 d-flex align-items-center">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                            <label class="form-check-label" for="remember-me" style="font-size: 13px; font-weight: 500; color: #475569;">
                                Ingat Saya
                            </label>
                        </div>
                    </div>

                    <button class="btn btn-submit d-grid w-100" type="submit">Masuk ke Dashboard</button>
                </form>
            </div>
        </div>

        <div class="footer-credit">
            &copy; {{ date('Y') }} {{ $general_setting->nama_perusahaan ?? 'Company' }}. All rights reserved.
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('/assets/vendor/js/bootstrap.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Password Show/Hide toggle logic
            $('#togglePassword').click(function() {
                const passwordField = $('#password');
                const fieldType = passwordField.attr('type');
                const icon = $(this).find('i');
                
                if (fieldType === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                }
            });
        });
    </script>
</body>
</html>
