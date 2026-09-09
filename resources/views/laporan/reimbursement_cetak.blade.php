<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Reimbursement {{ date('Y-m-d H:i:s') }}</title>
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
                        LAPORAN REIMBURSEMENT
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
                    <th>No Pengajuan</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen</th>
                    <th>Cabang</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Total Nominal</th>
                    <th>Total Disetujui</th>
                    <th>Status</th>
                    <th>Rincian Keterangan &amp; Item</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reimbursement as $r)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>{{ $r->no_reimbursement }}</td>
                        <td style="text-align: center;">{{ $r->nik_show ?? $r->nik }}</td>
                        <td>{{ $r->nama_karyawan }}</td>
                        <td>{{ $r->nama_dept }}</td>
                        <td>{{ $r->nama_cabang }}</td>
                        <td style="text-align: center;">{{ date('d-m-Y', strtotime($r->tanggal_pengajuan)) }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->total_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($r->total_disetujui, 0, ',', '.') }}</td>
                        <td style="text-align: center;">
                            @if ($r->status == 'P')
                                <span class="badge bg-warning">Pending</span>
                            @elseif ($r->status == 'A')
                                <span class="badge bg-success">Approved</span>
                            @elseif ($r->status == 'R')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif ($r->status == 'D')
                                <span class="badge bg-primary">Dibayar</span>
                            @elseif ($r->status == 'B')
                                <span class="badge bg-secondary">Batal</span>
                            @endif
                        </td>
                        <td>
                            @if (isset($details_by_id[$r->id]))
                                <ul style="margin: 0; padding-left: 15px;">
                                    @foreach ($details_by_id[$r->id] as $det)
                                        <li>
                                            <strong>{{ $det->nama_jenis }}</strong> - 
                                            {{ $det->keterangan }} 
                                            (Rp {{ number_format($det->nominal, 0, ',', '.') }} @if($det->nominal_disetujui !== null) disetujui Rp {{ number_format($det->nominal_disetujui, 0, ',', '.') }} @endif)
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($reimbursement->sum('total_nominal'), 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($reimbursement->sum('total_disetujui'), 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 30px; width: 50%;">
        <h4 style="margin-bottom: 10px;">REKAPITULASI PER JENIS REIMBURSEMENT</h4>
        <table class="datatable3">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Jenis Reimbursement</th>
                    <th>Total Nominal</th>
                    <th>Total Disetujui</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rekap_no = 1;
                    $rekap_total_nominal = 0;
                    $rekap_total_disetujui = 0;
                @endphp
                @foreach ($rekap as $rk)
                    <tr>
                        <td style="text-align: center;">{{ $rekap_no++ }}</td>
                        <td>{{ $rk['nama_jenis'] }}</td>
                        <td style="text-align: right;">Rp {{ number_format($rk['total_nominal'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($rk['total_disetujui'], 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $rekap_total_nominal += $rk['total_nominal'];
                        $rekap_total_disetujui += $rk['total_disetujui'];
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="2" style="text-align: right;">TOTAL</td>
                    <td style="text-align: right;">Rp {{ number_format($rekap_total_nominal, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($rekap_total_disetujui, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
