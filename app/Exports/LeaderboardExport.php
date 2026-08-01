<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\Judge;
use App\Models\Aspect;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaderboardExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Daftar Pemenang' => new WinnersSheet(),
            'Rekap Detail Nilai' => new DetailedScoresSheet(),
        ];
    }
}

// --- SHEET 1: DAFTAR PEMENANG & PODIUM ---
class WinnersSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Daftar Pemenang';
    }

    public function headings(): array
    {
        return [
            ['SETWAPRES KARAOKE FEST 2026 - DAFTAR PEMENANG RESMI'],
            ['Kategori Juara', 'Nama Tim Peserta', 'Judul Lagu', 'Skor Akhir Rata-Rata', 'Status Favorit']
        ];
    }

    public function collection()
    {
        $totalJudges = Judge::count() > 0 ? Judge::count() : 1;

        $participants = Participant::with('scores')->get()->map(function ($p) use ($totalJudges) {
            $avg = $p->scores->count() > 0 ? $p->scores->sum('total_score') / $totalJudges : 0;
            $p->avg_score = round($avg, 2);
            return $p;
        })->sortByDesc('avg_score')->values();

        $rows = [];
        
        // Juara 1
        if ($c1 = $participants->get(0)) {
            $rows[] = ['JUARA 1', $c1->name, $c1->song_title ?? '-', $c1->avg_score, $c1->is_favorite ? 'Ya' : '-'];
        }
        // Juara 2
        if ($c2 = $participants->get(1)) {
            $rows[] = ['JUARA 2', $c2->name, $c2->song_title ?? '-', $c2->avg_score, $c2->is_favorite ? 'Ya' : '-'];
        }
        // Juara 3
        if ($c3 = $participants->get(2)) {
            $rows[] = ['JUARA 3', $c3->name, $c3->song_title ?? '-', $c3->avg_score, $c3->is_favorite ? 'Ya' : '-'];
        }
        
        // Juara Favorit
        $fav = Participant::where('is_favorite', true)->first();
        if ($fav) {
            $rows[] = ['JUARA FAVORIT', $fav->name, $fav->song_title ?? '-', '-', 'YA (Pilihan Polling)'];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']], 'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]],
        ];
    }
}

// --- SHEET 2: REKAP DETAIL NILAI JURI & ASPEK ---
class DetailedScoresSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Rekap Detail Nilai';
    }

    public function headings(): array
    {
        $judges = Judge::all();
        $aspects = Aspect::all();
        
        $headings = ['No Urut', 'Nama Tim Peserta', 'Judul Lagu'];

        // Buat kolom dinamis berdasarkan juri dan aspeknya
        foreach ($judges as $j) {
            foreach ($aspects as $a) {
                $headings[] = "{$j->name} - {$a->name} ({$a->percentage}%)";
            }
            $headings[] = "Total Nilai ({$j->name})";
        }
        $headings[] = 'Skor Akhir Rata-Rata';

        return [
            ['MATRIKS REKAPITULASI PENILAIAN DEWAN JURI LOMBA KARET CUP'],
            $headings
        ];
    }

    public function collection()
    {
        $participants = Participant::with(['scores.details.aspect', 'scores.judge'])
                                 ->orderBy('order_number')
                                 ->get();
        $judges = Judge::all();
        $aspects = Aspect::all();
        $totalJudges = $judges->count() > 0 ? $judges->count() : 1;

        $rows = [];

        foreach ($participants as $p) {
            $row = [
                $p->order_number,
                $p->name,
                $p->song_title ?? '-'
            ];

            $totalAllJudgesScore = 0;

            foreach ($judges as $j) {
                // Cari score record untuk juri ini
                $judgeScore = $p->scores->where('judge_id', $j->id)->first();
                $judgeTotal = 0;

                foreach ($aspects as $a) {
                    $val = '-';
                    if ($judgeScore) {
                        $detail = $judgeScore->details->where('aspect_id', $a->id)->first();
                        if ($detail) {
                            $val = $detail->score_value; // Nilai mentah
                        }
                    }
                    $row[] = $val;
                }

                if ($judgeScore) {
                    $judgeTotal = $judgeScore->total_score;
                    $totalAllJudgesScore += $judgeTotal;
                }
                
                $row[] = $judgeScore ? number_format($judgeTotal, 2) : '-';
            }

            // Rata-rata akhir
            $avgFinal = $p->scores->count() > 0 ? ($totalAllJudgesScore / $totalJudges) : 0;
            $row[] = number_format($avgFinal, 2);

            $rows[] = $row;
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:O1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF10B981']], 'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]],
        ];
    }
}