<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeaderboardExcelImport;
use App\Models\LeaderboardItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LeaderboardUploadController extends Controller
{
    public function index(): View
    {
        return view('admin.leaderboard-upload.index', [
            'latestItems' => LeaderboardItem::query()
                ->orderByDesc('tanggal_faktur')
                ->orderByDesc('id')
                ->limit(20)
                ->get(),
            'totalRows' => LeaderboardItem::query()->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'replace_data' => ['nullable', 'boolean'],
        ]);

        $import = new LeaderboardExcelImport($request->file('file')->getClientOriginalName());

        DB::transaction(function () use ($validated, $request, $import) {
            if ((bool) ($validated['replace_data'] ?? true)) {
                LeaderboardItem::query()->truncate();
            }

            Excel::import($import, $request->file('file'));
        });

        return back()->with('status', "Upload berhasil. {$import->importedRows} baris diproses.");
    }
}
