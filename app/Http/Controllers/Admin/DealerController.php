<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(): View
    {
        return view('admin.dealers.index', [
            'dealers' => Dealer::query()
                ->with('apiWhitelists')
                ->orderBy('nama_dealer')
                ->paginate(15),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.dealers.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_dealer' => ['required', 'string', 'max:20', 'unique:dealers,kode_dealer'],
            'nama_dealer' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'whitelist_ips' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $dealer = Dealer::query()->create([
                'kode_dealer' => $validated['kode_dealer'],
                'nama_dealer' => $validated['nama_dealer'],
                'alamat' => $validated['alamat'] ?? null,
                'kota' => $validated['kota'],
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            $this->syncWhitelistIps($dealer, $validated['whitelist_ips'] ?? null);
        });

        return back()->with('status', 'Dealer berhasil ditambahkan.');
    }

    public function show(Dealer $dealer): RedirectResponse
    {
        return redirect()->route('admin.dealers.index');
    }

    public function edit(Dealer $dealer): RedirectResponse
    {
        return redirect()->route('admin.dealers.index');
    }

    public function update(Request $request, Dealer $dealer): RedirectResponse
    {
        $validated = $request->validate([
            'kode_dealer' => ['required', 'string', 'max:20', 'unique:dealers,kode_dealer,'.$dealer->id],
            'nama_dealer' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'whitelist_ips' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $dealer) {
            $dealer->update([
                'kode_dealer' => $validated['kode_dealer'],
                'nama_dealer' => $validated['nama_dealer'],
                'alamat' => $validated['alamat'] ?? null,
                'kota' => $validated['kota'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncWhitelistIps($dealer, $validated['whitelist_ips'] ?? null);
        });

        return back()->with('status', 'Dealer berhasil diperbarui.');
    }

    public function destroy(Dealer $dealer): RedirectResponse
    {
        $dealer->delete();

        return back()->with('status', 'Dealer berhasil dihapus.');
    }

    private function syncWhitelistIps(Dealer $dealer, ?string $rawIps): void
    {
        $ips = collect(preg_split('/[\r\n,;]+/', (string) $rawIps))
            ->map(fn ($ip) => trim((string) $ip))
            ->filter()
            ->unique()
            ->values();

        $dealer->apiWhitelists()->delete();

        if ($ips->isEmpty()) {
            return;
        }

        $dealer->apiWhitelists()->createMany(
            $ips->map(fn (string $ip) => [
                'ip_address' => $ip,
                'is_active' => true,
            ])->all()
        );
    }
}
