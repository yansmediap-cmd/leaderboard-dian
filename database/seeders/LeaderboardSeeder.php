<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\DeliveryOrder;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class LeaderboardSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@hondababel.test'],
            ['name' => 'Admin Honda Babel', 'password' => Hash::make('password123'), 'role' => 'admin']
        );
        User::query()->updateOrCreate(
            ['email' => 'viewer@hondababel.test'],
            ['name' => 'Viewer Honda Babel', 'password' => Hash::make('password123'), 'role' => 'viewer']
        );
        User::query()->updateOrCreate(
            ['email' => 'api@hondababel.test'],
            ['name' => 'API Client Dealer', 'password' => Hash::make('password123'), 'role' => 'api']
        );

        $dealerA = Dealer::query()->updateOrCreate(
            ['kode_dealer' => 'BABEL001'],
            ['nama_dealer' => 'Honda Pangkalpinang', 'kota' => 'Pangkalpinang', 'alamat' => 'Jl. Jenderal Sudirman', 'is_active' => true]
        );
        $dealerB = Dealer::query()->updateOrCreate(
            ['kode_dealer' => 'BABEL002'],
            ['nama_dealer' => 'Honda Sungailiat', 'kota' => 'Sungailiat', 'alamat' => 'Jl. Diponegoro', 'is_active' => true]
        );

        foreach ([$dealerA, $dealerB] as $dealer) {
            $dealer->apiWhitelists()->updateOrCreate(
                ['ip_address' => '127.0.0.1'],
                ['is_active' => true]
            );
            $dealer->apiWhitelists()->updateOrCreate(
                ['ip_address' => '::1'],
                ['is_active' => true]
            );
        }

        $salesA1 = Sales::query()->updateOrCreate(
            ['kode_sales' => 'SLS-A-001'],
            ['dealer_id' => $dealerA->id, 'nama_sales' => 'Rudi Hartono', 'target_bulanan' => 18, 'status_aktif' => true, 'no_hp' => '0811111111']
        );
        $salesA2 = Sales::query()->updateOrCreate(
            ['kode_sales' => 'SLS-A-002'],
            ['dealer_id' => $dealerA->id, 'nama_sales' => 'Deni Saputra', 'target_bulanan' => 16, 'status_aktif' => true, 'no_hp' => '0822222222']
        );
        $salesB1 = Sales::query()->updateOrCreate(
            ['kode_sales' => 'SLS-B-001'],
            ['dealer_id' => $dealerB->id, 'nama_sales' => 'Siti Rahma', 'target_bulanan' => 15, 'status_aktif' => true, 'no_hp' => '0833333333']
        );

        $currentDate = Carbon::now()->startOfMonth()->addDays(5);
        $previousDate = Carbon::now()->subMonth()->startOfMonth()->addDays(10);

        $samplePenjualans = [
            [$salesA1, $dealerA, 'SPK-A-1001', 'Beat CBS', $currentDate, 6, 18500000],
            [$salesA2, $dealerA, 'SPK-A-1002', 'Vario 160', $currentDate->copy()->addDays(2), 5, 25500000],
            [$salesB1, $dealerB, 'SPK-B-1001', 'Scoopy', $currentDate->copy()->addDays(3), 7, 23000000],
            [$salesA1, $dealerA, 'SPK-A-0901', 'PCX 160', $previousDate, 4, 33000000],
            [$salesB1, $dealerB, 'SPK-B-0901', 'Genio', $previousDate->copy()->addDays(1), 3, 21000000],
        ];

        foreach ($samplePenjualans as [$sales, $dealer, $noSpk, $tipeMotor, $tanggal, $jumlahUnit, $harga]) {
            Penjualan::query()->updateOrCreate(
                ['no_spk' => $noSpk],
                [
                    'sales_id' => $sales->id,
                    'dealer_id' => $dealer->id,
                    'tipe_motor' => $tipeMotor,
                    'tanggal_spk' => $tanggal->toDateString(),
                    'bulan' => (int) $tanggal->month,
                    'tahun' => (int) $tanggal->year,
                    'jumlah_unit' => $jumlahUnit,
                    'harga_unit' => $harga,
                ]
            );
        }

        $sampleDos = [
            [$salesA1, $dealerA, 'DO-A-1001', $currentDate->copy()->addDays(6), 4],
            [$salesA2, $dealerA, 'DO-A-1002', $currentDate->copy()->addDays(7), 3],
            [$salesB1, $dealerB, 'DO-B-1001', $currentDate->copy()->addDays(8), 5],
            [$salesA1, $dealerA, 'DO-A-0901', $previousDate->copy()->addDays(8), 2],
        ];

        foreach ($sampleDos as [$sales, $dealer, $noDo, $tanggal, $jumlahUnitDo]) {
            DeliveryOrder::query()->updateOrCreate(
                ['no_do' => $noDo],
                [
                    'sales_id' => $sales->id,
                    'dealer_id' => $dealer->id,
                    'tanggal_do' => $tanggal->toDateString(),
                    'jumlah_unit_do' => $jumlahUnitDo,
                ]
            );
        }

        /** @var LeaderboardService $leaderboardService */
        $leaderboardService = app(LeaderboardService::class);
        $leaderboardService->refreshMonthlySummary((int) now()->month, (int) now()->year);
        $leaderboardService->refreshMonthlySummary((int) now()->subMonth()->month, (int) now()->subMonth()->year);
    }
}
