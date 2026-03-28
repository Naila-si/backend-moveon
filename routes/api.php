<?php

use App\Http\Controllers\CrmReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| CRM REPORTS
|--------------------------------------------------------------------------
*/
Route::get('/crm-reports', function (Request $req) {
    $page = max((int) $req->query('page', 1), 1);
    $limit = 50;
    $offset = ($page - 1) * $limit;

    $q = trim((string) $req->query('q', ''));
    $status = trim((string) $req->query('status', ''));

    $query = DB::table('crm_reports_rows');

    if ($q !== '') {
        $query->where(function ($sub) use ($q) {
            $sub->where('report_code', 'like', "%{$q}%")
                ->orWhere('step1', 'like', "%{$q}%")
                ->orWhere('step2', 'like', "%{$q}%")
                ->orWhere('step4', 'like', "%{$q}%");
        });
    }

    if ($status !== '' && strtolower($status) !== 'semua') {
        $query->where('step4', 'like', '%'.$status.'%');
    }

    $total = (clone $query)->count();

    $rows = $query
        ->orderBy('pk_id', 'desc')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->map(function ($r) {
            return [
                'pk_id' => $r->pk_id,
                'report_code' => $r->report_code,
                'step1' => json_decode($r->step1 ?? '{}', true) ?: [],
                'step2' => json_decode($r->step2 ?? '{}', true) ?: [],
                'step3' => json_decode($r->step3 ?? '{}', true) ?: [],
                'step4' => json_decode($r->step4 ?? '{}', true) ?: [],
                'step5' => json_decode($r->step5 ?? '{}', true) ?: [],
            ];
        })
        ->values();

    return response()->json([
        'data' => $rows,
        'total' => $total,
        'page' => $page,
    ]);
});

Route::get('/crm-reports/{id}', function ($id) {
    $r = DB::table('crm_reports_rows')
        ->where('pk_id', $id)
        ->orWhereRaw('TRIM(report_code) = ?', [$id])
        ->first();

    if (!$r) return response()->json(['error' => 'Data tidak ditemukan'], 404);

    return response()->json([
        'pk_id' => $r->pk_id,
        'report_code' => $r->report_code,
        'step1' => json_decode($r->step1 ?? '{}', true) ?: [],
        'step2' => json_decode($r->step2 ?? '{}', true) ?: [],
        'step3' => json_decode($r->step3 ?? '{}', true) ?: [],
        'step4' => json_decode($r->step4 ?? '{}', true) ?: [],
        'step5' => json_decode($r->step5 ?? '{}', true) ?: [],
    ]);
});

Route::put('/crm-reports/{id}', function (Request $req, $id) {
    $report = DB::table('crm_reports_rows')->where('pk_id', $id)->first();
    if (!$report) return response()->json(['error' => 'Data tidak ditemukan'], 404);

    $newStep4 = array_merge(
        json_decode($report->step4 ?? '{}', true) ?: [],
        $req->input('step4', [])
    );

    DB::table('crm_reports_rows')->where('pk_id', $id)->update([
        'step4' => json_encode($newStep4),
    ]);

    return response()->json(['success' => true, 'step4' => $newStep4]);
});

