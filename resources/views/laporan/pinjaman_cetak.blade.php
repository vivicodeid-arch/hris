<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pinjaman Karyawan {{ date('Y-m-d H:i:s') }}</title>
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
        
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            color: white;
        }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-success { background-color: #28a745; }
        .bg-danger { background-color: #dc3545; }
        .bg-primary { background-color: #007bff; }
        .bg-secondary { background-color: #6c757d; }
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
                        LAPORAN PINJAMAN KARYAWAN
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        PERIODE {{ date('d-m-Y', strtotime($dari)) }} - {{ date('d-m-Y', strtotime($sampai)) }}
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
                    <th>No</th>
                    <th>No Pinjaman</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen</th>
                    <th>Cabang</th>
                    <th>Tanggal Pinjaman</th>
                    <th>Jumlah Pinjaman</th>
                    <th>Tenor (Bulan)</th>
                    <th>Total Dibayar</th>
                    <th>Sisa Pinjaman</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pinjaman as $p)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>{{ $p->no_pinjaman }}</td>
                        <td style="text-align: center;">{{ $p->nik_show ?? $p->nik }}</td>
                        <td>{{ $p->nama_karyawan }}</td>
                        <td>{{ $p->nama_dept }}</td>
                        <td>{{ $p->nama_cabang }}</td>
                        <td style="text-align: center;">{{ date('d-m-Y', strtotime($p->tanggal_pinjaman)) }}</td>
                        <td style="text-align: right;">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $p->jumlah_cicilan }} bln (Rp {{ number_format($p->jumlah_per_cicilan, 0, ',', '.') }}/bln)</td>
                        <td style="text-align: right;">Rp {{ number_format($p->total_dibayar, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($p->sisa_pinjaman, 0, ',', '.') }}</td>
                        <td style="text-align: center;">
                            @if ($p->status == 'A')
                                <span class="badge bg-primary">Aktif</span>
                            @elseif ($p->status == 'L')
                                <span class="badge bg-success">Lunas</span>
                            @elseif ($p->status == 'B')
                                <span class="badge bg-secondary">Batal</span>
                            @endif
                        </td>
                        <td>{{ $p->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($pinjaman->sum('jumlah_pinjaman'), 0, ',', '.') }}</td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($pinjaman->sum('total_dibayar'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($pinjaman->sum('sisa_pinjaman'), 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
