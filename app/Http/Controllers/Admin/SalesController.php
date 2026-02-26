<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Sales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(): View
    {
        return view('admin.sales.index', [
            'salesList' => Sales::query()
                ->with('dealer')
                ->orderByDesc('created_at')
                ->paginate(15),
            'dealers' => Dealer::query()
                ->where('is_active', true)
                ->orderBy('nama_dealer')
                ->get(['id', 'nama_dealer']),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.sales.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'kode_sales' => ['required', 'string', 'max:30', 'unique:sales,kode_sales'],
            'nama_sales' => ['required', 'string', 'max:100'],
            'foto_sales' => ['nullable', 'image', 'max:2048'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'target_bulanan' => ['nullable', 'integer', 'min:0'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('foto_sales')) {
            $validated['foto_sales'] = $request->file('foto_sales')->store('sales-photos', 'public');
        }

        $validated['status_aktif'] = (bool) ($validated['status_aktif'] ?? true);
        $validated['target_bulanan'] = (int) ($validated['target_bulanan'] ?? 0);

        Sales::query()->create($validated);

        return back()->with('status', 'Sales berhasil ditambahkan.');
    }

    public function show(Sales $sale): RedirectResponse
    {
        return redirect()->route('admin.sales.index');
    }

    public function edit(Sales $sale): RedirectResponse
    {
        return redirect()->route('admin.sales.index');
    }

    public function update(Request $request, Sales $sale): RedirectResponse
    {
        $validated = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'kode_sales' => ['required', 'string', 'max:30', 'unique:sales,kode_sales,'.$sale->id],
            'nama_sales' => ['required', 'string', 'max:100'],
            'foto_sales' => ['nullable', 'image', 'max:2048'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'target_bulanan' => ['nullable', 'integer', 'min:0'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('foto_sales')) {
            if ($sale->foto_sales) {
                Storage::disk('public')->delete($sale->foto_sales);
            }
            $validated['foto_sales'] = $request->file('foto_sales')->store('sales-photos', 'public');
        }

        $validated['status_aktif'] = (bool) ($validated['status_aktif'] ?? false);
        $validated['target_bulanan'] = (int) ($validated['target_bulanan'] ?? 0);

        $sale->update($validated);

        return back()->with('status', 'Sales berhasil diperbarui.');
    }

    public function destroy(Sales $sale): RedirectResponse
    {
        if ($sale->foto_sales) {
            Storage::disk('public')->delete($sale->foto_sales);
        }

        $sale->delete();

        return back()->with('status', 'Sales berhasil dihapus.');
    }
}