Route::delete('/crm-reports/{id}', [CrmReportController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| CRM SAVE & FILE
|--------------------------------------------------------------------------
*/
Route::post('/crm/save', function (Request $req) {
    $validated = $req->validate([
        'id' => 'nullable|string',
        'step1' => 'required|array',
        'step2' => 'required|array',
        'step3' => 'required|array',
        'step4' => 'required|array',
        'step5' => 'required|array',
    ]);

    $id = DB::table('crm_reports_rows')->insertGetId([
        'report_code' => $validated['id'] ?? null,
        'step1' => json_encode($validated['step1']),
        'step2' => json_encode($validated['step2']),
        'step3' => json_encode($validated['step3']),
        'step4' => json_encode($validated['step4']),
        'step5' => json_encode($validated['step5']),
    ]);

    return response()->json(['success' => true, 'reportId' => $id]);
});

Route::post('/crm/upload', function (Request $req) {
    $req->validate([
        'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $path = $req->file('file')->store('crm', 'public');

    return response()->json([
        'success' => true,
        'url' => '/storage/' . $path,
    ]);
});

/*
|--------------------------------------------------------------------------
| IWKBU (FIXED - SINGLE SOURCE)
|--------------------------------------------------------------------------
*/
Route::get('/iwkbu', function (Request $req) {
    $query = DB::table('iwkbu_rows');

    if ($req->perusahaan) {
        $query->where('nama_perusahaan', 'like', '%' . $req->perusahaan . '%');
    }

    $page = (int) ($req->page ?? 1);
    $limit = 50;

    $total = (clone $query)->count();

    $rows = $query
        ->orderBy('id', 'desc')
        ->offset(($page - 1) * $limit)
        ->limit($limit)
        ->get();

    return response()->json([
        'data' => $rows,
        'total' => $total
    ]);
});

Route::get('/iwkbu-total', fn() =>
    response()->json(['total' => DB::table('iwkbu_rows')->sum('nominal')])
);

Route::get('/iwkbu-all', function () {
    return response()->json(
        DB::table('iwkbu_rows')
            ->orderBy('id', 'desc')
            ->get()
    );
});

/*
|--------------------------------------------------------------------------
| IWKL (FIXED)
|--------------------------------------------------------------------------
*/
Route::get('/iwkl', fn() =>
    DB::table('iwkl_rows')->orderBy('id', 'desc')->get()
);

Route::post('/iwkl', fn(Request $req) =>
    DB::table('iwkl_rows')->insertGetId($req->all())
);

Route::put('/iwkl/{id}', function ($id, Request $req) {
    DB::table('iwkl_rows')->where('id', $id)->update($req->all());
    return response()->json(['success' => true]);
});

Route::delete('/iwkl/{id}', function ($id) {
    DB::table('iwkl_rows')->where('id', $id)->delete();
    return response()->json(['success' => true]);
});

Route::get('/iwkl-years', function () {

    return DB::table('iwkl_bulanan_rows')
        ->select('tahun')
        ->distinct()
        ->get();
});

Route::get('/iwkl-filters', function () {

    return DB::table('iwkl_rows')
        ->select('loket','kelas','status_pks','status_kapal','trayek')
        ->get();
});

Route::get('/iwkl-bulanan', function (Request $req) {
    $tahun = $req->tahun;

    return DB::table('iwkl_bulanan_rows')
        ->when($tahun, fn($q) => $q->where('tahun', $tahun))
        ->get();
});

Route::post('/iwkl-bulanan', function (Request $req) {

    DB::table('iwkl_bulanan_rows')->updateOrInsert(
        [
            'iwkl_id'=>$req->iwkl_id,
            'tahun'=>$req->tahun,
            'bulan'=>$req->bulan
        ],
        [
            'nilai'=>$req->nilai
        ]
    );

    return response()->json(['success'=>true]);
});

/*
|--------------------------------------------------------------------------
| EMPLOYEES (FIXED - SINGLE VERSION)
|--------------------------------------------------------------------------
*/
Route::get('/employees', function () {
    return DB::table('employees_rows')
        ->leftJoin('samsat_rows', 'employees_rows.samsat_id', '=', 'samsat_rows.id')
        ->select(
            'employees_rows.id',
            'employees_rows.name',
            'employees_rows.handle',
            'employees_rows.loket',
            'samsat_rows.name as samsat_name'
        )
        ->orderBy('employees_rows.name')
        ->get();
});

Route::post('/employees', function (Request $req) {
    $id = DB::table('employees_rows')->insertGetId($req->all());
    return DB::table('employees_rows')->where('id', $id)->first();
});

Route::put('/employees/{id}', function (Request $req, $id) {
    DB::table('employees_rows')->where('id', $id)->update($req->all());
    return response()->json(['success' => true]);
});

Route::delete('/employees/{id}', function ($id) {
    DB::table('employees_rows')->where('id', $id)->delete();
    return response()->json(['success' => true]);
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function (Request $req) {
    $year = $req->year;

    return response()->json([
        'employees' => DB::table('employees_rows')->get(),
        'iwkbu' => DB::table('iwkbu_rows')
            ->when($year, fn($q) => $q->whereYear('tgl_transaksi', $year))
            ->get(),
        'iwkl' => DB::table('iwkl_rows')->get(),
        'rkj' => DB::table('rkj_entries_rows')
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->get()
    ]);
});
