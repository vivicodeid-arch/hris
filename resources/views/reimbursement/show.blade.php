<div class="row g-4 mb-4">
    <!-- Detail Profil -->
    <div class="col-md-7">
        <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-info-circle me-1"></i> Informasi Reimbursement</h6>
        <div class="p-3 bg-lighter rounded-3 border">
            <table class="table table-sm table-borderless m-0" style="font-size: 13px;">
                <tr>
                    <td style="width: 140px; color: #6c757d;" class="fw-medium">No. Pengajuan</td>
                    <td class="fw-bold text-dark">: {{ $reimbursement->no_reimbursement }}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d;" class="fw-medium">Tgl Pengajuan</td>
                    <td>: <span class="fw-medium text-dark">{{ date('d M Y', strtotime($reimbursement->tanggal_pengajuan)) }}</span></td>
                </tr>
                <tr>
                    <td style="color: #6c757d;" class="fw-medium">Karyawan</td>
                    <td>: <span class="fw-bold text-dark">{{ $reimbursement->nama_karyawan }}</span> <span class="text-muted">({{ $reimbursement->nik_show }})</span></td>
                </tr>
                <tr>
                    <td style="color: #6c757d;" class="fw-medium">Unit / Jabatan</td>
                    <td>: <span class="fw-medium text-dark">{{ $reimbursement->nama_cabang }} - {{ $reimbursement->nama_dept }}</span></td>
                </tr>
                @if($reimbursement->catatan)
                <tr>
                    <td style="color: #6c757d;" class="fw-medium align-top">Keterangan Umum</td>
                    <td class="text-wrap">: {{ $reimbursement->catatan }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    
    <!-- Summary -->
    <div class="col-md-5">
        <div class="card bg-label-primary shadow-none h-100 border-0 rounded-3">
            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                <span class="d-block mb-3 text-primary fw-semibold" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Status Saat Ini</span>
                <div class="mb-4">{!! str_replace('badge', 'badge px-3 py-2 fs-6', $reimbursement->status_label) !!}</div>
                
                <div class="mt-auto pt-3 border-top border-primary border-opacity-25">
                    <span class="d-block mb-1 text-primary fw-medium" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Total Klaim</span>
                    <h3 class="mb-0 text-primary fw-bolder">Rp {{ number_format($reimbursement->total_nominal, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<h6 class="fw-bold mb-3"><i class="ti ti-list-details me-1"></i> Rincian Klaim</h6>
<div class="table-responsive mb-4 rounded-3 border">
    <table class="table table-sm table-striped m-0" style="font-size: 13px;">
        <thead class="bg-light">
            <tr>
                <th style="width: 100px;" class="text-center fw-semibold text-muted">Tanggal</th>
                <th class="fw-semibold text-muted">Kategori</th>
                <th class="fw-semibold text-muted">Deskripsi Biaya</th>
                <th class="text-end fw-semibold text-muted" style="width: 130px;">Nominal</th>
                <th class="text-center fw-semibold text-muted" style="width: 110px;">Bukti Nota</th>
            </tr>
        </thead>
        <tbody class="border-top-0">
            @foreach ($details as $d)
                <tr>
                    <td class="text-center align-middle">{{ date('d M Y', strtotime($d->tanggal_transaksi)) }}</td>
                    <td class="align-middle"><span class="badge bg-label-info fw-semibold">{{ $d->nama_jenis }}</span></td>
                    <td class="align-middle text-wrap">{{ $d->keterangan }}</td>
                    <td class="text-end fw-bold align-middle text-dark">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                    <td class="text-center align-middle">
                        @if($d->bukti_file)
                            @php
                                $ext = pathinfo($d->bukti_file, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']))
                                <a href="{{ asset('storage/uploads/reimbursement/' . $d->bukti_file) }}" target="_blank" class="btn btn-xs rounded-pill btn-outline-primary px-3 shadow-sm">
                                    <i class="ti ti-photo me-1"></i> Visual
                                </a>
                            @elseif(strtolower($ext) == 'pdf')
                                <a href="{{ asset('storage/uploads/reimbursement/' . $d->bukti_file) }}" target="_blank" class="btn btn-xs rounded-pill btn-outline-danger px-3 shadow-sm">
                                    <i class="ti ti-file-type-pdf me-1"></i> Doc
                                </a>
                            @else
                                <a href="{{ asset('storage/uploads/reimbursement/' . $d->bukti_file) }}" target="_blank" class="btn btn-xs rounded-pill btn-outline-secondary px-3 shadow-sm">
                                    <i class="ti ti-download me-1"></i> Unduh
                                </a>
                            @endif
                        @else
                            <span class="badge bg-label-secondary fst-italic" style="font-size: 10px;">Tanpa Lampiran</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($approvals->count() > 0)
    <h6 class="fw-bold mb-3"><i class="ti ti-history me-1"></i> Jejak Persetujuan</h6>
    <div class="table-responsive rounded-3 border mb-2">
        <table class="table table-sm m-0" style="font-size: 13px;">
            <thead class="bg-light border-bottom">
                <tr>
                    <th style="width: 90px;" class="text-center fw-medium text-muted">Tahap</th>
                    <th class="fw-medium text-muted">Pemroses</th>
                    <th class="text-center fw-medium text-muted" style="width: 120px;">Keputusan</th>
                    <th style="width: 150px;" class="fw-medium text-muted">Waktu Proses</th>
                    <th class="fw-medium text-muted">Catatan Pemroses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvals as $app)
                    <tr>
                        <td class="text-center align-middle"><span class="badge bg-label-secondary">Tahap {{ $app->level }}</span></td>
                        <td class="fw-bold align-middle text-dark">{{ $app->user_name }}</td>
                        <td class="text-center align-middle">
                            @if($app->status == 'approved')
                                <span class="badge bg-success rounded-pill px-3 shadow-sm"><i class="ti ti-check me-1"></i> Setuju</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 shadow-sm"><i class="ti ti-x me-1"></i> Tolak</span>
                            @endif
                        </td>
                        <td class="align-middle text-muted">{{ date('d M Y, H:i', strtotime($app->created_at)) }}</td>
                        <td class="align-middle"><span class="text-muted fst-italic">{{ $app->keterangan ?: '-' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($can_approve && $reimbursement->status == 'P')
    <div class="p-4 bg-lighter border rounded-3 mb-4 shadow-sm">
        <form action="{{ route('reimbursement.storeapprove', Crypt::encrypt($reimbursement->no_reimbursement)) }}" method="POST" id="fmApproveReimbursement">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold mb-2 text-dark d-flex align-items-center">
                    <i class="ti ti-message-dots me-2 text-primary fs-5"></i> 
                    <span>Catatan Persetujuan</span>
                    @if($is_super_admin)
                         <span class="badge bg-label-warning ms-2 shadow-xs" style="font-size: 9px; letter-spacing: 0.5px;">SUPER ADMIN BYPASS</span>
                    @endif
                </label>
                <textarea name="keterangan" class="form-control border-light-subtle shadow-none" rows="2" placeholder="Masukan catatan atau alasan jika diperlukan..."></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-2">
                 <button type="submit" name="tolak" value="tolak" class="btn btn-label-danger fw-bold px-4">
                    <i class="ti ti-x me-1"></i> Tolak
                </button>
                <button type="submit" name="approve" value="approve" class="btn btn-primary fw-bold px-4 shadow-md">
                    <i class="ti ti-check me-1"></i> {{ $is_super_admin ? 'Setujui Langsung' : 'Setujui' }}
                </button>
            </div>
        </form>
    </div>
@endif

<div class="text-end pt-3 mt-4 border-top d-flex justify-content-between align-items-center">
    <div>
        @if($reimbursement->status != 'P' && ($is_super_admin || (isset($approvals->last()->user_id) && $approvals->last()->user_id == auth()->id())))
            <form action="{{ route('reimbursement.cancelapprove', Crypt::encrypt($reimbursement->no_reimbursement)) }}" method="POST" class="d-inline ms-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-label-danger btn-sm fw-bold px-3 delete-confirm">
                    <i class="ti ti-rotate-clockwise-2 me-1"></i> Batalkan Approval Terakhir
                </button>
            </form>
        @endif
    </div>
    <div>
        <a href="{{ route('reimbursement.print', Crypt::encrypt($reimbursement->id)) }}" target="_blank" class="btn btn-outline-primary fw-semibold px-3 me-2 btn-sm">
            <i class="ti ti-printer me-1"></i> Cetak Ajuan
        </a>
        <button type="button" class="btn btn-label-secondary fw-semibold px-4 shadow-xs btn-sm" data-bs-dismiss="modal">
            <i class="ti ti-arrow-left me-1"></i> Kembali
        </button>
    </div>
</div>
