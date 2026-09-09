<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Rekap Pinjaman Karyawan {{ date('Y-m-d H:i:s') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header h4 {
            line-height: 1.2;
            margin: 0 0 5px 0;
        }

        .table-responsive {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .datatable3 {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .datatable3 th,
        .datatable3 td {
            border: 1px solid #333;
            padding: 6px;
            vertical-align: middle;
        }

        .datatable3 th {
            background-color: #024a75;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header" style="margin-bottom: 10px">
        <table>
            <tr>
                <td style="width: 70px; padding-right: 10px;">
                    @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo Perusahaan" style="max-width: 100px;">
                    @else
                        <img src="https://placehold.co/100x100?text=Logo" alt="Logo Default" style="max-width: 100px;">
                    @endif
                </td>
                <td>
                    <h4 style="line-height: 20px; margin-bottom: 5px">
                        LAPORAN REKAP PINJAMAN KARYAWAN
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        PERIODE {{ getNamabulan($bulan) }} {{ $tahun }}
                    </h4>
                    <span style="font-style: italic;">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic;">{{ $generalsetting->telepon }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-responsive">
        <table class="datatable3">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">NIK</th>
                    <th rowspan="2">Nama Karyawan</th>
                    <th rowspan="2">Departemen</th>
                    <th rowspan="2">Cabang</th>
                    <th rowspan="2">Saldo Awal</th>
                    <th rowspan="2">Penambah (Pinjaman Baru)</th>
                    <th colspan="2">Pembayaran</th>
                    <th rowspan="2">Saldo Akhir</th>
                </tr>
                <tr>
                    <th>Potong Gaji</th>
                    <th>Bayar Cash</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap as $r)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td style="text-align: center;">{{ $r->nik_show ?? $r->nik }}</td>
                        <td>{{ $r->nama_karyawan }}</td>
                        <td>{{ $r->nama_dept }}</td>
                        <td>{{ $r->nama_cabang }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->saldo_awal, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->penambah, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->potong_gaji, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->bayar_cash, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($r->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($rekap->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($rekap->sum('penambah'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($rekap->sum('potong_gaji'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($rekap->sum('bayar_cash'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($rekap->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
