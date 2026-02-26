<?php

namespace App\Imports;

use App\Models\LeaderboardItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LeaderboardExcelImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public int $importedRows = 0;

    public function __construct(private readonly string $sourceFile = '') {}

    public function collection(Collection $collection): void
    {
        $rows = [];

        foreach ($collection as $row) {
            $mapped = $this->mapRow($row);
            if ($mapped === null) {
                continue;
            }

            $rows[] = $mapped;
        }

        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            LeaderboardItem::query()->insert($chunk);
        }

        $this->importedRows += count($rows);
    }

    private function mapRow(Collection $row): ?array
    {
        $tanggalFaktur = $this->parseDate($this->pick($row, ['tanggal_faktur', 'tanggal faktur', 'tanggal']));
        $dealer = trim((string) $this->pick($row, ['dealer']));
        $tipeUnit = trim((string) $this->pick($row, ['tipe_unit', 'tipe unit']));
        $tipeBeli = trim((string) $this->pick($row, ['tipe_beli', 'tipe beli']));
        $jabatan = trim((string) $this->pick($row, ['jabatan']));
        $namaSales = trim((string) $this->pick($row, ['nama_sales', 'nama sales']));

        if ($dealer === '' && $namaSales === '' && $tipeUnit === '') {
            return null;
        }

        return [
            'tanggal_faktur' => $tanggalFaktur,
            'dealer' => $dealer !== '' ? $dealer : '-',
            'tipe_unit' => $tipeUnit !== '' ? $tipeUnit : null,
            'tipe_beli' => $tipeBeli !== '' ? $tipeBeli : null,
            'jabatan' => $jabatan !== '' ? $jabatan : null,
            'nama_sales' => $namaSales !== '' ? $namaSales : '-',
            'foto_profile' => null,
            'source_file' => $this->sourceFile !== '' ? $this->sourceFile : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            }

            $date = trim((string) $value);

            $parsed = Carbon::createFromFormat('d/m/Y', $date);
            if ($parsed !== false) {
                return $parsed->toDateString();
            }

            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function pick(Collection $row, array $keys): mixed
    {
        $array = $row->toArray();

        foreach ($keys as $key) {
            if (Arr::exists($array, $key)) {
                return $array[$key];
            }
        }

        return null;
    }
}
