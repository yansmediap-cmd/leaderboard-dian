<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaderboardExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $rows,
        private readonly int $bulan,
        private readonly int $tahun,
    ) {}

    public function collection(): Collection
    {
        return $this->rows->values();
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Tahun',
            'Rank',
            'Nama Sales',
            'Dealer',
            'Unit SPK',
            'Unit DO',
            'Revenue',
            'Persentase Target (%)',
        ];
    }

    public function map($row): array
    {
        return [
            $this->bulan,
            $this->tahun,
            $row['rank'],
            $row['nama_sales'],
            $row['nama_dealer'],
            $row['total_spk'],
            $row['total_do'],
            $row['total_revenue'],
            $row['persentase_target'],
        ];
    }
}
