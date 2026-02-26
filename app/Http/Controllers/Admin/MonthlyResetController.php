<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\Penjualan;
use App\Models\SalesMonthlySummary;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyResetController extends Controller
{
    public function store(Request $request, LeaderboardService $leaderboardService): RedirectResponse
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'dealer_id' => ['nullable', 'integer', 'exists:dealers,id'],
        ]);

        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $dealerId = isset($validated['dealer_id']) ? (int) $validated['dealer_id'] : null;

        DB::transaction(function () use ($bulan, $tahun, $dealerId) {
            Penjualan::query()
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->when($dealerId, fn ($query) => $query->where('dealer_id', $dealerId))
                ->delete();

            DeliveryOrder::query()
                ->whereMonth('tanggal_do', $bulan)
                ->whereYear('tanggal_do', $tahun)
                ->when($dealerId, fn ($query) => $query->where('dealer_id', $dealerId))
                ->delete();

            SalesMonthlySummary::query()
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->when($dealerId, fn ($query) => $query->where('dealer_id', $dealerId))
                ->delete();
        });

        $leaderboardService->refreshMonthlySummary($bulan, $tahun, $dealerId);

        return back()->with('status', 'Reset bulanan berhasil diproses.');
    }
}
