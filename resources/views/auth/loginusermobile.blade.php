<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | {{ $general_setting->nama_aplikasi ?? 'E-Presensi Mobile' }}</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="{{ $general_setting->nama_aplikasi ?? 'E-Presensi GPS V2' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $general_setting->nama_aplikasi ?? 'E-Presensi' }}">
    <meta name="description" content="Aplikasi {{ $general_setting->nama_aplikasi ?? 'Presensi GPS' }} untuk Karyawan">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#106f62">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/assets/img/icons/pwa/icon-192x192.png">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json?v={{ file_exists(public_path('manifest.json')) ? filemtime(public_path('manifest.json')) : time() }}">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        :root {
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#106f62' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b5247' }};
            --theme-color-light: color-mix(in srgb, var(--theme-color-1) 15%, #ffffff);
            --theme-color-fade: color-mix(in srgb, var(--theme-color-1) 30%, #ffffff);
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Poppins', sans-serif; 
        }
        
        body, html { 
            background-color: #f2f6f5; 
            color: #333; 
            height: 100%; 
            width: 100%;
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
        }

        .top-section { 
            height: 38%; 
            background-color: var(--theme-color-1);
            position: relative; 
            padding: 30px 35px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            z-index: 10;
        }

        .top-section h1 { 
            color: #fff; 
            font-size: 42px; 
            font-weight: 600; 
            line-height: 1; 
            margin-bottom: 0px;
        }

        .top-section p { 
            color: #e0f2f1; 
            font-size: 15px; 
            font-weight: 300; 
            letter-spacing: 0.5px;
        }
        
        /* Abstract decorative blob */
        .blob-1 { 
            position: absolute; 
            top: -20px; 
            left: -20px; 
            width: 150px; 
            height: 150px; 
            background: rgba(255,255,255,0.15); 
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; 
            z-index: -1; 
        }
        
        /* Plant overlapping the sections */
        .plant-wrapper {
            position: absolute;
            bottom: -35px;
            right: 25px;
            width: 80px;
            height: 150px;
            z-index: 15;
            pointer-events: none;
        }
        
        .leaf-1 { 
            position: absolute; 
            bottom: 45px; 
            right: 20px; 
            width: 30px; 
            height: 100px; 
            background: var(--theme-color-light); 
            border-radius: 100% 0 100% 0; 
            transform-origin: bottom center; 
            transform: rotate(-10deg); 
            z-index: 6;
        }

        .leaf-1::after { /* leaf inner line */
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 1px;
            height: 90%;
            background: rgba(0,0,0,0.1);
        }

        .leaf-2 { 
            position: absolute; 
            bottom: 45px; 
            right: 35px; 
            width: 25px; 
            height: 85px; 
            background: var(--theme-color-fade); 
            border-radius: 0 100% 0 100%; 
            transform-origin: bottom center; 
            transform: rotate(20deg); 
            z-index: 5;
        }

        .plant-pot { 
            position: absolute; 
            bottom: 10px; 
            left: 10px; 
            width: 60px; 
            height: 48px; 
            background: #fff; 
            border-radius: 8px 8px 25px 25px; 
            box-shadow: inset -5px -5px 10px rgba(0,0,0,0.06); 
            z-index: 10; 
        }

        .pot-shadow { 
            position: absolute; 
            bottom: -2px; 
            left: 14px; 
            width: 52px; 
            height: 15px; 
            background: rgba(0,0,0,0.15); 
            border-radius: 50%; 
            z-index: 4; 
        }
        
        .bottom-section { 
            background-color: #f2f6f5; 
            flex: 1; 
            border-radius: 35px 35px 0 0; 
            position: relative; 
            z-index: 12; 
            padding: 30px 35px 20px; 
            display: flex; 
            flex-direction: column; 
            margin-top: -30px; /* pull up over dark background */
        }
        
        .container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }

        .login-title { 
            /* Cool gradient text */
            background: linear-gradient(135deg, var(--theme-color-1) 0%, var(--theme-color-2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px; 
            font-weight: 800; 
            margin-bottom: 35px; 
            margin-top: 5px; 
            letter-spacing: -0.5px;
        }
        
        .form-group { 
            margin-bottom: 20px; 
            position: relative; 
        }

        .form-group ion-icon { 
            position: absolute; 
            left: 20px; 
            top: 50%;
            transform: translateY(-50%);
            color: #a9b5b2; 
            font-size: 20px; 
            z-index: 10;
            transition: all 0.3s ease;
        }

        .form-control { 
            width: 100%; 
            padding: 18px 20px 18px 50px; 
            border-radius: 12px; 
            border: 2px solid transparent; 
            background: #fff; 
            font-size: 14px; 
            font-weight: 500; 
            color: #444; 
            outline: none; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.03); 
            transition: all 0.3s ease;
        }

        .form-control::placeholder { 
            color: #a9b5b2; 
            font-weight: 400; 
        }

        .form-control:focus { 
            box-shadow: 0 10px 25px rgba(16, 111, 98, 0.12); 
            border: 2px solid var(--theme-color-light);
            transform: translateY(-2px);
        }
        
        .form-control:focus + ion-icon, 
        .form-group:focus-within ion-icon {
            color: var(--theme-color-1);
            transform: translateY(calc(-50% - 2px));
        }
        
        .forgot-pass { 
            text-align: right; 
            margin-top: -5px; 
            margin-bottom: 25px; 
        }

        .forgot-pass a { 
            color: #518b82; 
            font-size: 13px; 
            font-weight: 600; 
            text-decoration: none; 
        }
        
        .btn-login { 
            width: 100%; 
            padding: 16px; 
            border-radius: 12px; 
            border: none; 
            /* Cool gradient button */
            background: linear-gradient(135deg, var(--theme-color-1) 0%, var(--theme-color-2) 100%);
            color: #fff; 
            font-size: 16px; 
            font-weight: 600; 
            letter-spacing: 0.5px;
            cursor: pointer; 
            box-shadow: 0 8px 20px color-mix(in srgb, var(--theme-color-1) 40%, transparent); 
            transition: all 0.3s ease; 
            margin-bottom: 35px; 
        }
        .btn-login:active { 
            transform: scale(0.96) translateY(2px); 
            box-shadow: 0 4px 10px color-mix(in srgb, var(--theme-color-1) 30%, transparent); 
        }
        
        .divider { 
            display: flex; 
            align-items: center; 
            margin-bottom: 25px; 
            color: #a9b5b2; 
            font-size: 13px; 
        }
        
        .divider::before, .divider::after { 
            content: ''; 
            flex: 1; 
            height: 1px; 
            background: #d0deda; 
        }

        .divider span { margin: 0 10px; }
        
        .social-login { 
            display: flex; 
            justify-content: center; 
            gap: 20px; 
            margin-bottom: 25px; 
            margin-top: auto; 
        }
        
        .social-btn { 
            width: 60px; 
            height: 45px; 
            background: #fff; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.04); 
            text-decoration: none; 
        }
        
        .social-btn ion-icon { font-size: 24px; }
        .fb-icon { color: #1877F2; }
        /* Complex google icon pure CSS recreation */
        .google-icon { 
            background: conic-gradient(from -45deg, #ea4335 110deg, #4285f4 90deg 180deg, #34a853 180deg 270deg, #fbbc05 270deg) 73% 55%/150% 150% no-repeat;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .apple-icon { color: #000; }
        
        .signup-text { 
            text-align: center; 
            color: #8eaba5; 
            font-size: 14px; 
            font-weight: 400; 
            margin-bottom: 10px; 
        }

        .signup-text a { 
            color: #106f62; 
            font-weight: 600; 
            text-decoration: none; 
        }
        
        /* Alert overrides */
        .alert { 
            position: absolute; 
            top: 20px; 
            left: 20px; 
            right: 20px; 
            padding: 15px 18px; 
            border-radius: 15px; 
            font-size: 13px; 
            font-weight: 500; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            z-index: 100; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            animation: slideDown 0.3s ease-out; 
            background: #fff;
        }

        .alert-danger { color: #c62828; border-left: 5px solid #c62828; }
        .alert-success { color: #2e7d32; border-left: 5px solid #2e7d32; }
        .alert-close { 
            background: none; 
            border: none; 
            color: inherit; 
            font-size: 22px; 
            cursor: pointer; 
            padding: 0 5px; 
            opacity: 0.5;
        }

        @keyframes slideDown { 
            from { transform: translateY(-30px); opacity: 0; } 
            to { transform: translateY(0); opacity: 1; } 
        }
    </style>
</head>
<body>
    @if (session('error'))
        <div class="alert alert-danger" id="alert-box">
            <span>{{ session('error') }}</span>
            <button class="alert-close" onclick="document.getElementById('alert-box').style.display='none'">&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" id="alert-box-2">
            <span>{{ $errors->first() }}</span>
            <button class="alert-close" onclick="document.getElementById('alert-box-2').style.display='none'">&times;</button>
        </div>
    @endif

    <div class="top-section">
        <div class="blob-1"></div>
        
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px; z-index: 20; position: relative;">
            @if (!empty($general_setting->logo) && Storage::disk('public')->exists('logo/' . $general_setting->logo))
                <img src="{{ asset('storage/logo/' . $general_setting->logo) }}" alt="Logo" style="width: 50px; height: 50px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2)); background: rgba(255,255,255,0.9); border-radius: 50%; padding: 8px;" />
            @else
                <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="Logo" style="width: 50px; height: 50px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2)); background: rgba(255,255,255,0.9); border-radius: 50%; padding: 8px;" />
            @endif
            <div style="color: #fff; font-size: 22px; font-weight: 800; letter-spacing: 1px;">{{ $general_setting->nama_aplikasi ?? 'GAWE V3' }}</div>
        </div>
        
        <h1 style="text-shadow: 0 2px 10px rgba(0,0,0,0.1);">Welcome!</h1>
        <p style="opacity: 0.9;">to {{ $general_setting->nama_perusahaan ?? 'E-Presensi GPS' }}</p>
        
        <div class="plant-wrapper">
            <div class="leaf-1"></div>
            <div class="leaf-2"></div>
            <div class="plant-pot"></div>
            <div class="pot-shadow"></div>
        </div>
    </div>
    
    <div class="bottom-section">
        <div class="container">
            <div class="login-title">Login</div>
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" name="id_user" class="form-control" placeholder="Email / NIK / ID" autocomplete="off" required value="{{ old('id_user') }}">
                </div>
                
                <div class="form-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                
                <div class="forgot-pass">
                    <a href="#">Forgot Password</a>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="divider">
                <span>Or login with</span>
            </div>
            
            <div class="social-login">
                <a href="#" class="social-btn">
                    <ion-icon name="logo-facebook" class="fb-icon"></ion-icon>
                </a>
                <a href="#" class="social-btn" style="border: 1px solid #eee;">
                    <!-- Using raw svg for google logo for better crossbrowser rendering -->
                    <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </a>
                <a href="#" class="social-btn">
                    <ion-icon name="logo-apple" class="apple-icon"></ion-icon>
                </a>
            </div>
            
            <div class="signup-text">
                Don't have account? <a href="#">Sign Up</a>
            </div>
        </div>
    </div>
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                        // Force update check to sync manifest/icons immediately
                        registration.update();
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
    @include('components.pwa-install-prompt')
</body>
</html>
