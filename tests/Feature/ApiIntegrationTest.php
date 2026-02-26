<?php

namespace Tests\Feature;

use App\Models\Dealer;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_bearer_token(): void
    {
        User::factory()->create([
            'email' => 'api@test.com',
            'password' => Hash::make('password123'),
            'role' => 'api',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api@test.com',
            'password' => 'password123',
            'device_name' => 'integration-test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user']);
    }

    public function test_can_ingest_spk_and_do_then_fetch_leaderboard(): void
    {
        $token = $this->createApiToken();

        $dealer = Dealer::query()->create([
            'kode_dealer' => 'BABEL001',
            'nama_dealer' => 'Honda Pangkalpinang',
            'kota' => 'Pangkalpinang',
            'is_active' => true,
        ]);

        Sales::query()->create([
            'dealer_id' => $dealer->id,
            'kode_sales' => 'SLS001',
            'nama_sales' => 'Rudi',
            'target_bulanan' => 10,
            'status_aktif' => true,
        ]);

        $this->withToken($token)->postJson('/api/spk/store', [
            'kode_dealer' => 'BABEL001',
            'kode_sales' => 'SLS001',
            'no_spk' => 'SPK-001',
            'tipe_motor' => 'Beat',
            'tanggal_spk' => '2026-06-05',
            'jumlah_unit' => 3,
            'harga_unit' => 18000000,
        ])->assertCreated();

        $this->withToken($token)->postJson('/api/do/store', [
            'kode_dealer' => 'BABEL001',
            'kode_sales' => 'SLS001',
            'no_do' => 'DO-001',
            'tanggal_do' => '2026-06-06',
            'jumlah_unit_do' => 2,
        ])->assertCreated();

        $leaderboard = $this->withToken($token)->getJson('/api/leaderboard?bulan=6&tahun=2026');
        $leaderboard->assertOk()
            ->assertJsonPath('data.0.rank', 1)
            ->assertJsonPath('data.0.total_spk', 3)
            ->assertJsonPath('data.0.total_do', 2);
    }

    public function test_duplicate_spk_is_rejected(): void
    {
        $token = $this->createApiToken();

        $dealer = Dealer::query()->create([
            'kode_dealer' => 'BABEL009',
            'nama_dealer' => 'Honda Toboali',
            'kota' => 'Toboali',
            'is_active' => true,
        ]);

        Sales::query()->create([
            'dealer_id' => $dealer->id,
            'kode_sales' => 'SLS009',
            'nama_sales' => 'Dina',
            'target_bulanan' => 8,
            'status_aktif' => true,
        ]);

        $payload = [
            'kode_dealer' => 'BABEL009',
            'kode_sales' => 'SLS009',
            'no_spk' => 'SPK-009',
            'tipe_motor' => 'Scoopy',
            'tanggal_spk' => '2026-06-10',
            'jumlah_unit' => 1,
            'harga_unit' => 23000000,
        ];

        $this->withToken($token)->postJson('/api/spk/store', $payload)->assertCreated();
        $this->withToken($token)->postJson('/api/spk/store', $payload)->assertStatus(409);
    }

    private function createApiToken(): string
    {
        $user = User::factory()->create([
            'role' => 'api',
            'password' => Hash::make('password123'),
        ]);

        return $user->createToken('test')->plainTextToken;
    }
}
