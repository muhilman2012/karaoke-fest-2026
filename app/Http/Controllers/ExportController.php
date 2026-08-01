<?php

namespace App\Http\Controllers;

use App\Exports\LeaderboardExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function download()
    {
        return Excel::download(new LeaderboardExport, 'Rekap-Nilai-Setwapres-Karaoke-Fest-2026.xlsx');
    }
}