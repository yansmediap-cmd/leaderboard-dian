<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request, LeaderboardService $leaderboardService): JsonResponse
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

        if (! empty($validated['kode_dealer']) && ! $dealerId) {
            return response()->json([
                'message' => 'Dealer tidak ditemukan.',
            ], 404);
        }

        $rows = $leaderboardService->getLeaderboard(
            (int) $validated['bulan'],
            (int) $validated['tahun'],
            $dealerId ? (int) $dealerId : null
        );

        return response()->json([
            'bulan' => (int) $validated['bulan'],
            'tahun' => (int) $validated['tahun'],
            'data' => $rows->map(fn (array $row) => [
                'rank' => $row['rank'],
                'nama_sales' => $row['nama_sales'],
                'nama_dealer' => $row['nama_dealer'],
                'total_spk' => $row['total_spk'],
                'total_do' => $row['total_do'],
                'total_revenue' => $row['total_revenue'],
                'persentase_target' => $row['persentase_target'],
            ])->values(),
        ]);
    }
}
