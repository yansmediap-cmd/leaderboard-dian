<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        return view('admin.penjualans.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'penjualans' => Penjualan::query()
                ->with(['dealer', 'sales'])
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->orderByDesc('tanggal_spk')
                ->paginate(20),
            'dealers' => Dealer::query()->where('is_active', true)->orderBy('nama_dealer')->get(),
            'salesList' => Sales::query()->where('status_aktif', true)->orderBy('nama_sales')->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.penjualans.index');
    }

    public function store(Request $request, LeaderboardService $leaderboardService): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'sales_id' => ['required', 'exists:sales,id'],
            'no_spk' => ['required', 'string', 'max:50', 'unique:penjualans,no_spk'],
            'tipe_motor' => ['required', 'string', 'max:100'],
            'tanggal_spk' => ['required', 'date'],
            'jumlah_unit' => ['required', 'integer', 'min:1'],
            'harga_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $sales = Sales::query()->findOrFail($validated['sales_id']);
        if ((int) $sales->dealer_id !== (int) $validated['dealer_id']) {
            return back()->withErrors(['sales_id' => 'Sales tidak terdaftar pada dealer tersebut.'])->withInput();
        }

        $tanggal = Carbon::parse($validated['tanggal_spk']);
        $entry = Penjualan::query()->create([
            ...$validated,
            'bulan' => (int) $tanggal->month,
            'tahun' => (int) $tanggal->year,
        ]);

        $leaderboardService->refreshMonthlySummary($entry->bulan, $entry->tahun, $entry->dealer_id);

        return back()->with('status', 'Data SPK manual berhasil ditambahkan.');
    }

    public function show(Penjualan $penjualan): RedirectResponse
    {
        return redirect()->route('admin.penjualans.index');
    }

    public function edit(Penjualan $penjualan): RedirectResponse
    {
        return redirect()->route('admin.penjualans.index');
    }

    public function update(Request $request, Penjualan $penjualan, LeaderboardService $leaderboardService): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'sales_id' => ['required', 'exists:sales,id'],
            'no_spk' => ['required', 'string', 'max:50', 'unique:penjualans,no_spk,'.$penjualan->id],
            'tipe_motor' => ['required', 'string', 'max:100'],
            'tanggal_spk' => ['required', 'date'],
            'jumlah_unit' => ['required', 'integer', 'min:1'],
            'harga_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $sales = Sales::query()->findOrFail($validated['sales_id']);
        if ((int) $sales->dealer_id !== (int) $validated['dealer_id']) {
            return back()->withErrors(['sales_id' => 'Sales tidak terdaftar pada dealer tersebut.'])->withInput();
        }

        $oldPeriod = [$penjualan->bulan, $penjualan->tahun, $penjualan->dealer_id];

        $tanggal = Carbon::parse($validated['tanggal_spk']);
        $penjualan->update([
            ...$validated,
            'bulan' => (int) $tanggal->month,
            'tahun' => (int) $tanggal->year,
        ]);

        $leaderboardService->refreshMonthlySummary($oldPeriod[0], $oldPeriod[1], $oldPeriod[2]);
        $leaderboardService->refreshMonthlySummary($penjualan->bulan, $penjualan->tahun, $penjualan->dealer_id);

        return back()->with('status', 'Data SPK berhasil diperbarui.');
    }

    public function destroy(Penjualan $penjualan, LeaderboardService $leaderboardService): RedirectResponse
    {
        $bulan = $penjualan->bulan;
        $tahun = $penjualan->tahun;
        $dealerId = $penjualan->dealer_id;

        $penjualan->delete();
        $leaderboardService->refreshMonthlySummary($bulan, $tahun, $dealerId);

        return back()->with('status', 'Data SPK berhasil dihapus.');
    }
}
