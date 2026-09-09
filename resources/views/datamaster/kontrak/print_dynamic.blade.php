<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrak {{ $kontrak->no_kontrak }}</title>
    <style>
        @page {
            margin: 48mm 10mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        /* Kop Surat Styles */
        header {
            position: fixed;
            top: -38mm;
            left: 0px;
            right: 0px;
            height: 32mm; /* Increased slightly for better fit */
            border-bottom: 4px double #000;
            padding-bottom: 5px; /* Added padding to prevent touching the line */
            margin-bottom: 0px;
        }
        .kop-surat table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-logo {
            width: 120px;
            text-align: left;
            vertical-align: top;
        }
        .kop-logo img {
            max-width: 90px; /* Adjusted width */
            max-height: 25mm; /* Added height limit to prevent overflow */
            height: auto;
            display: block;
        }
        .kop-text {
            text-align: left;
            vertical-align: top;
            padding-left: 10px;
        }
        .kop-text h2 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .kop-text p {
            margin: 0 0 2px 0;
            font-size: 10pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            line-height: 1.2;
        }
        .title {
            text-align: center;
            text-transform: uppercase;
        }
        .title h2, .title h4 {
            margin: 0;
        }
        .content {
            margin-top: 25px;
        }
        .section-table {
            width: 100%;
            border-collapse: collapse;
        }
        .section-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .section-table .label {
            width: 160px;
        }
        .section-table .colon {
            width: 10px;
        }
        .paragraph {
            text-align: justify;
        }
        .pasal-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        ul {
            padding-left: 18px;
        }
        .page-break {
            page-break-before: always;
        }
        table.comp-table {
            width: 70%;
            border-collapse: separate;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        table.comp-table td {
            padding: 6px 10px;
            border: none;
        }
        table.comp-table td.label {
            width: 55%;
        }
        table.comp-table td.value {
            text-align: right;
        }
    </style>
</head>
<body>
    @if(isset($setting))
    <header>
        <table>
            <tr>
                <td class="kop-logo">
                    @php
                        $base64 = '';
                        if (!empty($setting->logo)) {
                            // Try storage path first (actual file location)
                            $path = storage_path('app/public/logo/' . $setting->logo);
                            // Fallback to public path (symlink)
                            if (!file_exists($path)) {
                                $path = public_path('storage/logo/' . $setting->logo);
                            }
                            if (file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        }
                    @endphp
                    @if($base64)
                        <img src="{{ $base64 }}" alt="Logo">
                    @endif
                </td>
                <td class="kop-text">
                    <h2>{{ $setting->nama_perusahaan ?? 'PERUSAHAAN' }}</h2>
                    <p>{{ $setting->alamat ?? '' }}</p>
                    <p>
                        Telp : {{ $setting->telepon ?? '-' }}
                        @if(strtolower(trim($setting->nama_perusahaan ?? '')) == 'demetria store')
                            , IG : @demetria_grosir, FB : Demetria Store Grosir Online Cirebon.
                        @endif
                    </p>
                </td>
            </tr>
        </table>
    </header>
    @endif

    {!! $konten !!}
</body>
</html>
