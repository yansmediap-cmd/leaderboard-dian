<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSpkRequest;
use App\Models\Dealer;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SpkController extends Controller
{
    public function store(StoreSpkRequest $request, LeaderboardService $leaderboardService): JsonResponse
    {
        $validated = $request->validated();

        $dealer = Dealer::query()
            ->where('kode_dealer', $validated['kode_dealer'])
            ->where('is_active', true)
            ->first();

        if (! $dealer) {
            return response()->json([
                'message' => 'Dealer tidak ditemukan atau nonaktif.',
            ], 404);
        }

        $sales = Sales::query()
            ->where('kode_sales', $validated['kode_sales'])
            ->where('dealer_id', $dealer->id)
            ->where('status_aktif', true)
            ->first();

        if (! $sales) {
            return response()->json([
                'message' => 'Sales tidak ditemukan untuk dealer terkait.',
            ], 404);
        }

        if (Penjualan::query()->where('no_spk', $validated['no_spk'])->exists()) {
            return response()->json([
                'message' => 'No SPK sudah terdaftar.',
            ], 409);
        }

        $tanggalSpk = Carbon::parse($validated['tanggal_spk']);

        $penjualan = DB::transaction(function () use ($validated, $dealer, $sales, $tanggalSpk) {
            return Penjualan::query()->create([
                'sales_id' => $sales->id,
                'dealer_id' => $dealer->id,
                'no_spk' => $validated['no_spk'],
                'tipe_motor' => $validated['tipe_motor'],
                'tanggal_spk' => $tanggalSpk->toDateString(),
                'bulan' => (int) $tanggalSpk->month,
                'tahun' => (int) $tanggalSpk->year,
                'jumlah_unit' => $validated['jumlah_unit'],
                'harga_unit' => $validated['harga_unit'],
            ]);
        });

        $leaderboardService->refreshMonthlySummary((int) $tanggalSpk->month, (int) $tanggalSpk->year, $dealer->id);

        return response()->json([
            'message' => 'Data SPK berhasil disimpan.',
            'data' => [
                'id' => $penjualan->id,
                'no_spk' => $penjualan->no_spk,
            ],
        ], 201);
    }
}
