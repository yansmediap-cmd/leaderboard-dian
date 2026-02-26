<?php

namespace App\Services;

use App\Models\Sales;
use App\Models\SalesMonthlySummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function getLeaderboard(int $bulan, int $tahun, ?int $dealerId = null, bool $useCache = true): Collection
    {
        $cacheKey = $this->cacheKey($bulan, $tahun, $dealerId);
        $ttl = now()->addMinutes((int) config('leaderboard.cache_ttl_minutes', 5));

        if (! $useCache) {
            return $this->buildLeaderboard($bulan, $tahun, $dealerId);
        }

        return Cache::remember($cacheKey, $ttl, fn () => $this->buildLeaderboard($bulan, $tahun, $dealerId));
    }

    public function refreshMonthlySummary(int $bulan, int $tahun, ?int $dealerId = null): void
    {
        $rows = $this->getLeaderboard($bulan, $tahun, $dealerId, false);

        $payload = $rows->map(function (array $row) use ($bulan, $tahun) {
            return [
                'sales_id' => $row['sales_id'],
                'dealer_id' => $row['dealer_id'],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_spk' => $row['total_spk'],
                'total_do' => $row['total_do'],
                'total_revenue' => $row['total_revenue'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        })->all();

        DB::transaction(function () use ($payload, $bulan, $tahun, $dealerId) {
            $query = SalesMonthlySummary::query()
                ->where('bulan', $bulan)
                ->where('tahun', $tahun);

            if ($dealerId) {
                $query->where('dealer_id', $dealerId);
            }

            $query->delete();

            if (! empty($payload)) {
                SalesMonthlySummary::insert($payload);
            }
        });

        $this->invalidateCache($bulan, $tahun, $dealerId);
    }

    public function invalidateCache(int $bulan, int $tahun, ?int $dealerId = null): void
    {
        Cache::forget($this->cacheKey($bulan, $tahun, null));

        if ($dealerId) {
            Cache::forget($this->cacheKey($bulan, $tahun, $dealerId));
        }
    }

    public function extractMonthYear(string $date): array
    {
        $parsed = Carbon::parse($date);

        return [(int) $parsed->month, (int) $parsed->year];
    }

    private function buildLeaderboard(int $bulan, int $tahun, ?int $dealerId = null): Collection
    {
        $spkSubQuery = DB::table('penjualans')
            ->selectRaw('sales_id, dealer_id, SUM(jumlah_unit) as total_spk, SUM(total_harga) as total_revenue')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->groupBy('sales_id', 'dealer_id');

        $doSubQuery = DB::table('delivery_orders')
            ->selectRaw('sales_id, dealer_id, SUM(jumlah_unit_do) as total_do')
            ->whereMonth('tanggal_do', $bulan)
            ->whereYear('tanggal_do', $tahun)
            ->groupBy('sales_id', 'dealer_id');

        $query = Sales::query()
            ->select([
                'sales.id as sales_id',
                'sales.nama_sales',
                'sales.foto_sales',
                'sales.target_bulanan',
                'dealers.id as dealer_id',
                'dealers.nama_dealer',
                DB::raw('COALESCE(spk.total_spk, 0) as total_spk'),
                DB::raw('COALESCE(delivery.total_do, 0) as total_do'),
                DB::raw('COALESCE(spk.total_revenue, 0) as total_revenue'),
            ])
            ->join('dealers', 'dealers.id', '=', 'sales.dealer_id')
            ->leftJoinSub($spkSubQuery, 'spk', function ($join) {
                $join->on('spk.sales_id', '=', 'sales.id')
                    ->on('spk.dealer_id', '=', 'sales.dealer_id');
            })
            ->leftJoinSub($doSubQuery, 'delivery', function ($join) {
                $join->on('delivery.sales_id', '=', 'sales.id')
                    ->on('delivery.dealer_id', '=', 'sales.dealer_id');
            })
            ->where('sales.status_aktif', true)
            ->where('dealers.is_active', true);

        if ($dealerId) {
            $query->where('sales.dealer_id', $dealerId);
        }

        return $query
            ->orderByDesc('total_spk')
            ->orderByDesc('total_do')
            ->orderByDesc('total_revenue')
            ->orderBy('sales.nama_sales')
            ->get()
            ->values()
            ->map(function ($row, int $index) {
                $target = (int) $row->target_bulanan;
                $totalSpk = (int) $row->total_spk;

                return [
                    'rank' => $index + 1,
                    'sales_id' => (int) $row->sales_id,
                    'nama_sales' => $row->nama_sales,
                    'foto_sales' => $row->foto_sales,
                    'dealer_id' => (int) $row->dealer_id,
                    'nama_dealer' => $row->nama_dealer,
                    'target_bulanan' => $target,
                    'total_spk' => $totalSpk,
                    'total_do' => (int) $row->total_do,
                    'total_revenue' => (float) $row->total_revenue,
                    'persentase_target' => $target > 0
                        ? round(($totalSpk / $target) * 100, 2)
                        : 0.0,
                ];
            });
    }

    private function cacheKey(int $bulan, int $tahun, ?int $dealerId = null): string
    {
        return sprintf('leaderboard:%d:%d:%s', $bulan, $tahun, $dealerId ?? 'all');
    }
}
