<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDoRequest;
use App\Models\Dealer;
use App\Models\DeliveryOrder;
use App\Models\Sales;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DoController extends Controller
{
    public function store(StoreDoRequest $request, LeaderboardService $leaderboardService): JsonResponse
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

        if (DeliveryOrder::query()->where('no_do', $validated['no_do'])->exists()) {
            return response()->json([
                'message' => 'No DO sudah terdaftar.',
            ], 409);
        }

        $tanggalDo = Carbon::parse($validated['tanggal_do']);

        $deliveryOrder = DB::transaction(function () use ($validated, $dealer, $sales, $tanggalDo) {
            return DeliveryOrder::query()->create([
                'sales_id' => $sales->id,
                'dealer_id' => $dealer->id,
                'no_do' => $validated['no_do'],
                'tanggal_do' => $tanggalDo->toDateString(),
                'jumlah_unit_do' => $validated['jumlah_unit_do'],
            ]);
        });

        $leaderboardService->refreshMonthlySummary((int) $tanggalDo->month, (int) $tanggalDo->year, $dealer->id);

        return response()->json([
            'message' => 'Data DO berhasil disimpan.',
            'data' => [
                'id' => $deliveryOrder->id,
                'no_do' => $deliveryOrder->no_do,
            ],
        ], 201);
    }
}
