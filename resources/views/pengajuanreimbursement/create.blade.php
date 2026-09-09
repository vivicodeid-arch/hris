@extends('layouts.mobile.modern')

@section('title', 'Buat Reimbursement')

@section('header_left')
    <a href="{{ route('pengajuanreimbursement.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: {{ $t['bg_body'] }} !important;
        }

        .form-container {
            padding: 8px 0;
        }

        /* Floating Label Input Group */
        .form-label-group {
            position: relative;
            margin-bottom: 12px;
            background: transparent !important;
            border: 1px solid {{ $t['primary'] }};
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .form-label-group .input-icon {
            position: absolute;
            left: 14px;
            top: 11px;
            font-size: 20px;
            color: {{ $t['primary'] }};
            z-index: 10;
            pointer-events: none;
        }

        .form-label-group input,
        .form-label-group textarea,
        .form-label-group select {
            width: 100% !important;
            height: 44px;
            padding: 18px 14px 2px 42px !important;
            font-size: 14px;
            font-weight: 500;
            color: {{ $t['primary'] }};
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: block !important;
            -webkit-appearance: none;
        }

        .form-label-group textarea {
            height: 80px !important;
            padding-top: 22px !important;
            resize: none;
        }

        .form-label-group label {
            position: absolute;
            top: 11px;
            left: 42px;
            font-size: 14px;
            color: {{ $t['primary'] }};
            opacity: 0.8;
            pointer-events: none;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0;
            z-index: 5;
        }

        .form-label-group input:focus ~ label,
        .form-label-group input:not(:placeholder-shown) ~ label,
        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label,
        .form-label-group select:focus ~ label,
        .form-label-group select.has-value ~ label {
            top: 2px;
            left: 42px;
            font-size: 10px;
            font-weight: 600;
            color: {{ $t['primary'] }};
        }

        /* Item Card */
        .item-nota {
            background: #ffffff;
            border: 1.5px solid {{ $t['primary'] }}20;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .item-nota .item-number {
            position: absolute;
            top: -8px;
            left: 16px;
            background: {{ $t['primary'] }};
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .item-nota .btn-remove {
            position: absolute;
            top: -8px;
            right: 12px;
            width: 24px;
            height: 24px;
            background: #ef4444;
            color: #fff;
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 10;
            box-shadow: 0 2px 6px rgba(239,68,68,0.3);
        }

        /* Static label for select */
        .select-group {
            margin-bottom: 10px;
        }

        .select-group .select-label {
            font-size: 10px;
            font-weight: 700;
            color: {{ $t['primary'] }};
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: block;
        }

        .select-group select {
            width: 100%;
            height: 44px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            background: #fff;
            border: 1px solid {{ $t['primary'] }}40;
            border-radius: 10px;
            outline: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .select-group select:focus {
            border-color: {{ $t['primary'] }};
        }

        /* Mini floating input inside item card */
        .mini-field {
            position: relative;
            border: 1px solid {{ $t['primary'] }}40;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .mini-field input,
        .mini-field textarea {
            width: 100%;
            height: 42px;
            padding: 16px 12px 2px 12px;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            background: transparent;
            border: none;
            outline: none;
        }

        .mini-field textarea {
            height: 60px;
            padding-top: 20px;
            resize: none;
        }

        .mini-field label {
            position: absolute;
            top: 11px;
            left: 12px;
            font-size: 12px;
            color: {{ $t['primary'] }};
            opacity: 0.7;
            pointer-events: none;
            transition: all 0.2s ease-in-out;
        }

        .mini-field input:focus ~ label,
        .mini-field input:not(:placeholder-shown) ~ label,
        .mini-field textarea:focus ~ label,
        .mini-field textarea:not(:placeholder-shown) ~ label {
            top: 2px;
            font-size: 9px;
            font-weight: 700;
            opacity: 1;
        }

        /* Upload Button */
        .upload-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            border: 1.5px dashed {{ $t['primary'] }}50;
            border-radius: 10px;
            cursor: pointer;
            color: {{ $t['primary'] }};
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .upload-area.has-file {
            border-color: #16a34a;
            background: #f0fdf4;
            color: #16a34a;
        }

        /* Limit Info */
        .limit-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            color: #f59e0b;
            background: #fffbeb;
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 4px;
        }

        /* Grand Total Bar */
        .total-bar {
            position: fixed;
            bottom: 70px;
            left: 0;
            right: 0;
            z-index: 40;
            background: {{ $t['primary'] }};
            padding: 14px 20px 16px;
            box-shadow: 0 -4px 20px {{ $t['primary'] }}40;
            border-radius: 20px 20px 0 0;
        }

        .total-bar .total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-bar .total-label {
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .total-bar .total-amount {
            font-size: 22px;
            color: #fff;
            font-weight: 800;
        }

        .btn-submit-modern {
            width: 100%;
            height: 44px;
            background: transparent;
            color: #ffffff;
            border: 2px solid rgba(255,255,255,0.8);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-submit-modern:active {
            background: rgba(255,255,255,0.2);
            transform: scale(0.97);
        }

        /* Button Add */
        .btn-add-item {
            width: 100%;
            height: 44px;
            border: 1.5px dashed {{ $t['primary'] }}60;
            border-radius: 12px;
            background: transparent;
            color: {{ $t['primary'] }};
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .btn-add-item:active {
            transform: scale(0.97);
            background: {{ $t['primary'] }}10;
        }

        /* Add bottom padding for fixed total bar */
        .form-container {
            padding-bottom: 160px;
        }
    </style>
@endpush

@section('content')
    <div class="fade-up form-container">
        <form action="{{ route('pengajuanreimbursement.store') }}" method="POST" enctype="multipart/form-data" id="formReimbursement" autocomplete="off">
            @csrf

            {{-- Tanggal --}}
            <div class="form-label-group">
                <ion-icon name="calendar-outline" class="input-icon"></ion-icon>
                <input type="text" name="tanggal" id="tanggal" placeholder=" " value="{{ date('Y-m-d') }}" required readonly>
                <label for="tanggal">Tanggal Pengajuan</label>
            </div>

            {{-- Keterangan Global --}}
            <div class="form-label-group">
                <ion-icon name="document-text-outline" class="input-icon"></ion-icon>
                <textarea name="keterangan" id="keterangan" placeholder=" " required></textarea>
                <label for="keterangan">Keterangan Global</label>
            </div>

            {{-- Section Title --}}
            <div class="flex items-center justify-between mt-4 mb-3 px-1">
                <div>
                    <h3 class="text-[13px] font-bold" style="color: {{ $t['primary'] }}">Rincian Nota</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Tambahkan item biaya yang diklaim</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 rounded-full" style="background: {{ $t['primary'] }}15; color: {{ $t['primary'] }}" id="itemCounter">1 Item</span>
            </div>

            {{-- Items Container --}}
            <div id="items-container">
                <div class="item-nota" data-index="0">
                    <span class="item-number">NOTA #1</span>
                    
                    <div class="select-group" style="margin-top: 8px;">
                        <span class="select-label">JENIS REIMBURSE</span>
                        <select name="items[0][item_kategori]" class="select-jenis" required>
                            <option value="">— Pilih Jenis —</option>
                            @foreach($jenis_reimburse as $j)
                                <option value="{{ $j->kode_jenis_reimburse }}" data-limit="{{ $j->limit_nominal }}" data-wajib-bukti="{{ $j->wajib_bukti }}">{{ $j->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="limit-info"></div>

                    <div class="mini-field">
                        <input type="text" name="items[0][item_keterangan]" placeholder=" " required>
                        <label>Keterangan Nota</label>
                    </div>

                    <div class="flex gap-2">
                        <div class="flex-1">
                            <div class="mini-field">
                                <input type="text" name="items[0][item_jumlah]" class="nominal-field" placeholder=" " inputmode="numeric" required>
                                <label>Jumlah (Rp)</label>
                            </div>
                        </div>
                        <div class="w-[110px]">
                            <label class="upload-area file-upload-label">
                                <ion-icon name="camera-outline" class="text-lg"></ion-icon>
                                <span class="file-label-text">Foto</span>
                                <input type="file" name="items[0][item_foto]" class="hidden file-input" accept="image/*" capture="camera">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Item Button --}}
            <button type="button" class="btn-add-item" id="btnAddRow">
                <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                Tambah Nota Baru
            </button>
        </form>
    </div>

    {{-- Fixed Total Bar --}}
    <div class="total-bar">
        <div class="total-row">
            <div class="total-label">TOTAL KLAIM</div>
            <div class="total-amount" id="grandTotalLabel">Rp 0</div>
        </div>
        <button type="submit" form="formReimbursement" class="btn-submit-modern" id="btnSubmit">
            <ion-icon name="paper-plane-outline"></ion-icon>
            <span>Kirim Pengajuan</span>
        </button>
    </div>
@endsection

@push('myscript')
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.5.0/air-datepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCount = 1;

            // ========== AIR DATEPICKER ==========
            const localeIndo = {
                days: ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
                daysShort: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                daysMin: ['Mg','Sn','Sl','Rb','Km','Jm','Sb'],
                months: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                monthsShort: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                today: 'Hari ini', clear: 'Hapus',
                dateFormat: 'yyyy-MM-dd', timeFormat: 'HH:mm', firstDay: 1
            };

            new AirDatepicker('#tanggal', {
                locale: localeIndo,
                autoClose: true,
                isMobile: true,
                selectedDates: [new Date()],
                buttons: ['today', 'clear']
            });

            // ========== MONEY FORMAT ==========
            function formatMoney(value) {
                let num = value.replace(/\D/g, '');
                return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function parseMoney(value) {
                return parseInt(value.replace(/\./g, '')) || 0;
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('nominal-field')) {
                    let raw = e.target.value.replace(/\D/g, '');
                    e.target.value = formatMoney(raw);
                    calculateTotal();
                }
            });

            // ========== CALCULATE TOTAL ==========
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.nominal-field').forEach(function(el) {
                    total += parseMoney(el.value);
                });
                document.getElementById('grandTotalLabel').textContent = 'Rp ' + formatMoney(total.toString());
            }

            // ========== JENIS CHANGE (LIMIT INFO) ==========
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('select-jenis')) {
                    let option = e.target.options[e.target.selectedIndex];
                    let limit = option.dataset.limit;
                    let infoEl = e.target.closest('.item-nota').querySelector('.limit-info');
                    if (limit && parseFloat(limit) > 0) {
                        let limitVal = Math.round(parseFloat(limit)).toString();
                        infoEl.innerHTML = '<span class="limit-badge">⚡ Plafon: Rp ' + formatMoney(limitVal) + '</span>';
                    } else {
                        infoEl.innerHTML = '';
                    }
                }
            });

            // ========== FILE UPLOAD ==========
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('file-input')) {
                    let label = e.target.closest('.file-upload-label');
                    let labelText = label.querySelector('.file-label-text');
                    if (e.target.files.length > 0) {
                        label.classList.add('has-file');
                        labelText.textContent = '✓ Ada';
                    } else {
                        label.classList.remove('has-file');
                        labelText.textContent = 'Foto';
                    }
                }
            });

            // ========== ADD ITEM ==========
            document.getElementById('btnAddRow').addEventListener('click', function() {
                let idx = itemCount;
                let html = `
                    <div class="item-nota fade-up" data-index="${idx}" style="animation-delay: 0.05s">
                        <span class="item-number">NOTA #${idx + 1}</span>
                        <button type="button" class="btn-remove" onclick="removeItem(this)"><ion-icon name="close"></ion-icon></button>
                        
                        <div class="select-group" style="margin-top: 8px;">
                            <span class="select-label">JENIS REIMBURSE</span>
                            <select name="items[${idx}][item_kategori]" class="select-jenis" required>
                                <option value="">— Pilih Jenis —</option>
                                @foreach($jenis_reimburse as $j)
                                    <option value="{{ $j->kode_jenis_reimburse }}" data-limit="{{ $j->limit_nominal }}" data-wajib-bukti="{{ $j->wajib_bukti }}">{{ $j->nama_jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="limit-info"></div>

                        <div class="mini-field">
                            <input type="text" name="items[${idx}][item_keterangan]" placeholder=" " required>
                            <label>Keterangan Nota</label>
                        </div>

                        <div class="flex gap-2">
                            <div class="flex-1">
                                <div class="mini-field">
                                    <input type="text" name="items[${idx}][item_jumlah]" class="nominal-field" placeholder=" " inputmode="numeric" required>
                                    <label>Jumlah (Rp)</label>
                                </div>
                            </div>
                            <div class="w-[110px]">
                                <label class="upload-area file-upload-label">
                                    <ion-icon name="camera-outline" class="text-lg"></ion-icon>
                                    <span class="file-label-text">Foto</span>
                                    <input type="file" name="items[${idx}][item_foto]" class="hidden file-input" accept="image/*" capture="camera">
                                </label>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('items-container').insertAdjacentHTML('beforeend', html);
                itemCount++;
                updateCounter();
            });

            // ========== UPDATE COUNTER ==========
            function updateCounter() {
                let count = document.querySelectorAll('.item-nota').length;
                document.getElementById('itemCounter').textContent = count + ' Item';
            }

            // ========== REMOVE ITEM ==========
            window.removeItem = function(btn) {
                let card = btn.closest('.item-nota');
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateX(50px)';
                setTimeout(() => {
                    card.remove();
                    updateCounter();
                    calculateTotal();
                    // Re-number remaining items
                    document.querySelectorAll('.item-nota').forEach((el, i) => {
                        el.querySelector('.item-number').textContent = 'NOTA #' + (i + 1);
                    });
                }, 300);
            };

            // ========== FORM VALIDATION ==========
            document.getElementById('formReimbursement').addEventListener('submit', function(e) {
                let hasError = false;
                let errorMessage = '';

                let tanggal = document.getElementById('tanggal').value;
                let keterangan = document.getElementById('keterangan').value;

                if (!tanggal) {
                    hasError = true;
                    errorMessage = 'Tanggal pengajuan harus diisi!';
                } else if (!keterangan.trim()) {
                    hasError = true;
                    errorMessage = 'Keterangan global harus diisi!';
                }

                if (!hasError) {
                    let items = document.querySelectorAll('.item-nota');
                    if (items.length === 0) {
                        hasError = true;
                        errorMessage = 'Minimal harus ada 1 item nota!';
                    }

                    items.forEach(function(card, index) {
                        if (hasError) return;
                        
                        let jenis = card.querySelector('.select-jenis');
                        let nominal = card.querySelector('.nominal-field');
                        let file = card.querySelector('.file-input');
                        let option = jenis.options[jenis.selectedIndex];

                        if (!jenis.value) {
                            hasError = true;
                            errorMessage = 'Jenis reimburse pada Nota #' + (index + 1) + ' belum dipilih!';
                        } else if (!nominal.value || parseMoney(nominal.value) === 0) {
                            hasError = true;
                            errorMessage = 'Jumlah pada Nota #' + (index + 1) + ' belum diisi!';
                        } else {
                            let val = parseMoney(nominal.value);
                            let limit = Math.round(parseFloat(option.dataset.limit)) || 0;
                            let wajibBukti = parseInt(option.dataset.wajibBukti) || 0;

                            if (wajibBukti === 1 && file.files.length === 0) {
                                hasError = true;
                                errorMessage = '"' + option.text + '" (Nota #' + (index + 1) + ') wajib lampirkan foto bukti!';
                            } else if (limit > 0 && val > limit) {
                                hasError = true;
                                errorMessage = '"' + option.text + '" (Nota #' + (index + 1) + ') melebihi plafon Rp ' + formatMoney(limit.toString()) + '!';
                            }
                        }
                    });
                }

                if (hasError) {
                    e.preventDefault();
                    Swal.fire({ title: 'Oops!', text: errorMessage, icon: 'warning' });
                    return;
                }

                // Convert formatted money back to raw numbers before submit
                document.querySelectorAll('.nominal-field').forEach(function(el) {
                    el.value = parseMoney(el.value);
                });

                let btn = document.getElementById('btnSubmit');
                btn.disabled = true;
                btn.innerHTML = '<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Mengirim...</span>';
            });
        });
    </script>
@endpush
