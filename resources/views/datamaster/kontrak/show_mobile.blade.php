@extends('layouts.mobile.modern')
@section('title', 'Detail Kontrak')

@section('header_left')
    <a href="{{ route('kontrak.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        .letter-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            padding: 30px 20px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            position: relative;
            overflow: hidden;
        }

        /* Decorative top accent for the document */
        .letter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #32745e, #4b9b82);
        }

        /* Override template styles for mobile */
        .letter-card table {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-collapse: collapse !important;
        }

        .letter-card td {
            padding: 4px 0 !important;
            vertical-align: top !important;
        }

        /* Handle labels in tables */
        .letter-card td.label, 
        .letter-card td:first-child:not([colspan]) {
            width: 100px !important; /* Fixed width for labels to align colons */
            color: #64748b;
            font-weight: 500;
            font-size: 13px;
        }

        .letter-card td.colon,
        .letter-card td:nth-child(2):not([colspan]) {
            width: 12px !important;
            color: #94a3b8;
        }

        .letter-card .title h1, 
        .letter-card .title h2 {
            font-size: 18px !important;
            color: #0f172a !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            margin-bottom: 8px !important;
            line-height: 1.3 !important;
            text-align: center;
        }

        .letter-card .title p, 
        .letter-card .title h4 {
            font-size: 12px !important;
            color: #64748b !important;
            font-weight: 600 !important;
            text-align: center;
            margin: 0 !important;
        }

        .letter-card .pasal-title {
            text-align: center;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 32px;
            margin-bottom: 16px;
            font-size: 15px;
            border-top: 1px solid #f1f5f9;
            padding-top: 24px;
            line-height: 1.4;
        }

        .letter-card .ayat-title {
            text-align: center;
            font-weight: 700;
            color: #475569;
            font-size: 13px;
            margin-top: 12px;
            margin-bottom: 8px;
        }

        .letter-card .paragraph, 
        .letter-card p {
            text-align: justify;
            margin-bottom: 16px;
            color: #334155;
        }

        .letter-card strong, 
        .letter-card b {
            color: #0f172a;
            font-weight: 700;
        }

        /* Compensation table specific styling */
        .letter-card .comp-table {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin: 16px 0;
        }

        .letter-card .comp-table td {
            padding: 10px 14px !important;
            border-bottom: 1px solid #eef2f6 !important;
        }

        .letter-card .comp-table tr:last-child td {
            border-bottom: none !important;
        }

        .letter-card .comp-table td.label {
            width: auto !important;
            color: #475569;
        }

        .letter-card .comp-table td.value {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }

        /* Signature section refinements */
        .letter-card .signature-table,
        .letter-card table:last-of-type {
            margin-top: 40px !important;
        }

        .letter-card .signature-table td {
            text-align: center !important;
            width: 50% !important;
        }

        /* Animations */
        .fade-up {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')
    <div class="px-1 pt-2 pb-24">
        <div class="letter-card fade-up">
            {!! $konten !!}

            <div class="mt-8 text-center px-2">
                <a href="{{ route('kontrak.print', Crypt::encrypt($kontrak->id)) }}" class="flex items-center justify-center gap-2 w-full py-3 bg-[#32745e] text-white rounded-xl font-bold text-sm shadow-lg shadow-[#32745e]/20 active:scale-95 transition-all">
                    <ion-icon name="print-outline" class="text-lg"></ion-icon> Download / Cetak PDF
                </a>
            </div>

        </div>
    </div>
@endsection
