@extends('layouts.app')

@section('content')
    @include('admin.partials.menu')

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Tambah Sales</h2>
            <form method="POST" action="{{ route('admin.sales.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <select name="dealer_id" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                    <option value="">Pilih Dealer</option>
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->id }}">{{ $dealer->nama_dealer }}</option>
                    @endforeach
                </select>
                <input name="kode_sales" placeholder="Kode Sales" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                <input name="nama_sales" placeholder="Nama Sales" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                <input name="no_hp" placeholder="No HP" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <input name="target_bulanan" type="number" min="0" value="0" placeholder="Target Bulanan" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <input name="foto_sales" type="file" accept="image/*" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-white/30 bg-transparent text-red-600">
                    Sales aktif
                </label>
                <button class="rounded-lg bg-red-600 px-4 py-2 font-semibold hover:bg-red-500">Simpan Sales</button>
            </form>
        </section>

        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Daftar Sales</h2>
            <div class="space-y-4">
                @foreach($salesList as $sales)
                    <article class="rounded-xl border border-white/15 bg-black/25 p-4">
                        <form method="POST" action="{{ route('admin.sales.update', $sales) }}" enctype="multipart/form-data" class="space-y-2">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center gap-3">
                                <div class="h-14 w-14 overflow-hidden rounded-full border border-white/20">
                                    @if($sales->foto_sales)
                                        <img src="{{ asset('storage/'.$sales->foto_sales) }}" alt="{{ $sales->nama_sales }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-red-600/25 text-xs font-bold">{{ strtoupper(substr($sales->nama_sales, 0, 2)) }}</div>
                                    @endif
                                </div>
                                <select name="dealer_id" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                    @foreach($dealers as $dealer)
                                        <option value="{{ $dealer->id }}" @selected((int) $sales->dealer_id === (int) $dealer->id)>
                                            {{ $dealer->nama_dealer }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-2 md:grid-cols-2">
                                <input name="kode_sales" value="{{ $sales->kode_sales }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                                <input name="nama_sales" value="{{ $sales->nama_sales }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                                <input name="no_hp" value="{{ $sales->no_hp }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                                <input name="target_bulanan" type="number" min="0" value="{{ $sales->target_bulanan }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                            </div>
                            <input name="foto_sales" type="file" accept="image/*" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="status_aktif" value="1" @checked($sales->status_aktif) class="rounded border-white/30 bg-transparent text-red-600">
                                Sales aktif
                            </label>
                            <div class="flex gap-2">
                                <button class="rounded-lg bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">Update</button>
                        </form>
                                <form method="POST" action="{{ route('admin.sales.destroy', $sales) }}" onsubmit="return confirm('Hapus sales ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg bg-red-600/30 px-3 py-1.5 text-sm hover:bg-red-600/50">Hapus</button>
                                </form>
                            </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $salesList->links() }}</div>
        </section>
    </div>
@endsection
