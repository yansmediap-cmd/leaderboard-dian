@extends('layouts.app')

@section('content')
    @include('admin.partials.menu')

    <section class="card-glass mb-6 p-5">
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
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Input DO Manual</h2>
            <form method="POST" action="{{ route('admin.delivery-orders.store') }}" class="space-y-3">
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
                <input name="no_do" placeholder="No DO" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <div class="grid gap-2 md:grid-cols-2">
                    <input name="tanggal_do" type="date" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <input name="jumlah_unit_do" type="number" min="1" required placeholder="Jumlah Unit DO" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                </div>
                <button class="rounded-lg bg-red-600 px-4 py-2 font-semibold hover:bg-red-500">Simpan DO</button>
            </form>
        </section>

        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Data DO Bulan Berjalan</h2>
            <div class="max-h-[560px] overflow-auto pr-2">
                <div class="space-y-4">
                    @foreach($deliveryOrders as $deliveryOrder)
                        <article class="rounded-xl border border-white/15 bg-black/25 p-4">
                            <form method="POST" action="{{ route('admin.delivery-orders.update', $deliveryOrder) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-2 md:grid-cols-2">
                                    <select name="dealer_id" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                        @foreach($dealers as $dealer)
                                            <option value="{{ $dealer->id }}" @selected((int) $deliveryOrder->dealer_id === (int) $dealer->id)>{{ $dealer->nama_dealer }}</option>
                                        @endforeach
                                    </select>
                                    <select name="sales_id" required class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                        @foreach($salesList as $sales)
                                            <option value="{{ $sales->id }}" @selected((int) $deliveryOrder->sales_id === (int) $sales->id)>{{ $sales->nama_sales }}</option>
                                        @endforeach
                                    </select>
                                    <input name="no_do" value="{{ $deliveryOrder->no_do }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input type="date" name="tanggal_do" value="{{ $deliveryOrder->tanggal_do->format('Y-m-d') }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    <input type="number" min="1" name="jumlah_unit_do" value="{{ $deliveryOrder->jumlah_unit_do }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2 md:col-span-2">
                                </div>
                                <div class="flex gap-2">
                                    <button class="rounded-lg bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">Update</button>
                            </form>
                                    <form method="POST" action="{{ route('admin.delivery-orders.destroy', $deliveryOrder) }}" onsubmit="return confirm('Hapus DO ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg bg-red-600/30 px-3 py-1.5 text-sm hover:bg-red-600/50">Hapus</button>
                                    </form>
                                </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="mt-4">{{ $deliveryOrders->appends(['bulan' => $bulan, 'tahun' => $tahun])->links() }}</div>
        </section>
    </div>
@endsection
