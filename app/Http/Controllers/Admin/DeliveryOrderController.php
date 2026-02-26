<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\DeliveryOrder;
use App\Models\Sales;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        return view('admin.delivery-orders.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'deliveryOrders' => DeliveryOrder::query()
                ->with(['dealer', 'sales'])
                ->whereMonth('tanggal_do', $bulan)
                ->whereYear('tanggal_do', $tahun)
                ->orderByDesc('tanggal_do')
                ->paginate(20),
            'dealers' => Dealer::query()->where('is_active', true)->orderBy('nama_dealer')->get(),
            'salesList' => Sales::query()->where('status_aktif', true)->orderBy('nama_sales')->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.delivery-orders.index');
    }

    public function store(Request $request, LeaderboardService $leaderboardService): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'sales_id' => ['required', 'exists:sales,id'],
            'no_do' => ['required', 'string', 'max:50', 'unique:delivery_orders,no_do'],
            'tanggal_do' => ['required', 'date'],
            'jumlah_unit_do' => ['required', 'integer', 'min:1'],
        ]);

        $sales = Sales::query()->findOrFail($validated['sales_id']);
        if ((int) $sales->dealer_id !== (int) $validated['dealer_id']) {
            return back()->withErrors(['sales_id' => 'Sales tidak terdaftar pada dealer tersebut.'])->withInput();
        }

        $tanggal = Carbon::parse($validated['tanggal_do']);
        $entry = DeliveryOrder::query()->create([
            ...$validated,
            'tanggal_do' => $tanggal->toDateString(),
        ]);

        $leaderboardService->refreshMonthlySummary((int) $tanggal->month, (int) $tanggal->year, $entry->dealer_id);

        return back()->with('status', 'Data DO manual berhasil ditambahkan.');
    }

    public function show(DeliveryOrder $deliveryOrder): RedirectResponse
    {
        return redirect()->route('admin.delivery-orders.index');
    }

    public function edit(DeliveryOrder $deliveryOrder): RedirectResponse
    {
        return redirect()->route('admin.delivery-orders.index');
    }

    public function update(Request $request, DeliveryOrder $deliveryOrder, LeaderboardService $leaderboardService): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'sales_id' => ['required', 'exists:sales,id'],
            'no_do' => ['required', 'string', 'max:50', 'unique:delivery_orders,no_do,'.$deliveryOrder->id],
            'tanggal_do' => ['required', 'date'],
            'jumlah_unit_do' => ['required', 'integer', 'min:1'],
        ]);

        $sales = Sales::query()->findOrFail($validated['sales_id']);
        if ((int) $sales->dealer_id !== (int) $validated['dealer_id']) {
            return back()->withErrors(['sales_id' => 'Sales tidak terdaftar pada dealer tersebut.'])->withInput();
        }

        $oldDate = Carbon::parse($deliveryOrder->tanggal_do);
        $newDate = Carbon::parse($validated['tanggal_do']);

        $deliveryOrder->update([
            ...$validated,
            'tanggal_do' => $newDate->toDateString(),
        ]);

        $leaderboardService->refreshMonthlySummary((int) $oldDate->month, (int) $oldDate->year, $deliveryOrder->dealer_id);
        $leaderboardService->refreshMonthlySummary((int) $newDate->month, (int) $newDate->year, (int) $validated['dealer_id']);

        return back()->with('status', 'Data DO berhasil diperbarui.');
    }

    public function destroy(DeliveryOrder $deliveryOrder, LeaderboardService $leaderboardService): RedirectResponse
    {
        $date = Carbon::parse($deliveryOrder->tanggal_do);
        $dealerId = $deliveryOrder->dealer_id;

        $deliveryOrder->delete();
        $leaderboardService->refreshMonthlySummary((int) $date->month, (int) $date->year, $dealerId);

        return back()->with('status', 'Data DO berhasil dihapus.');
    }
}
