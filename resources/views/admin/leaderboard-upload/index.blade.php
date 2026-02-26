@extends('layouts.app')

@section('content')
    @include('admin.partials.menu')

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card-glass p-5">
            <h2 class="brand-title mb-3 text-xl font-bold">Upload Excel Leaderboard</h2>
            <p class="mb-4 text-sm text-white/70">
                Format header wajib: <strong>Tanggal Faktur</strong>, <strong>Dealer</strong>, <strong>Tipe Unit</strong>, <strong>Tipe Beli</strong>, <strong>Jabatan</strong>, <strong>Nama Sales</strong>.
            </p>

            <form method="POST" action="{{ route('admin.leaderboard-upload.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2">
                <label class="flex items-center gap-2 text-sm text-white/80">
                    <input type="checkbox" name="replace_data" value="1" checked class="rounded border-white/30 bg-transparent text-red-600">
                    Replace semua data leaderboard sebelumnya
                </label>
                <button class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-500">Upload & Proses</button>
            </form>

            <div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-3 text-sm text-white/70">
                Total baris saat ini: <span class="font-semibold text-white">{{ number_format($totalRows) }}</span>
            </div>
        </section>

        <section class="card-glass p-5">
            <h2 class="brand-title mb-3 text-xl font-bold">Preview Data Terakhir</h2>
            <div class="max-h-[420px] overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/10 text-left text-xs uppercase tracking-wide text-white/70">
                        <tr>
                            <th class="table-cell">Tanggal Faktur</th>
                            <th class="table-cell">Dealer</th>
                            <th class="table-cell">Tipe Unit</th>
                            <th class="table-cell">Tipe Beli</th>
                            <th class="table-cell">Jabatan</th>
                            <th class="table-cell">Nama Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestItems as $item)
                            <tr class="border-t border-white/10">
                                <td class="table-cell">{{ optional($item->tanggal_faktur)->format('d/m/Y') ?? '-' }}</td>
                                <td class="table-cell">{{ $item->dealer }}</td>
                                <td class="table-cell">{{ $item->tipe_unit ?? '-' }}</td>
                                <td class="table-cell">{{ $item->tipe_beli ?? '-' }}</td>
                                <td class="table-cell">{{ $item->jabatan ?? '-' }}</td>
                                <td class="table-cell">{{ $item->nama_sales }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="table-cell text-center text-white/60" colspan="6">Belum ada data upload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
