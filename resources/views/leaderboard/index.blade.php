@extends('layouts.app')

@section('content')
    @php
        $topThreeRows = collect($rows)->take(3)->values();
        $podiumConfigs = [
            1 => [
                'label' => 'Juara 1',
                'border' => 'border-yellow-300/60',
                'glow' => 'from-yellow-300/30 via-yellow-100/5 to-transparent',
                'text' => 'text-yellow-100',
                'badge' => 'bg-yellow-300/20 text-yellow-100',
                'col' => 'lg:col-span-2',
            ],
            2 => [
                'label' => 'Juara 2',
                'border' => 'border-slate-300/60',
                'glow' => 'from-slate-300/30 via-slate-100/5 to-transparent',
                'text' => 'text-slate-100',
                'badge' => 'bg-slate-300/20 text-slate-100',
                'col' => 'lg:col-span-1',
            ],
            3 => [
                'label' => 'Juara 3',
                'border' => 'border-orange-300/60',
                'glow' => 'from-orange-300/30 via-orange-100/5 to-transparent',
                'text' => 'text-orange-100',
                'badge' => 'bg-orange-300/20 text-orange-100',
                'col' => 'lg:col-span-1',
            ],
        ];
    @endphp

    <div class="space-y-6" id="leaderboard-app">
        <section class="card-glass p-5">
            <div class="grid gap-4 lg:grid-cols-4">
                <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-7 lg:col-span-3">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Bulan</label>
                        <input type="number" min="1" max="12" name="bulan" value="{{ $bulan }}" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Tahun</label>
                        <input type="number" min="2000" max="2100" name="tahun" value="{{ $tahun }}" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Dealer</label>
                        <select name="dealer" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="">Semua Dealer</option>
                            @foreach($dealers as $dealerOption)
                                <option value="{{ $dealerOption }}" @selected($dealer === $dealerOption)>
                                    {{ $dealerOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Tipe Unit</label>
                        <select name="tipe_unit" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="">Semua Tipe Unit</option>
                            @foreach($tipeUnits as $tipeUnitOption)
                                <option value="{{ $tipeUnitOption }}" @selected($tipeUnit === $tipeUnitOption)>
                                    {{ $tipeUnitOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Jabatan</label>
                        <select name="jabatan" class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="">Semua Jabatan</option>
                            @foreach($jabatans as $jabatanOption)
                                <option value="{{ $jabatanOption }}" @selected($jabatan === $jabatanOption)>
                                    {{ $jabatanOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-white/70">Jumlah Sales / Dealer</label>
                        <input
                            type="number"
                            min="1"
                            max="50"
                            name="limit_per_dealer"
                            value="{{ $limitPerDealer ?? '' }}"
                            placeholder="Semua"
                            class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white placeholder:text-white/40 focus:border-red-500 focus:outline-none"
                        >
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-500">Terapkan</button>
                    </div>
                </form>

                <div class="space-y-2 rounded-xl border border-red-500/40 bg-red-600/10 p-4">
                    <p class="text-xs uppercase tracking-wide text-red-200">Data Leaderboard</p>
                    <p class="brand-title text-2xl font-bold text-white" id="totalRows">{{ number_format($totals['total_rows']) }} Row</p>
                    <p class="text-xs text-white/70">
                        Sales: <span id="totalSales">{{ number_format($totals['total_sales']) }}</span> |
                        Dealer: <span id="totalDealer">{{ number_format($totals['total_dealer']) }}</span> |
                        Faktur: <span id="totalFaktur">{{ number_format($totals['total_faktur']) }}</span>
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-4" id="topThreeCards">
            @for($rank = 1; $rank <= 3; $rank++)
                @php
                    $row = $topThreeRows->get($rank - 1);
                    $config = $podiumConfigs[$rank];
                    $initials = strtoupper(substr((string) ($row['nama_sales'] ?? '--'), 0, 2));
                @endphp
                <article class="relative overflow-hidden rounded-2xl border bg-black/30 p-6 shadow-[0_20px_45px_-25px_rgba(0,0,0,0.8)] {{ $config['border'] }} {{ $config['col'] }}">
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br {{ $config['glow'] }}"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] {{ $config['text'] }}">{{ $config['label'] }}</p>
                            <p class="brand-title mt-1 text-5xl font-bold {{ $config['text'] }}">#{{ $rank }}</p>
                        </div>
                        <div class="h-14 w-14 overflow-hidden rounded-full border border-white/20">
                            @if(!empty($row['foto_profile']))
                                <img src="{{ asset('storage/'.$row['foto_profile']) }}" alt="{{ $row['nama_sales'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-white/10 text-sm font-bold">{{ $initials }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="relative mt-4">
                        <p class="text-2xl font-extrabold leading-tight text-white">{{ $row['nama_sales'] ?? '-' }}</p>
                        <p class="mt-1 text-sm text-white/70">{{ $row['dealer'] ?? 'Belum ada data' }}</p>
                    </div>
                    <div class="relative mt-5 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider {{ $config['badge'] }}">
                        Total Faktur: {{ number_format((int) ($row['total_faktur'] ?? 0), 0, ',', '.') }}
                    </div>
                </article>
            @endfor
        </section>

        <section class="card-glass overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-white/10 text-left text-xs uppercase tracking-wider text-white/70">
                        <tr>
                            <th class="table-cell">Rangking</th>
                            <th class="table-cell">Foto Profile</th>
                            <th class="table-cell">Nama Sales</th>
                            <th class="table-cell">Dealer</th>
                            <th class="table-cell">Jabatan</th>
                            <th class="table-cell">Tipe Unit</th>
                            <th class="table-cell">Tipe Beli</th>
                            <th class="table-cell">Total Faktur</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBody">
                        @forelse($rows as $row)
                            <tr class="{{ $loop->odd ? 'bg-white/[0.03]' : 'border-t border-white/10' }}">
                                <td class="table-cell font-bold text-red-200">#{{ $row['rank'] }}</td>
                                <td class="table-cell">
                                    <div class="h-10 w-10 overflow-hidden rounded-full border border-white/20">
                                        @if($row['foto_profile'])
                                            <img src="{{ asset('storage/'.$row['foto_profile']) }}" alt="{{ $row['nama_sales'] }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-red-600/20 text-xs font-bold">{{ strtoupper(substr($row['nama_sales'], 0, 2)) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="table-cell">{{ $row['nama_sales'] }}</td>
                                <td class="table-cell">{{ $row['dealer'] }}</td>
                                <td class="table-cell">{{ $row['jabatan'] ?? '-' }}</td>
                                <td class="table-cell">{{ $row['tipe_unit'] ?? '-' }}</td>
                                <td class="table-cell">{{ $row['tipe_beli'] ?? '-' }}</td>
                                <td class="table-cell">{{ number_format((int) ($row['total_faktur'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="table-cell text-center text-white/60" colspan="8">Belum ada data. Upload file Excel di menu Admin -> Upload Excel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const endpoint = '{{ route('leaderboard.data') }}';
        const params = new URLSearchParams({
            bulan: '{{ $bulan }}',
            tahun: '{{ $tahun }}',
            dealer: '{{ $dealer }}',
            tipe_unit: '{{ $tipeUnit }}',
            jabatan: '{{ $jabatan }}',
            limit_per_dealer: '{{ $limitPerDealer }}',
        });
        const pollingMs = {{ $pollingSeconds }} * 1000;
        const storageBase = '{{ asset('storage') }}';
        const initials = (name) => (name || '').split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
        const podiumConfig = {
            1: {
                label: 'Juara 1',
                border: 'border-yellow-300/60',
                glow: 'from-yellow-300/30 via-yellow-100/5 to-transparent',
                text: 'text-yellow-100',
                badge: 'bg-yellow-300/20 text-yellow-100',
                col: 'lg:col-span-2',
            },
            2: {
                label: 'Juara 2',
                border: 'border-slate-300/60',
                glow: 'from-slate-300/30 via-slate-100/5 to-transparent',
                text: 'text-slate-100',
                badge: 'bg-slate-300/20 text-slate-100',
                col: 'lg:col-span-1',
            },
            3: {
                label: 'Juara 3',
                border: 'border-orange-300/60',
                glow: 'from-orange-300/30 via-orange-100/5 to-transparent',
                text: 'text-orange-100',
                badge: 'bg-orange-300/20 text-orange-100',
                col: 'lg:col-span-1',
            },
        };

        const renderTable = (rows) => {
            const body = document.getElementById('leaderboardBody');
            body.innerHTML = '';

            if (rows.length === 0) {
                body.innerHTML = '<tr><td class="table-cell text-center text-white/60" colspan="8">Belum ada data. Upload file Excel di menu Admin -> Upload Excel.</td></tr>';
                return;
            }

            rows.forEach((row, index) => {
                body.insertAdjacentHTML('beforeend', `
                    <tr class="${index % 2 === 0 ? 'bg-white/[0.03]' : 'border-t border-white/10'}">
                        <td class="table-cell font-bold text-red-200">#${row.rank}</td>
                        <td class="table-cell">
                            <div class="h-10 w-10 overflow-hidden rounded-full border border-white/20">
                                ${row.foto_profile
                                    ? `<img src="${storageBase}/${row.foto_profile}" alt="${row.nama_sales}" class="h-full w-full object-cover">`
                                    : `<div class="flex h-full w-full items-center justify-center bg-red-600/20 text-xs font-bold">${initials(row.nama_sales)}</div>`}
                            </div>
                        </td>
                        <td class="table-cell">${row.nama_sales}</td>
                        <td class="table-cell">${row.dealer}</td>
                        <td class="table-cell">${row.jabatan ?? '-'}</td>
                        <td class="table-cell">${row.tipe_unit ?? '-'}</td>
                        <td class="table-cell">${row.tipe_beli ?? '-'}</td>
                        <td class="table-cell">${Number(row.total_faktur ?? 0).toLocaleString('id-ID')}</td>
                    </tr>
                `);
            });
        };

        const renderTopThree = (rows) => {
            const container = document.getElementById('topThreeCards');
            if (!container) return;

            container.innerHTML = '';

            for (let rank = 1; rank <= 3; rank += 1) {
                const row = rows[rank - 1] || null;
                const config = podiumConfig[rank];
                const avatar = row?.foto_profile
                    ? `<img src="${storageBase}/${row.foto_profile}" alt="${row.nama_sales}" class="h-full w-full object-cover">`
                    : `<div class="flex h-full w-full items-center justify-center bg-white/10 text-sm font-bold">${initials(row?.nama_sales ?? '--')}</div>`;

                container.insertAdjacentHTML('beforeend', `
                    <article class="relative overflow-hidden rounded-2xl border bg-black/30 p-6 shadow-[0_20px_45px_-25px_rgba(0,0,0,0.8)] ${config.border} ${config.col}">
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br ${config.glow}"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] ${config.text}">${config.label}</p>
                                <p class="brand-title mt-1 text-5xl font-bold ${config.text}">#${rank}</p>
                            </div>
                            <div class="h-14 w-14 overflow-hidden rounded-full border border-white/20">
                                ${avatar}
                            </div>
                        </div>
                        <div class="relative mt-4">
                            <p class="text-2xl font-extrabold leading-tight text-white">${row?.nama_sales ?? '-'}</p>
                            <p class="mt-1 text-sm text-white/70">${row?.dealer ?? 'Belum ada data'}</p>
                        </div>
                        <div class="relative mt-5 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider ${config.badge}">
                            Total Faktur: ${Number(row?.total_faktur ?? 0).toLocaleString('id-ID')}
                        </div>
                    </article>
                `);
            }
        };

        const refresh = async () => {
            try {
                const response = await fetch(`${endpoint}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;
                const payload = await response.json();

                document.getElementById('totalRows').textContent = `${Number(payload.totals.total_rows).toLocaleString('id-ID')} Row`;
                document.getElementById('totalSales').textContent = Number(payload.totals.total_sales).toLocaleString('id-ID');
                document.getElementById('totalDealer').textContent = Number(payload.totals.total_dealer).toLocaleString('id-ID');
                document.getElementById('totalFaktur').textContent = Number(payload.totals.total_faktur).toLocaleString('id-ID');

                renderTopThree(payload.rows);
                renderTable(payload.rows);
            } catch (error) {
                console.error(error);
            }
        };

        setInterval(refresh, pollingMs);
    })();
</script>
@endpush
