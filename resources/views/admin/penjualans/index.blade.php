@extends('layouts.app')

@section('content')
    @include('admin.partials.menu')

    <section class="card-glass mb-6 p-5">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs text-white/70">Bulan</label>
                    <input type="number" name="bulan" value="{{ $bulan }}" min="1" max="12" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-white/70">Tahun</label>
                    <input type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                </div>
                <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold hover:bg-red-500">Filter</button>
            </form>

            <a class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200 hover:bg-emerald-500/20" href="{{ route('admin.export.leaderboard', ['bulan' => $bulan, 'tahun' => $tahun]) }}">
                Export Excel
            </a>

            <form method="POST" action="{{ route('admin.monthly-reset.store') }}" onsubmit="return confirm('Yakin reset data SPK & DO bulan ini?')">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button class="rounded-lg border border-red-500/50 bg-red-600/20 px-4 py-2 text-sm text-red-100 hover:bg-red-600/35">Reset Bulanan</button>
            </form>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Input SPK Manual</h2>
            <form method="POST" action="{{ route('admin.penjualans.store') }}" class="space-y-3">
                @csrf
                <select name="dealer_id" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <option value="">Pilih Dealer</option>
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->id }}">{{ $dealer->nama_dealer }}</option>
                    @endforeach
                </select>
                <select name="sales_id" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <option value="">Pilih Sales</option>
                    @foreach($salesList as $sales)
                        <option value="{{ $sales->id }}">{{ $sales->nama_sales }} ({{ $sales->kode_sales }})</option>
                    @endforeach
                </select>
                <input name="no_spk" placeholder="No SPK" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <input name="tipe_motor" placeholder="Tipe Motor" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <div class="grid gap-2 md:grid-cols-3">
                    <input name="tanggal_spk" type="date" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <input name="jumlah_unit" type="number" min="1" required placeholder="Jumlah Unit" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <input name="harga_unit" type="number" min="0" step="0.01" required placeholder="Harga Unit" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                </div>
                <button class="rounded-lg bg-red-600 px-4 py-2 font-semibold hover:bg-red-500">Simpan SPK</button>
            </form>
        </section>

        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Data SPK Bulan Berjalan</h2>
            <div class="max-h-[560px] overflow-auto pr-2">
                <div class="space-y-4">
                    @foreach($penjualans as $penjualan)
                        <article class="rounded-xl border border-white/15 bg-black/25 p-4">
                            <form method="POST" action="{{ route('admin.penjualans.update', $penjualan) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-2 md:grid-cols-2">
                                    <select name="dealer_id" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                        @foreach($dealers as $dealer)
                                            <option value="{{ $dealer->id }}" @selected((int) $penjualan->dealer_id === (int) $dealer->id)>{{ $dealer->nama_dealer }}</option>
                                        @endforeach
                                    </select>
                                    <select name="sales_id" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                        @foreach($salesList as $sales)
                                            <option value="{{ $sales->id }}" @selected((int) $penjualan->sales_id === (int) $sales->id)>{{ $sales->nama_sales }}</option>
                                        @endforeach
                                    </select>
                                    <input name="no_spk" value="{{ $penjualan->no_spk }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input name="tipe_motor" value="{{ $penjualan->tipe_motor }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input type="date" name="tanggal_spk" value="{{ $penjualan->tanggal_spk->format('Y-m-d') }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input type="number" min="1" name="jumlah_unit" value="{{ $penjualan->jumlah_unit }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input type="number" min="0" step="0.01" name="harga_unit" value="{{ $penjualan->harga_unit }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2 md:col-span-2">
                                </div>
                                <div class="flex gap-2">
                                    <button class="rounded-lg bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">Update</button>
                            </form>
                                    <form method="POST" action="{{ route('admin.penjualans.destroy', $penjualan) }}" onsubmit="return confirm('Hapus SPK ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg bg-red-600/30 px-3 py-1.5 text-sm hover:bg-red-600/50">Hapus</button>
                                    </form>
                                </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="mt-4">{{ $penjualans->appends(['bulan' => $bulan, 'tahun' => $tahun])->links() }}</div>
        </section>
    </div>
@endsection
