@php
    $setting = \App\Models\Pengaturanumum::first();
    $expiredDateStr = '';
    if ($setting && $setting->expired) {
        $expiredDateStr = \Carbon\Carbon::parse($setting->expired)->translatedFormat('d F Y');
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak (403)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #32745e 0%, #1a4a3a 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="flex items-center justify-center p-4 bg-pattern">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-20 h-20 bg-[#32745e] opacity-20 rounded-full"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-[#32745e] opacity-20 rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-[#32745e] opacity-10 rounded-full">
        </div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="glass-effect rounded-2xl p-8 shadow-xl" data-aos="fade-up" data-aos-duration="1000">
            <div class="text-center">
                <!-- Modern illustration -->
                <div class="mb-6 floating" data-aos="zoom-in" data-aos-delay="200">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/access-denied-4489363-3723271.png" alt="Akses Ditolak"
                        class="w-48 h-48 mx-auto">
                </div>

                <h1 class="text-4xl font-extrabold text-[#1a4a3a] mb-2" data-aos="fade-up" data-aos-delay="400">
                    403
                </h1>
                <h2 class="text-2xl font-bold text-[#32745e] mb-4" data-aos="fade-up" data-aos-delay="500">
                    Akses Terbatas / Ditolak
                </h2>

                <p class="text-gray-700 font-medium mb-8 leading-relaxed animate-pulse" data-aos="fade-up" data-aos-delay="600">
                    {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.' }}
                    @if(!empty($expiredDateStr) && (str_contains(strtolower($exception->getMessage()), 'kadaluarsa') || str_contains(strtolower($exception->getMessage()), 'expired')))
                        <span class="text-sm text-red-600 font-semibold mt-2 block">
                            (Tanggal Kadaluarsa: {{ $expiredDateStr }})
                        </span>
                    @endif
                </p>

                <div class="flex flex-col gap-3 justify-center items-center" data-aos="fade-up" data-aos-delay="800">
                    @if(str_contains(strtolower($exception->getMessage()), 'kadaluarsa') || str_contains(strtolower($exception->getMessage()), 'expired'))
                        <a href="https://wa.me/6289670444321" target="_blank"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg shadow-lg hover:bg-emerald-700 transition duration-300 ease-in-out transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.5-5.739-1.446L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.965C16.57 1.978 14.1 1.957 12.006 1.957c-5.437 0-9.863 4.373-9.867 9.803-.001 1.812.489 3.585 1.42 5.161l-.944 3.449 3.524-.925zm11.233-6.52c-.29-.145-1.72-.848-1.986-.944-.266-.096-.46-.145-.653.145-.193.29-.747.944-.915 1.137-.168.193-.336.217-.626.072-1.353-.678-2.222-1.229-3.003-2.57-.193-.331.193-.307.553-1.017.06-.12.03-.223-.015-.32-.045-.096-.46-1.109-.63-1.522-.165-.397-.333-.343-.46-.349-.118-.005-.253-.006-.388-.006-.135 0-.356.05-.542.253-.186.203-.71.694-.71 1.694 0 1.001.728 1.968.829 2.103.102.135 1.433 2.188 3.473 3.067.485.209.864.335 1.161.429.489.156.935.134 1.287.082.393-.058 1.72-.703 1.962-1.382.242-.678.242-1.261.17-1.382-.072-.12-.266-.217-.556-.363z"/>
                            </svg>
                            Hubungi Adam Adifa
                        </a>
                    @endif
                    <a href="{{ url('/') }}"
                        class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#32745e] to-[#1a4a3a] text-white font-semibold rounded-lg shadow-lg hover:from-[#1a4a3a] hover:to-[#32745e] transition duration-300 ease-in-out transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 transition duration-300 ease-in-out transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Keluar (Logout)
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            once: true,
            offset: 50
        });
    </script>
</body>

</html>
