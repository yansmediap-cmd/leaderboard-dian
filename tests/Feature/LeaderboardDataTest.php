<?php

namespace Tests\Feature;

use App\Models\LeaderboardItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class LeaderboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_endpoint_returns_global_ranking_for_all_sales(): void
    {
        $this->seedLeaderboardRowsForGlobalRankingScenario();

        $response = $this->getJson('/leaderboard/data?bulan=1&tahun=2026');

        $response->assertOk()
            ->assertJsonPath('totals.total_rows', 6)
            ->assertJsonPath('totals.total_sales', 6)
            ->assertJsonPath('totals.total_dealer', 2)
            ->assertJsonPath('totals.total_faktur', 13);

        $rows = collect($response->json('rows'))
            ->map(fn (array $row) => Arr::only($row, ['dealer', 'nama_sales', 'rank', 'total_faktur']))
            ->all();

        $this->assertEquals([
            ['dealer' => 'Dealer B', 'nama_sales' => 'Dina', 'rank' => 1, 'total_faktur' => 4],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Alice', 'rank' => 2, 'total_faktur' => 3],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Bob', 'rank' => 3, 'total_faktur' => 2],
            ['dealer' => 'Dealer B', 'nama_sales' => 'Eko', 'rank' => 4, 'total_faktur' => 2],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Charlie', 'rank' => 5, 'total_faktur' => 1],
            ['dealer' => 'Dealer B', 'nama_sales' => 'Feri', 'rank' => 6, 'total_faktur' => 1],
        ], $rows);
    }

    public function test_data_endpoint_filters_by_tipe_unit_and_recalculates_rank(): void
    {
        $this->seedLeaderboardRowsForTipeUnitFilterScenario();

        $response = $this->getJson('/leaderboard/data?bulan=1&tahun=2026&tipe_unit=Vario');

        $response->assertOk()
            ->assertJsonPath('tipeUnit', 'Vario')
            ->assertJsonPath('totals.total_rows', 4)
            ->assertJsonPath('totals.total_faktur', 7);

        $rows = collect($response->json('rows'))
            ->map(fn (array $row) => Arr::only($row, ['dealer', 'nama_sales', 'rank', 'total_faktur', 'tipe_unit']))
            ->all();

        $this->assertEquals([
            ['dealer' => 'Dealer B', 'nama_sales' => 'Dina', 'rank' => 1, 'total_faktur' => 3, 'tipe_unit' => 'Vario'],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Bob', 'rank' => 2, 'total_faktur' => 2, 'tipe_unit' => 'Vario'],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Alice', 'rank' => 3, 'total_faktur' => 1, 'tipe_unit' => 'Vario'],
            ['dealer' => 'Dealer B', 'nama_sales' => 'Feri', 'rank' => 4, 'total_faktur' => 1, 'tipe_unit' => 'Vario'],
        ], $rows);
    }

    public function test_data_endpoint_filters_by_jabatan(): void
    {
        $this->seedLeaderboardRowsForJabatanFilterScenario();

        $response = $this->getJson('/leaderboard/data?bulan=1&tahun=2026&jabatan=SUPERVISOR');

        $response->assertOk()
            ->assertJsonPath('jabatan', 'SUPERVISOR')
            ->assertJsonPath('totals.total_rows', 3)
            ->assertJsonPath('totals.total_faktur', 6);

        $rows = collect($response->json('rows'))
            ->map(fn (array $row) => Arr::only($row, ['dealer', 'nama_sales', 'rank', 'total_faktur', 'jabatan']))
            ->all();

        $this->assertEquals([
            ['dealer' => 'Dealer B', 'nama_sales' => 'Dina', 'rank' => 1, 'total_faktur' => 4, 'jabatan' => 'SUPERVISOR'],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Charlie', 'rank' => 2, 'total_faktur' => 1, 'jabatan' => 'SUPERVISOR'],
            ['dealer' => 'Dealer B', 'nama_sales' => 'Feri', 'rank' => 3, 'total_faktur' => 1, 'jabatan' => 'SUPERVISOR'],
        ], $rows);
    }

    public function test_data_endpoint_limits_sales_per_dealer(): void
    {
        $this->seedLeaderboardRowsForGlobalRankingScenario();

        $response = $this->getJson('/leaderboard/data?bulan=1&tahun=2026&limit_per_dealer=1');

        $response->assertOk()
            ->assertJsonPath('limitPerDealer', 1)
            ->assertJsonPath('totals.total_rows', 2)
            ->assertJsonPath('totals.total_sales', 2)
            ->assertJsonPath('totals.total_faktur', 7);

        $rows = collect($response->json('rows'))
            ->map(fn (array $row) => Arr::only($row, ['dealer', 'nama_sales', 'rank', 'total_faktur']))
            ->all();

        $this->assertEquals([
            ['dealer' => 'Dealer B', 'nama_sales' => 'Dina', 'rank' => 1, 'total_faktur' => 4],
            ['dealer' => 'Dealer A', 'nama_sales' => 'Alice', 'rank' => 2, 'total_faktur' => 3],
        ], $rows);
    }

    private function seedLeaderboardRowsForGlobalRankingScenario(): void
    {
        foreach (range(1, 3) as $index) {
            $this->createItem('Dealer A', 'Alice', $index === 1 ? 'Beat' : 'Vario');
        }

        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer A', 'Bob', $index === 1 ? 'Beat' : 'Vario');
        }

        $this->createItem('Dealer A', 'Charlie', 'Scoopy');

        foreach (range(1, 4) as $index) {
            $this->createItem('Dealer B', 'Dina', $index <= 2 ? 'Vario' : 'Beat');
        }

        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer B', 'Eko', $index === 1 ? 'Beat' : 'Vario');
        }

        $this->createItem('Dealer B', 'Feri', 'Scoopy');
    }

    private function seedLeaderboardRowsForTipeUnitFilterScenario(): void
    {
        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer A', 'Alice', $index === 1 ? 'Vario' : 'Beat');
        }
        $this->createItem('Dealer A', 'Alice', 'Beat');
        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer A', 'Bob', 'Vario');
        }
        $this->createItem('Dealer A', 'Charlie', 'Beat');

        foreach (range(1, 3) as $index) {
            $this->createItem('Dealer B', 'Dina', 'Vario');
        }
        $this->createItem('Dealer B', 'Eko', 'Beat');
        $this->createItem('Dealer B', 'Feri', 'Vario');
    }

    private function seedLeaderboardRowsForJabatanFilterScenario(): void
    {
        foreach (range(1, 3) as $index) {
            $this->createItem('Dealer A', 'Alice', $index === 1 ? 'Beat' : 'Vario', 'COUNTER SALES');
        }

        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer A', 'Bob', $index === 1 ? 'Beat' : 'Vario', 'COUNTER SALES');
        }

        $this->createItem('Dealer A', 'Charlie', 'Scoopy', 'SUPERVISOR');

        foreach (range(1, 4) as $index) {
            $this->createItem('Dealer B', 'Dina', $index <= 2 ? 'Vario' : 'Beat', 'SUPERVISOR');
        }

        foreach (range(1, 2) as $index) {
            $this->createItem('Dealer B', 'Eko', $index === 1 ? 'Beat' : 'Vario', 'COUNTER SALES');
        }

        $this->createItem('Dealer B', 'Feri', 'Scoopy', 'SUPERVISOR');
    }

    private function createItem(string $dealer, string $namaSales, string $tipeUnit, string $jabatan = 'COUNTER SALES'): void
    {
        LeaderboardItem::query()->create([
            'tanggal_faktur' => '2026-01-10',
            'dealer' => $dealer,
            'tipe_unit' => $tipeUnit,
            'tipe_beli' => 'CASH',
            'jabatan' => $jabatan,
            'nama_sales' => $namaSales,
        ]);
    }
}
