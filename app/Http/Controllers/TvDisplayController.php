<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TvDisplayController extends Controller
{
    public function index(Request $request): View
    {
        $latestDate = LeaderboardItem::query()
            ->whereNotNull('tanggal_faktur')
            ->max('tanggal_faktur');

        $defaultMonth = $latestDate ? (int) date('n', strtotime((string) $latestDate)) : now()->month;
        $defaultYear = $latestDate ? (int) date('Y', strtotime((string) $latestDate)) : now()->year;

        return view('leaderboard.tv', [
            'bulan' => (int) $request->query('bulan', $defaultMonth),
            'tahun' => (int) $request->query('tahun', $defaultYear),
            'dealer' => $request->query('dealer'),
            'pollingSeconds' => (int) config('leaderboard.polling_seconds', 30),
        ]);
    }
}
