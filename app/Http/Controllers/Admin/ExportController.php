<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LeaderboardExport;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function leaderboard(Request $request, LeaderboardService $leaderboardService)
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'dealer_id' => ['nullable', 'integer'],
            'kode_dealer' => ['nullable', 'string', 'max:20'],
        ]);

        $dealerId = $validated['dealer_id'] ?? null;
        if (! $dealerId && ! empty($validated['kode_dealer'])) {
            $dealerId = Dealer::query()
                ->where('kode_dealer', $validated['kode_dealer'])
                ->value('id');
        }

        $rows = $leaderboardService->getLeaderboard(
            (int) $validated['bulan'],
            (int) $validated['tahun'],
            $dealerId ? (int) $dealerId : null
        );

        $fileName = sprintf(
            'leaderboard-honda-babel-%d-%d.xlsx',
            (int) $validated['tahun'],
            (int) $validated['bulan']
        );

        return Excel::download(
            new LeaderboardExport($rows, (int) $validated['bulan'], (int) $validated['tahun']),
            $fileName
        );
    }
}
