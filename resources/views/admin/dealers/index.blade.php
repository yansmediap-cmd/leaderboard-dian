@extends('layouts.app')

@section('content')
    @include('admin.partials.menu')

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Tambah Dealer</h2>
            <form method="POST" action="{{ route('admin.dealers.store') }}" class="space-y-3">
                @csrf
                <input name="kode_dealer" placeholder="Kode Dealer" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                <input name="nama_dealer" placeholder="Nama Dealer" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                <input name="kota" placeholder="Kota" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                <textarea name="alamat" rows="2" placeholder="Alamat" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2"></textarea>
                <textarea name="whitelist_ips" rows="3" placeholder="Whitelist IP (pisah baris / koma)" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2"></textarea>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-white/30 bg-transparent text-red-600">
                    Dealer aktif
                </label>
                <button class="rounded-lg bg-red-600 px-4 py-2 font-semibold hover:bg-red-500">Simpan Dealer</button>
            </form>
        </section>

        <section class="card-glass p-5">
            <h2 class="brand-title mb-4 text-xl font-bold">Daftar Dealer</h2>
            <div class="space-y-4">
                @foreach($dealers as $dealer)
                    <article class="rounded-xl border border-white/15 bg-black/25 p-4">
                        <form method="POST" action="{{ route('admin.dealers.update', $dealer) }}" class="space-y-2">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-2 md:grid-cols-2">
                                <input name="kode_dealer" value="{{ $dealer->kode_dealer }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                                <input name="nama_dealer" value="{{ $dealer->nama_dealer }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                                <input name="kota" value="{{ $dealer->kota }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2" required>
                                <input name="alamat" value="{{ $dealer->alamat }}" class="rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                            </div>
                            <textarea name="whitelist_ips" rows="2" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">{{ $dealer->apiWhitelists->pluck('ip_address')->implode("\n") }}</textarea>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_active" value="1" @checked($dealer->is_active) class="rounded border-white/30 bg-transparent text-red-600">
                                Dealer aktif
                            </label>
                            <div class="flex gap-2">
                                <button class="rounded-lg bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">Update</button>
                        </form>
                                <form method="POST" action="{{ route('admin.dealers.destroy', $dealer) }}" onsubmit="return confirm('Hapus dealer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg bg-red-600/30 px-3 py-1.5 text-sm hover:bg-red-600/50">Hapus</button>
                                </form>
                            </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $dealers->links() }}</div>
        </section>
    </div>
@endsection
