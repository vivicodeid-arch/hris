@extends('layouts.mobile.modern')


@section('title', 'Ajuan Izin')

@section('header_left')
    <a href="{{ route('dashboard.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-90 transition-transform">
        <ion-icon name="chevron-back-outline" class="text-base"></ion-icon>
    </a>
@endsection

@section('content')

    {{-- ===== HISTORY LIST ===== --}}
    <div id="showhistori">
        {{-- Skeleton synced with dashboard & histori --}}
        <div id="skeleton-container" class="space-y-2">
            @for ($i = 0; $i < 5; $i++)
                <div class="rounded-[10px] p-1 border shadow-sm" style="background: #fff; border-color: #f1f5f9;">

                    <div class="flex items-center gap-2">
                        <div class="skeleton-avatar sk flex-shrink-0"></div>
                        <div class="flex-1 space-y-2 pr-2">
                            <div class="flex justify-between items-center">
                                <div class="skeleton-text w-24 sk"></div>
                                <div class="skeleton-text w-12 sk"></div>
                            </div>
                            <div class="skeleton-text w-32 sk"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Data synced with dashboard & histori --}}
        <div id="data-container" style="display:none;" class="space-y-2">
            @foreach ($pengajuan_izin as $index => $d)
                @php
                    if ($d->ket == 'i') {
                        $route = 'izinabsen.delete';
                        $ket_text = 'Izin Absen';
                        $icon = 'document-text-outline';
                    } elseif ($d->ket == 's') {
                        $route = 'izinsakit.delete';
                        $ket_text = 'Izin Sakit';
                        $icon = 'medkit-outline';
                    } elseif ($d->ket == 'c') {
                        $route = 'izincuti.delete';
                        $ket_text = 'Izin Cuti';
                        $icon = 'calendar-outline';
                    } elseif ($d->ket == 'd') {
                        $route = 'izindinas.delete';
                        $ket_text = 'Izin Dinas';
                        $icon = 'airplane-outline';
                    } elseif ($d->ket == 'k') {
                        $route = 'koreksi.delete';
                        $ket_text = 'Koreksi Absen';
                        $icon = 'create-outline';
                    }

                    $namahari = [
                        'Sun' => 'Minggu',
                        'Mon' => 'Senin',
                        'Tue' => 'Selasa',
                        'Wed' => 'Rabu',
                        'Thu' => 'Kamis',
                        'Fri' => 'Jumat',
                        'Sat' => 'Sabtu'
                    ];
                    $day_eng = date('D', strtotime($d->dari));
                    $day_indo = $namahari[$day_eng] ?? $day_eng;
                    $day_short = strtoupper(substr($day_indo, 0, 3));
                    $tgl = date('d', strtotime($d->dari));
                    $jml_hari = date_diff(date_create($d->dari), date_create($d->sampai))->format('%a') + 1;

                    // Status styles synced with histori but adapted for approval status
                    $statusStyles = [
                        '0' => ['label' => 'Pending', 'color' => '#ff9f40', 'rgb' => '255, 159, 64'],
                        '1' => ['label' => 'Disetujui', 'color' => $t['primary'], 'rgb' => '50, 116, 94'],
                        '2' => ['label' => 'Ditolak', 'color' => '#e74c3c', 'rgb' => '231, 76, 60'],
                    ];

                    $st = $statusStyles[$d->status_izin] ?? $statusStyles['0'];
                    $bgColor = "rgba({$st['rgb']}, 0.1)";
                @endphp

                <form method="POST" name="deleteform" class="deleteform" action="{{ route($route, Crypt::encrypt($d->kode)) }}">
                    @csrf
                    @method('DELETE')
                    <div class="fade-up card press mb-1 overflow-hidden cursor-pointer {{ $d->status_izin == 0 ? 'cancel-confirm' : '' }}"
                        style="border: 1px solid {{ $t['primary'] }}; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); background: #fff; animation-delay: {{ $index * 0.04 }}s;">

                        <div class="card-body p-1 flex items-center gap-2">
                            {{-- Date Badge --}}
                            <div class="flex-shrink-0 w-[45px] h-[45px] flex flex-col items-center justify-center rounded-[12px]"
                                style="background: {{ $bgColor }}; color: {{ $st['color'] }};">
                                <span class="text-[10px] font-bold leading-none">{{ $day_short }}</span>
                                <span class="text-[16px] font-extrabold leading-tight mt-0.5">{{ $tgl }}</span>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0 pr-1">
                                <div class="flex items-center justify-between mb-0.5">
                                    <h3 class="text-[14px] font-semibold truncate" style="color: #333;">
                                        {{ $ket_text }}
                                    </h3>
                                    <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-full font-bold"
                                        style="background: {{ $bgColor }}; color: {{ $st['color'] }};">
                                        {{ $st['label'] }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between mb-0.5">
                                    <div class="flex items-center gap-1.5 text-[11px] font-medium" style="color: #666;">
                                        <ion-icon name="calendar-clear-outline"></ion-icon>
                                        <span>{{ DateToIndo($d->dari) }} - {{ DateToIndo($d->sampai) }}</span>
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-bold"
                                            style="font-size: 9px;">{{ $jml_hari }} Hari</span>
                                    </div>
                                </div>

                                <p class="text-[11px] leading-tight text-gray-400 truncate" style="max-width: 90%;">
                                    {{ $d->keterangan }}
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            @endforeach

            @if ($pengajuan_izin->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background: #f1f5f9;">
                        <ion-icon name="document-text-outline" class="text-3xl" style="color: #cbd5e1;"></ion-icon>
                    </div>
                    <h3 class="text-[14px] font-bold mb-1" style="color: #334155;">Belum Ada Pengajuan</h3>
                    <p class="text-[12px] leading-relaxed max-w-[220px]" style="color: #94a3b8;">Klik tombol tambah di bawah
                        untuk membuat pengajuan izin baru.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Floating Action Button - Modern Style --}}
    <div class="fixed bottom-24 right-6 z-50">
        <div class="relative group">
            {{-- Menu Items (Appears on click/hover) --}}
            <div id="fab-menu"
                class="absolute bottom-16 right-0 space-y-3 pointer-events-none opacity-0 translate-y-4 transition-all duration-300">
                <a href="{{ route('izinabsen.create') }}" class="flex items-center gap-3 justify-end group/item">
                    <span class="bg-white px-3 py-1.5 rounded-lg shadow-md text-xs font-bold text-gray-700 whitespace-nowrap">Izin Absen</span>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        style="background: {{ $t['primary'] }};">
                        <ion-icon name="document-text-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
                <a href="{{ route('izinsakit.create') }}" class="flex items-center gap-3 justify-end group/item">
                    <span class="bg-white px-3 py-1.5 rounded-lg shadow-md text-xs font-bold text-gray-700 whitespace-nowrap">Izin Sakit</span>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        style="background: #ff6384;">
                        <ion-icon name="medkit-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
                <a href="{{ route('izincuti.create') }}" class="flex items-center gap-3 justify-end group/item">
                    <span class="bg-white px-3 py-1.5 rounded-lg shadow-md text-xs font-bold text-gray-700 whitespace-nowrap">Izin Cuti</span>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        style="background: #ff9f40;">
                        <ion-icon name="calendar-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
                <a href="{{ route('izindinas.create') }}" class="flex items-center gap-3 justify-end group/item">
                    <span class="bg-white px-3 py-1.5 rounded-lg shadow-md text-xs font-bold text-gray-700 whitespace-nowrap">Izin Dinas</span>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        style="background: #4bc0c0;">
                        <ion-icon name="airplane-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
                <a href="{{ route('koreksi.create') }}" class="flex items-center gap-3 justify-end group/item">
                    <span class="bg-white px-3 py-1.5 rounded-lg shadow-md text-xs font-bold text-gray-700 whitespace-nowrap">Koreksi Absen</span>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        style="background: #9966ff;">
                        <ion-icon name="create-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
            </div>

            {{-- Main Toggle Button --}}
            <button id="fab-main"
                class="w-14 h-14 rounded-full flex items-center justify-center text-white shadow-xl active:scale-90 transition-all duration-300"
                style="background: {{ $t['primary'] }};">
                <ion-icon name="add-outline" id="fab-icon" class="text-3xl transition-transform duration-300"></ion-icon>
            </button>
        </div>
    </div>

