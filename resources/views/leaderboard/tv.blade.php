<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Mode - Honda Babel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <main class="min-h-screen px-6 py-5">
        <header class="mb-5 flex items-end justify-between border-b border-white/20 pb-4">
            <div>
                <p class="brand-title text-sm tracking-[0.18em] text-red-400">HONDA BABEL</p>
                <h1 class="brand-title text-4xl font-bold">LEADERBOARD TV MODE</h1>
                <p class="mt-1 text-sm text-white/70">
                    Bulan {{ $bulan }} Tahun {{ $tahun }}
                    @if($dealer)
                        | Dealer: {{ $dealer }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                <div class="brand-title text-5xl font-bold text-red-400" id="clock">00:00:00</div>
                <div class="text-xs text-white/70">Auto refresh {{ $pollingSeconds }} detik</div>
            </div>
        </header>

        <section class="mb-4 grid gap-3 md:grid-cols-4">
            <div class="rounded-xl border border-red-500/40 bg-red-600/10 p-4">
                <p class="text-xs uppercase text-red-200">Total Row</p>
                <p class="brand-title text-3xl font-bold text-white" id="totalRows">0</p>
            </div>
            <div class="rounded-xl border border-white/20 bg-white/5 p-4">
                <p class="text-xs uppercase text-white/70">Total Sales</p>
                <p class="brand-title text-3xl font-bold text-white" id="totalSales">0</p>
            </div>
            <div class="rounded-xl border border-white/20 bg-white/5 p-4">
                <p class="text-xs uppercase text-white/70">Total Dealer</p>
                <p class="brand-title text-3xl font-bold text-white" id="totalDealer">0</p>
            </div>
            <div class="rounded-xl border border-white/20 bg-white/5 p-4">
                <p class="text-xs uppercase text-white/70">Total Faktur</p>
                <p class="brand-title text-3xl font-bold text-white" id="totalFaktur">0</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-white/20 bg-white/5">
            <table class="min-w-full">
                <thead class="bg-red-600/20 text-left text-sm uppercase tracking-wide text-red-100">
                    <tr>
                        <th class="px-4 py-3">Rangking</th>
                        <th class="px-4 py-3">Foto Profile</th>
                        <th class="px-4 py-3">Nama Sales</th>
                        <th class="px-4 py-3">Dealer</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3">Tipe Unit</th>
                        <th class="px-4 py-3">Tipe Beli</th>
                        <th class="px-4 py-3">Total Faktur</th>
                    </tr>
                </thead>
                <tbody id="rows" class="text-base"></tbody>
            </table>
        </section>
    </main>

    <script>
        (function () {
            const endpoint = '{{ route('leaderboard.data') }}';
            const params = new URLSearchParams({
                bulan: '{{ $bulan }}',
                tahun: '{{ $tahun }}',
                dealer: '{{ $dealer }}',
            });
            const interval = {{ $pollingSeconds }} * 1000;
            const storageBase = '{{ asset('storage') }}';
            const initials = (name) => (name || '').split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();

            const refreshClock = () => {
                document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
            };

            const renderRows = (rows) => {
                const tbody = document.getElementById('rows');
                tbody.innerHTML = '';

                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td class="px-4 py-6 text-center text-white/60" colspan="8">Belum ada data upload untuk filter ini.</td></tr>';
                    return;
                }

                rows.forEach((row, index) => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr class="${index % 2 === 0 ? 'bg-white/[0.04]' : 'border-t border-white/10'}">
                            <td class="px-4 py-3 font-bold text-red-200">#${row.rank}</td>
                            <td class="px-4 py-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full border border-white/20">
                                    ${row.foto_profile
                                        ? `<img src="${storageBase}/${row.foto_profile}" alt="${row.nama_sales}" class="h-full w-full object-cover">`
                                        : `<div class="flex h-full w-full items-center justify-center bg-red-600/25 text-xs font-bold">${initials(row.nama_sales)}</div>`}
                                </div>
                            </td>
                            <td class="px-4 py-3">${row.nama_sales}</td>
                            <td class="px-4 py-3">${row.dealer}</td>
                            <td class="px-4 py-3">${row.jabatan ?? '-'}</td>
                            <td class="px-4 py-3">${row.tipe_unit ?? '-'}</td>
                            <td class="px-4 py-3">${row.tipe_beli ?? '-'}</td>
                            <td class="px-4 py-3">${Number(row.total_faktur ?? 0).toLocaleString('id-ID')}</td>
                        </tr>
                    `);
                });
            };

            const load = async () => {
                try {
                    const response = await fetch(`${endpoint}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) return;

                    const payload = await response.json();
                    document.getElementById('totalRows').textContent = Number(payload.totals.total_rows).toLocaleString('id-ID');
                    document.getElementById('totalSales').textContent = Number(payload.totals.total_sales).toLocaleString('id-ID');
                    document.getElementById('totalDealer').textContent = Number(payload.totals.total_dealer).toLocaleString('id-ID');
                    document.getElementById('totalFaktur').textContent = Number(payload.totals.total_faktur).toLocaleString('id-ID');

                    renderRows(payload.rows);
                } catch (error) {
                    console.error(error);
                }
            };

            refreshClock();
            load();
            setInterval(refreshClock, 1000);
            setInterval(load, interval);
        })();
    </script>
</body>
</html>
