<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        [$bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer] = $this->resolveFilters($request);
        $rows = $this->fetchRows($bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer);

        return view('leaderboard.index', $this->buildPayload($rows, $bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer));
    }

    public function data(Request $request): JsonResponse
    {
        [$bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer] = $this->resolveFilters($request);
        $rows = $this->fetchRows($bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer);

        return response()->json($this->buildPayload($rows, $bulan, $tahun, $dealer, $tipeUnit, $jabatan, $limitPerDealer));
    }

    private function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'dealer' => ['nullable', 'string', 'max:150'],
            'tipe_unit' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'limit_per_dealer' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $latestDate = LeaderboardItem::query()
            ->whereNotNull('tanggal_faktur')
            ->max('tanggal_faktur');

        $defaultMonth = $latestDate ? (int) date('n', strtotime((string) $latestDate)) : now()->month;
        $defaultYear = $latestDate ? (int) date('Y', strtotime((string) $latestDate)) : now()->year;
        $dealer = isset($validated['dealer']) ? trim((string) $validated['dealer']) : null;
        $tipeUnit = isset($validated['tipe_unit']) ? trim((string) $validated['tipe_unit']) : null;
        $jabatan = isset($validated['jabatan']) ? trim((string) $validated['jabatan']) : null;
        $limitPerDealer = isset($validated['limit_per_dealer']) ? (int) $validated['limit_per_dealer'] : null;

        return [
            (int) ($validated['bulan'] ?? $defaultMonth),
            (int) ($validated['tahun'] ?? $defaultYear),
            $dealer !== '' ? $dealer : null,
            $tipeUnit !== '' ? $tipeUnit : null,
            $jabatan !== '' ? $jabatan : null,
            $limitPerDealer ?: null,
        ];
    }

    private function fetchRows(
        int $bulan,
        int $tahun,
        ?string $dealer,
        ?string $tipeUnit,
        ?string $jabatan,
        ?int $limitPerDealer
    ): Collection {
        $salesPhotoSubquery = DB::table('sales as s')
            ->selectRaw('LOWER(TRIM(s.nama_sales)) as sales_key, MAX(s.foto_sales) as foto_sales')
            ->groupBy(DB::raw('LOWER(TRIM(s.nama_sales))'));

        $rows = DB::table('leaderboard_items as li')
            ->leftJoinSub($salesPhotoSubquery, 'sp', function ($join) {
                $join->on(DB::raw('LOWER(TRIM(li.nama_sales))'), '=', 'sp.sales_key');
            })
            ->select([
                'li.tanggal_faktur',
                'li.dealer',
                'li.tipe_unit',
                'li.tipe_beli',
                'li.jabatan',
                'li.nama_sales',
                'li.foto_profile',
                DB::raw('sp.foto_sales as master_foto_sales'),
            ])
            ->whereMonth('li.tanggal_faktur', $bulan)
            ->whereYear('li.tanggal_faktur', $tahun)
            ->when($dealer, fn ($query) => $query->where('li.dealer', $dealer))
            ->when($tipeUnit, fn ($query) => $query->where('li.tipe_unit', $tipeUnit))
            ->when($jabatan, fn ($query) => $query->where('li.jabatan', $jabatan))
            ->get()
            ->map(function ($row) {
                return [
                    'tanggal_faktur' => $row->tanggal_faktur,
                    'dealer' => $row->dealer,
                    'tipe_unit' => $row->tipe_unit,
                    'tipe_beli' => $row->tipe_beli,
                    'jabatan' => $row->jabatan,
                    'nama_sales' => $row->nama_sales,
                    'foto_profile' => $row->foto_profile ?: $row->master_foto_sales,
                ];
            });

        if ($rows->isEmpty()) {
            return $rows;
        }

        $summaryRows = $rows
            ->groupBy(fn (array $row) => $this->normalizeSalesDealerKey($row['dealer'], $row['nama_sales']))
            ->map(function (Collection $group) {
                $first = $group->first();
                $dealerName = trim((string) ($first['dealer'] ?? '-'));
                $salesName = trim((string) ($first['nama_sales'] ?? '-'));

                return [
                    'dealer' => $dealerName !== '' ? $dealerName : '-',
                    'nama_sales' => $salesName !== '' ? $salesName : '-',
                    'foto_profile' => $group->pluck('foto_profile')->filter()->first(),
                    'jabatan' => $this->mostFrequentValue($group, 'jabatan'),
                    'tipe_unit' => $this->mostFrequentValue($group, 'tipe_unit'),
                    'tipe_beli' => $this->mostFrequentValue($group, 'tipe_beli'),
                    'total_faktur' => $group->count(),
                    'sort_name' => mb_strtoupper($salesName),
                    'sort_dealer' => mb_strtoupper($dealerName),
                ];
            })
            ->values()
            ->sortBy([
                ['total_faktur', 'desc'],
                ['sort_name', 'asc'],
                ['sort_dealer', 'asc'],
            ])
            ->values();

        if ($limitPerDealer) {
            $summaryRows = $summaryRows
                ->groupBy(fn (array $row) => mb_strtolower(trim((string) $row['dealer'])))
                ->flatMap(function (Collection $dealerRows) use ($limitPerDealer) {
                    return $dealerRows
                        ->sortBy([
                            ['total_faktur', 'desc'],
                            ['sort_name', 'asc'],
                            ['sort_dealer', 'asc'],
                        ])
                        ->values()
                        ->take($limitPerDealer);
                })
                ->values()
                ->sortBy([
                    ['total_faktur', 'desc'],
                    ['sort_name', 'asc'],
                    ['sort_dealer', 'asc'],
                ])
                ->values();
        }

        return $summaryRows
            ->map(function (array $row, int $index) {
                $row['rank'] = $index + 1;
                unset($row['sort_name'], $row['sort_dealer']);

                return $row;
            })
            ->values();
    }

    private function buildPayload(
        Collection $rows,
        int $bulan,
        int $tahun,
        ?string $dealer,
        ?string $tipeUnit,
        ?string $jabatan,
        ?int $limitPerDealer
    ): array {
        $totalSales = $rows
            ->map(fn (array $row) => $this->normalizeSalesDealerKey($row['dealer'], $row['nama_sales']))
            ->unique()
            ->count();

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dealer' => $dealer,
            'tipeUnit' => $tipeUnit,
            'jabatan' => $jabatan,
            'limitPerDealer' => $limitPerDealer,
            'rows' => $rows->all(),
            'totals' => [
                'total_rows' => $rows->count(),
                'total_sales' => $totalSales,
                'total_dealer' => $rows->pluck('dealer')->unique()->count(),
                'total_faktur' => (int) $rows->sum('total_faktur'),
            ],
            'dealers' => LeaderboardItem::query()
                ->select('dealer')
                ->whereNotNull('dealer')
                ->distinct()
                ->orderBy('dealer')
                ->pluck('dealer'),
            'tipeUnits' => LeaderboardItem::query()
                ->select('tipe_unit')
                ->whereNotNull('tipe_unit')
                ->where('tipe_unit', '!=', '')
                ->whereMonth('tanggal_faktur', $bulan)
                ->whereYear('tanggal_faktur', $tahun)
                ->when($dealer, fn ($query) => $query->where('dealer', $dealer))
                ->when($jabatan, fn ($query) => $query->where('jabatan', $jabatan))
                ->distinct()
                ->orderBy('tipe_unit')
                ->pluck('tipe_unit'),
            'jabatans' => LeaderboardItem::query()
                ->select('jabatan')
                ->whereNotNull('jabatan')
                ->where('jabatan', '!=', '')
                ->whereMonth('tanggal_faktur', $bulan)
                ->whereYear('tanggal_faktur', $tahun)
                ->when($dealer, fn ($query) => $query->where('dealer', $dealer))
                ->when($tipeUnit, fn ($query) => $query->where('tipe_unit', $tipeUnit))
                ->distinct()
                ->orderBy('jabatan')
                ->pluck('jabatan'),
            'pollingSeconds' => (int) config('leaderboard.polling_seconds', 30),
        ];
    }

    private function mostFrequentValue(Collection $group, string $field): string
    {
        $value = $group
            ->map(fn (array $row) => trim((string) ($row[$field] ?? '')))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $value ?: '-';
    }

    private function normalizeSalesDealerKey(?string $dealer, ?string $namaSales): string
    {
        return mb_strtolower(trim((string) $dealer).'|'.trim((string) $namaSales));
    }
}