@endsection

@push('myscript')
    <script>
        function showSkeleton() { $('#data-container').hide(); $('#skeleton-container').show(); }
        function hideSkeleton() { $('#skeleton-container').fadeOut(200, function () { $('#data-container').fadeIn(300); }); }
        $(document).ready(function () {
            setTimeout(hideSkeleton, 400);

            // FAB Toggle logic
            let fabOpen = false;
            $('#fab-main').on('click', function () {
                fabOpen = !fabOpen;
                if (fabOpen) {
                    $('#fab-menu').removeClass('pointer-events-none opacity-0 translate-y-4').addClass('opacity-100 translate-y-0 pointer-events-auto');
                    $('#fab-icon').addClass('rotate-45');
                } else {
                    $('#fab-menu').removeClass('opacity-100 translate-y-0 pointer-events-auto').addClass('pointer-events-none opacity-0 translate-y-4');
                    $('#fab-icon').removeClass('rotate-45');
                }
            });

            // Close FAB when clicking outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#fab-main, #fab-menu').length && fabOpen) {
                    $('#fab-main').trigger('click');
                }
            });

            // Delete confirmation
            $(".cancel-confirm").click(function (e) {
                var form = $(this).closest('form');
                e.preventDefault();
                Swal.fire({
                    title: 'Batalkan Pengajuan?',
                    text: "Data pengajuan ini akan dihapus permanen",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '{{ $t['primary'] }}',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tutup',
                    borderRadius: '20px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    </script>
@endpush