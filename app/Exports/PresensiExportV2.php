<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PresensiExportV2 implements FromCollection, WithMapping, WithHeadings, WithStyles, WithEvents, WithTitle, ShouldAutoSize, WithCustomStartCell
{
    protected $data;
    protected $periode_dari;
    protected $periode_sampai;
    protected $jmlhari;
    protected $generalsetting;
    protected $libur_nasional_dates;
    protected $datalibur_indexed;
    protected $datalibur_by_tanggal;
    protected $datalembur_indexed;
    protected $denda_list;
    protected $jadwal_bydate;
    protected $jadwal_grup_bydate;
    protected $jadwal_byday;
    protected $jadwal_bydept;
    protected $jadwal_global;
    protected $lembur_khusus_map;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->periode_dari = $data['periode_dari'];
        $this->periode_sampai = $data['periode_sampai'];
        $this->jmlhari = $data['jmlhari'];
        $this->generalsetting = $data['generalsetting'];
        $this->libur_nasional_dates = $data['libur_nasional_dates'] ?? [];
        $this->datalibur_indexed = $data['datalibur_indexed'] ?? [];
        $this->datalibur_by_tanggal = $data['datalibur_by_tanggal'] ?? [];
        $this->datalembur_indexed = $data['datalembur_indexed'] ?? [];
        $this->denda_list = $data['denda_list'] ?? [];
        $this->jadwal_bydate = $data['jadwal_bydate'] ?? [];
        $this->jadwal_grup_bydate = $data['jadwal_grup_bydate'] ?? [];
        $this->jadwal_byday = $data['jadwal_byday'] ?? [];
        $this->jadwal_bydept = $data['jadwal_bydept'] ?? [];
        $this->jadwal_global = $data['jadwal_global'] ?? [];
        $this->lembur_khusus_map = $data['lembur_khusus_map'] ?? [];
    }

    public function collection()
    {
        return $this->data['laporan_presensi'];
    }

    public function title(): string
    {
        return 'Laporan Presensi Format 2';
    }

    public function startCell(): string
    {
        return 'A11';
    }

    public function headings(): array
    {
        return [];
    }

    private $rowNumber = 0;

    public function map($d): array
    {
        $this->rowNumber++;

        $row_data = [
            $this->rowNumber,
            "'" . ($d['nik_show'] ?? $d['nik']),
            $d['nama_karyawan'],
            $d['nama_jabatan'],
            $d['kode_dept'],
        ];

        $tanggal_presensi = $this->periode_dari;
        $total_denda = 0; $total_potongan_jam = 0; $total_jam_lembur = 0;
        $jml_hadir = 0; $jml_sakit = 0; $jml_izin = 0; $jml_cuti = 0;
        $jml_libur = 0; $jml_alfa = 0; $jml_terlambat = 0;
        $jml_pulangcepat = 0; $jml_tidakscanmasuk = 0; $jml_tidakscanpulang = 0;

        $mapJadwalByDate = $this->jadwal_bydate[$d['nik']] ?? [];
        $mapJadwalGrupByDate = $this->jadwal_grup_bydate[$d['nik']] ?? [];
        $mapJadwalByDay = $this->jadwal_byday[$d['nik']] ?? [];
        $lemburKhusus = $this->lembur_khusus_map[$d['nik']] ?? null;

        while (strtotime($tanggal_presensi) <= strtotime($this->periode_sampai)) {
            $denda = 0; $potongan_jam = 0;
            $libur_key = $d['nik'] . '|' . $tanggal_presensi;
            $ceklibur = $this->datalibur_indexed[$libur_key] ?? ($this->datalibur_by_tanggal[$tanggal_presensi] ?? []);
            
            if (!empty($ceklibur)) {
                $is_libur = true;
            } else {
                $has_schedule = false;
                $nama_hari = getHari($tanggal_presensi);
                if (isset($mapJadwalByDate[$tanggal_presensi])) $has_schedule = true;
                elseif (isset($mapJadwalGrupByDate[$tanggal_presensi])) $has_schedule = true;
                elseif (isset($mapJadwalByDay[$nama_hari])) $has_schedule = true;
                else {
                    $keyDC = $d['kode_dept'] . '|' . ($d['kode_cabang'] ?? '');
                    $mapD = $this->jadwal_bydept[$keyDC] ?? [];
                    if (isset($mapD[$nama_hari])) $has_schedule = true;
                    elseif (isset($this->jadwal_global[$nama_hari])) $has_schedule = true;
                }
                $is_libur = !$has_schedule;
            }

            $lembur_key = $d['nik'] . '|' . $tanggal_presensi;
            $ceklembur = $this->datalembur_indexed[$lembur_key] ?? [];
            $row_p = $d[$tanggal_presensi] ?? null;
            $snapshot_lembur = $row_p && $row_p['jam_lembur_aktual'] !== null;

            if ($snapshot_lembur) {
                $jam_netto_harian = $row_p['is_lembur_khusus'] ? $row_p['jam_lembur_aktual'] : $row_p['jam_lembur_netto'];
            } else {
                $lembur_aktual = !empty($ceklembur) ? hitungLembur($ceklembur) : 0;
                $tipe_hari = $is_libur ? 2 : 1;
                $jam_netto_harian = $lembur_aktual > 0 ? hitungJamNetto($lembur_aktual, $tipe_hari) : 0;
                if ($lemburKhusus) $jam_netto_harian = $lembur_aktual;
            }

            $col_vals = ['jadwal' => '-', 'in' => '-', 'out' => '-', 'ist_o' => '-', 'ist_i' => '-', 'lbr' => '-', 'pj' => '-', 'dnd' => '-'];

            if ($row_p) {
                $col_vals['jadwal'] = $row_p['nama_jam_kerja'] . ' (' . date('H:i', strtotime($row_p['jam_masuk'])) . '-' . date('H:i', strtotime($row_p['jam_pulang'])) . ')';
                if ($row_p['status'] == 'h') {
                    $jml_hadir++;
                    $col_vals['in'] = !empty($row_p['jam_in']) ? date('H:i', strtotime($row_p['jam_in'])) : 'X';
                    $col_vals['out'] = !empty($row_p['jam_out']) ? date('H:i', strtotime($row_p['jam_out'])) : 'X';
                    
                    $jam_masuk_ref = $tanggal_presensi . ' ' . $row_p['jam_masuk'];
                    $terlambat = hitungjamterlambat($row_p['jam_in'], $jam_masuk_ref);
                    if ($terlambat && $terlambat['menitterlambat'] > 0) $jml_terlambat++;

                    $denda_db = $row_p['denda'] ?? null;
                    if ($denda_db !== null) { $denda = $denda_db; } 
                    else if ($terlambat) { $denda = $terlambat['desimal_terlambat'] < 1 ? hitungdenda($this->denda_list, $terlambat['menitterlambat']) : 0; }

                    $pc = hitungpulangcepat($tanggal_presensi, $row_p['jam_out'], $row_p['jam_pulang'], $row_p['istirahat'], $row_p['jam_awal_istirahat'], $row_p['jam_akhir_istirahat'], $row_p['lintashari']);
                    if ($pc) $jml_pulangcepat++;

                    $ist_pot = hitungPotonganIstirahat($row_p['istirahat_out'], $row_p['istirahat_in'], $row_p['jam_awal_istirahat'], $row_p['jam_akhir_istirahat']);
                    $no_abs_pot = (empty($row_p['jam_out']) || empty($row_p['jam_in'])) ? $row_p['total_jam'] : 0;
                    $pj_ist_stat = $row_p['status_potongan_istirahat'] ?? $this->generalsetting->potongan_istirahat;
                    
                    $potongan_jam = $no_abs_pot == 0 ? ($pc + ($terlambat && $terlambat['desimal_terlambat'] >= 1 ? $terlambat['desimal_terlambat'] : 0) + ($pj_ist_stat == 1 ? $ist_pot : 0)) : $no_abs_pot;
                    if (($row_p['status_potongan'] ?? $this->generalsetting->status_potongan_jam) == 0) $potongan_jam = 0;

                    $col_vals['ist_o'] = !empty($row_p['istirahat_out']) ? date('H:i', strtotime($row_p['istirahat_out'])) : '-';
                    $col_vals['ist_i'] = !empty($row_p['istirahat_in']) ? date('H:i', strtotime($row_p['istirahat_in'])) : '-';
                    $col_vals['lbr'] = $jam_netto_harian > 0 ? formatAngkaDesimal($jam_netto_harian) : '-';
                    $col_vals['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                    $col_vals['dnd'] = $denda > 0 ? formatAngka($denda) : '-';
                    
                    if (empty($row_p['jam_in'])) $jml_tidakscanmasuk++;
                    if (empty($row_p['jam_out'])) $jml_tidakscanpulang++;
                } else {
                    $status_map = ['i' => 'IZIN', 's' => 'SAKIT', 'c' => 'CUTI', 'a' => 'ALPA'];
                    $col_vals['in'] = $status_map[$row_p['status']] ?? $row_p['status'];
                    if ($row_p['status'] == 'i') $jml_izin++;
                    elseif ($row_p['status'] == 's') $jml_sakit++;
                    elseif ($row_p['status'] == 'c') $jml_cuti++;
                    elseif ($row_p['status'] == 'a') $jml_alfa++;

                    if ($row_p['status'] == 'a' || $row_p['status'] == 'i') {
                       $potongan_jam = ($row_p['status_potongan'] ?? $this->generalsetting->status_potongan_jam) == 1 ? $row_p['total_jam'] : 0;
                       $col_vals['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                    }
                    $denda = $row_p['denda'] ?? 0;
                    $col_vals['dnd'] = $denda > 0 ? formatAngka($denda) : '-';
                }
            } else {
                $is_future = strtotime($tanggal_presensi) > strtotime(date('Y-m-d'));
                if (!empty($ceklibur)) { 
                    $col_vals['in'] = 'LIBUR';
                    $jml_libur++;
                } else {
                    $fallback = $mapJadwalByDate[$tanggal_presensi] ?? ($mapJadwalGrupByDate[$tanggal_presensi] ?? ($mapJadwalByDay[$nama_hari] ?? ($this->jadwal_bydept[$d['kode_dept'].'|'.($d['kode_cabang'] ?? '')][$nama_hari] ?? ($this->jadwal_global[$nama_hari] ?? null))));
                    if (is_array($fallback)) {
                        $col_vals['jadwal'] = $fallback['nama_jam_kerja'] . ' (' . date('H:i', strtotime($fallback['jam_masuk'])) . '-' . date('H:i', strtotime($fallback['jam_pulang'])) . ')';
                        $tJam = $fallback['total_jam'];
                        if ($tJam !== null && !$is_future) {
                            $col_vals['in'] = 'ALPA';
                            $jml_alfa++;
                            $potongan_jam = $this->generalsetting->status_potongan_jam == 1 ? $tJam : 0;
                            $col_vals['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                        }
                    }
                }
                if ($is_libur && empty($ceklibur)) $col_vals['in'] = 'LB-K';
            }

            $row_data = array_merge($row_data, array_values($col_vals));
            $total_denda += $denda;
            $total_potongan_jam += $potongan_jam;
            $total_jam_lembur += $jam_netto_harian;
            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
        }

        $lemburTotalStr = formatAngkaDesimal($total_jam_lembur) . ($lemburKhusus ? ' *' : '');

        return array_merge($row_data, [
            formatAngka($total_denda),
            formatAngkaDesimal($total_potongan_jam),
            $lemburTotalStr,
            $jml_hadir,
            $jml_izin,
            $jml_sakit,
            $jml_alfa,
            $jml_libur,
            $jml_terlambat,
            $jml_tidakscanmasuk,
            $jml_tidakscanpulang,
            $jml_pulangcepat,
        ]);
    }

    public function styles(Worksheet $sheet) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $jmlhari = $this->jmlhari;
                $lastColIdx = 5 + ($jmlhari * 8) + 12 - 1;
                $lastCol = $this->getNameFromNumber($lastColIdx);
                
                // --- Headings ---
                $sheet->setCellValue('A1', 'LAPORAN PRESENSI (FORMAT 2)');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                
                $sheet->setCellValue('A2', $this->generalsetting->nama_perusahaan);
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);

                $sheet->setCellValue('A3', 'PERIODE ' . date('d-m-Y', strtotime($this->periode_dari)) . ' - ' . date('d-m-Y', strtotime($this->periode_sampai)));
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);

                $sheet->setCellValue('A4', $this->generalsetting->alamat);
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->getStyle('A4')->getFont()->setItalic(true);

                $sheet->setCellValue('A5', $this->generalsetting->telepon);
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getStyle('A5')->getFont()->setItalic(true);

                // Headers
                $sheet->setCellValue('A7', 'No'); $sheet->mergeCells('A7:A10');
                $sheet->setCellValue('B7', 'Nik'); $sheet->mergeCells('B7:B10');
                $sheet->setCellValue('C7', 'Nama Karyawan'); $sheet->mergeCells('C7:C10');
                $sheet->setCellValue('D7', 'Jabatan'); $sheet->mergeCells('D7:D10');
                $sheet->setCellValue('E7', 'Dept'); $sheet->mergeCells('E7:E10');
                
                $dateEndColNum = 5 + ($jmlhari * 8) - 1;
                $dateEndCol = $this->getNameFromNumber($dateEndColNum);
                $sheet->setCellValue('F7', 'Tanggal');
                $sheet->mergeCells("F7:{$dateEndCol}7");

                $sheet->setCellValue($this->getNameFromNumber($dateEndColNum + 1) . '7', 'Denda (T)');
                $sheet->mergeCells($this->getNameFromNumber($dateEndColNum + 1) . '7:' . $this->getNameFromNumber($dateEndColNum + 1) . '10');
                
                $sheet->setCellValue($this->getNameFromNumber($dateEndColNum + 2) . '7', 'Pot. Jam (T)');
                $sheet->mergeCells($this->getNameFromNumber($dateEndColNum + 2) . '7:' . $this->getNameFromNumber($dateEndColNum + 2) . '10');

                $sheet->setCellValue($this->getNameFromNumber($dateEndColNum + 3) . '7', 'Lembur (T)');
                $sheet->mergeCells($this->getNameFromNumber($dateEndColNum + 3) . '7:' . $this->getNameFromNumber($dateEndColNum + 3) . '10');

                $rekapStartCol = $this->getNameFromNumber($dateEndColNum + 4);
                $sheet->setCellValue($rekapStartCol . '7', 'Rekap');
                $sheet->mergeCells($rekapStartCol . "7:{$lastCol}7");

                $tanggal = $this->periode_dari; $col = 5;
                while (strtotime($tanggal) <= strtotime($this->periode_sampai)) {
                    $start = $this->getNameFromNumber($col);
                    $end = $this->getNameFromNumber($col + 7);
                    $sheet->setCellValue($start . '8', getHari($tanggal));
                    $sheet->mergeCells("{$start}8:{$end}8");
                    $sheet->setCellValue($start . '9', date('d', strtotime($tanggal)));
                    $sheet->mergeCells("{$start}9:{$end}9");
                    
                    $sheet->setCellValue($this->getNameFromNumber($col) . '10', 'Jadwal');
                    $sheet->setCellValue($this->getNameFromNumber($col + 1) . '10', 'In');
                    $sheet->setCellValue($this->getNameFromNumber($col + 2) . '10', 'Out');
                    $sheet->setCellValue($this->getNameFromNumber($col + 3) . '10', 'Ist-O');
                    $sheet->setCellValue($this->getNameFromNumber($col + 4) . '10', 'Ist-I');
                    $sheet->setCellValue($this->getNameFromNumber($col + 5) . '10', 'Lbr');
                    $sheet->setCellValue($this->getNameFromNumber($col + 6) . '10', 'PJ');
                    $sheet->setCellValue($this->getNameFromNumber($col + 7) . '10', 'Dnd');

                    // Header Specific Colors
                    $sheet->getStyle($this->getNameFromNumber($col) . "10:" . $this->getNameFromNumber($col + 4) . "10")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('056191');
                    $sheet->getStyle($this->getNameFromNumber($col + 5) . "10")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0b99b3');
                    $sheet->getStyle($this->getNameFromNumber($col + 6) . "10:" . $this->getNameFromNumber($col + 7) . "10")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('c0392b');

                    $col += 8;
                    $tanggal = date('Y-m-d', strtotime('+1 day', strtotime($tanggal)));
                }

                $rekapLabels = ['Hadir', 'Izin', 'Sakit', 'Alfa', 'Libur', 'Terlambat', 'T.S.M', 'T.S.P', 'P.C'];
                $rCol = $dateEndColNum + 4;
                foreach ($rekapLabels as $label) {
                    $cName = $this->getNameFromNumber($rCol);
                    $sheet->setCellValue($cName . '8', $label);
                    $sheet->mergeCells("{$cName}8:{$cName}10");
                    $rCol++;
                }

                // Header General Style
                $headerRange = "A7:{$lastCol}10";
                $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A7:{$lastCol}9")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('024a75');
                $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Data Styling
                $rowCount = count($this->data['laporan_presensi']);
                $startDataRow = 11;
                $endDataRow = $startDataRow + $rowCount - 1;
                $sheet->getStyle("A11:{$lastCol}{$endDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A11:{$lastCol}{$endDataRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A11:B{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($this->getNameFromNumber($dateEndColNum + 1) . "11:{$lastCol}{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Conditional Data Colors
                $rowIdx = $startDataRow;
                foreach ($this->data['laporan_presensi'] as $d) {
                    $tanggal_presensi = $this->periode_dari; $colIdx = 5;
                    $mapJadwalByDate = $this->jadwal_bydate[$d['nik']] ?? [];
                    $mapJadwalGrupByDate = $this->jadwal_grup_bydate[$d['nik']] ?? [];
                    $mapJadwalByDay = $this->jadwal_byday[$d['nik']] ?? [];

                    while (strtotime($tanggal_presensi) <= strtotime($this->periode_sampai)) {
                        $is_libur_nasional = isset($this->libur_nasional_dates[$tanggal_presensi]);
                        $ceklibur = $this->datalibur_indexed[$d['nik'].'|'.$tanggal_presensi] ?? ($this->datalibur_by_tanggal[$tanggal_presensi] ?? []);
                        
                        if (!empty($ceklibur)) {
                            $is_libur = true;
                        } else {
                            $has_schedule = false; $nama_hari = getHari($tanggal_presensi);
                            if (isset($mapJadwalByDate[$tanggal_presensi])) $has_schedule = true;
                            elseif (isset($mapJadwalGrupByDate[$tanggal_presensi])) $has_schedule = true;
                            elseif (isset($mapJadwalByDay[$nama_hari])) $has_schedule = true;
                            else {
                                $keyDC = $d['kode_dept'] . '|' . ($d['kode_cabang'] ?? '');
                                $mapD = $this->jadwal_bydept[$keyDC] ?? [];
                                if (isset($mapD[$nama_hari])) $has_schedule = true;
                                elseif (isset($this->jadwal_global[$nama_hari])) $has_schedule = true;
                            }
                            $is_libur = !$has_schedule;
                        }
                        
                        $row_p = $d[$tanggal_presensi] ?? null;
                        $targetRange = $this->getNameFromNumber($colIdx) . $rowIdx . ':' . $this->getNameFromNumber($colIdx + 7) . $rowIdx;
                        $bgcolor = null; $textcolor = '000000';
                        
                        if ($row_p) {
                            if ($row_p['status'] != 'h') {
                                $status_colors = ['i' => 'dea51f', 's' => 'c8075b', 'c' => '0164b5', 'a' => 'ff0000'];
                                $bgcolor = $status_colors[$row_p['status']] ?? null; $textcolor = 'ffffff';
                            }
                        } else {
                            $is_future = strtotime($tanggal_presensi) > strtotime(date('Y-m-d'));
                            if (empty($ceklibur)) {
                                $fallback = $mapJadwalByDate[$tanggal_presensi] ?? ($mapJadwalGrupByDate[$tanggal_presensi] ?? ($mapJadwalByDay[$nama_hari] ?? ($this->jadwal_bydept[$d['kode_dept'].'|'.($d['kode_cabang'] ?? '')][$nama_hari] ?? ($this->jadwal_global[$nama_hari] ?? null))));
                                if (is_array($fallback) && !$is_future) { $bgcolor = 'ff0000'; $textcolor = 'ffffff'; }
                            }
                        }

                        if (!empty($ceklibur)) { $bgcolor = '006400'; $textcolor = 'ffffff'; }
                        elseif ($is_libur) { $bgcolor = 'ffa500'; $textcolor = 'ffffff'; }

                        if ($bgcolor) {
                            $sheet->getStyle($targetRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
                            $sheet->getStyle($targetRange)->getFont()->setColor(new Color($textcolor));
                        }

                        $colIdx += 8;
                        $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                    }
                    $rowIdx++;
                }
            },
        ];
    }

    private function getNameFromNumber($num) {
        $numeric = $num % 26; $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        return ($num2 > 0) ? $this->getNameFromNumber($num2 - 1) . $letter : $letter;
    }
}
