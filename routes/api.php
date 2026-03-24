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
        ->orderBy('created_at', 'desc')
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
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ];
        })
        ->values();

    return response()->json([
        'data' => $rows,
        'total' => $total,
        'page' => $page,
    ], 200, [
        'Content-Type' => 'application/json; charset=UTF-8',
    ]);
});

Route::get('/crm-reports/{id}', function ($id) {
    $r = DB::table('crm_reports_rows')->where('pk_id', $id)->first();

    if (!$r) {
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    return response()->json([
        'pk_id' => $r->pk_id,
        'report_code' => $r->report_code,
        'step1' => json_decode($r->step1 ?? '{}', true) ?: [],
        'step2' => json_decode($r->step2 ?? '{}', true) ?: [],
        'step3' => json_decode($r->step3 ?? '{}', true) ?: [],
        'step4' => json_decode($r->step4 ?? '{}', true) ?: [],
        'step5' => json_decode($r->step5 ?? '{}', true) ?: [],
        'created_at' => $r->created_at,
        'updated_at' => $r->updated_at,
    ]);
});

Route::put('/crm-reports/{id}', function (Request $req, $id) {
    $report = DB::table('crm_reports_rows')->where('pk_id', $id)->first();

    if (!$report) {
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    $oldStep4 = json_decode($report->step4 ?? '{}', true) ?: [];
    $newStep4 = array_merge($oldStep4, $req->input('step4', []));

    DB::table('crm_reports_rows')
        ->where('pk_id', $id)
        ->update([
            'step4' => json_encode($newStep4),
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'step4' => $newStep4,
    ]);
});

Route::delete('/crm-reports/{id}', [CrmReportController::class, 'destroy']);

Route::get('/crm-armada/{id}', function ($id) {
    $rows = DB::table('crm_armada_rows')
        ->where('report_id', $id)
        ->get()
        ->map(function ($r) {
            $r->bukti = json_decode($r->bukti ?? '[]', true) ?: [];
            return $r;
        });

    return response()->json($rows);
});

Route::post('/crm/save', function (Request $req) {
    try {
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
            'step1' => json_encode($validated['step1'], JSON_UNESCAPED_UNICODE),
            'step2' => json_encode($validated['step2'], JSON_UNESCAPED_UNICODE),
            'step3' => json_encode($validated['step3'], JSON_UNESCAPED_UNICODE),
            'step4' => json_encode($validated['step4'], JSON_UNESCAPED_UNICODE),
            'step5' => json_encode($validated['step5'], JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'success' => true,
            'reportId' => $id,
            'reportCode' => $validated['id'] ?? null,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::post('/crm/upload', function (Request $req) {
    try {
        $req->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,jfif,webp|max:5120',
        ]);

        if (!Storage::disk('public')->exists('crm')) {
            Storage::disk('public')->makeDirectory('crm');
        }

        $file = $req->file('file');
        $path = $file->store('crm', 'public'); // <- cukup crm saja

        return response()->json([
            'success' => true,
            'name' => $file->getClientOriginalName(),
            'path' => $path, // crm/namafile.png
            'url' => '/storage/' . $path, // /storage/crm/namafile.png
            'full_url' => url('/storage/' . $path),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/cek-crm', function () {
    return response()->json(['ok' => 'crm-file route kebaca']);
});

Route::get('/crm-file', function (Request $req) {
    $rawUrl = $req->query('url');

    if (!$rawUrl) {
        return response()->json(['error' => 'URL file kosong'], 400);
    }

    $url = trim($rawUrl);
    $path = parse_url($url, PHP_URL_PATH) ?: $url;
    $path = str_replace('\\', '/', $path);

    // hanya izinkan file dari /storage/crm/
    if (!str_starts_with($path, '/storage/crm/')) {
        return response()->json(['error' => 'Path file tidak diizinkan'], 403);
    }

    $relativePath = ltrim(str_replace('/storage/', '', $path), '/');

    if (!Storage::disk('public')->exists($relativePath)) {
        return response()->json([
            'error' => 'File tidak ditemukan',
            'path' => $relativePath,
        ], 404);
    }

    $fullPath = Storage::disk('public')->path($relativePath);
    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
});

Route::get('/crm-notifikasi', function () {
    return response()->json(
        DB::table('crm_notifikasi_rows')->orderBy('ts', 'desc')->get()
    );
});

Route::post('/crm-notifikasi', function (Request $req) {
    DB::table('crm_notifikasi_rows')->insert([
        'report_id' => $req->report_id,
        'report_uuid' => $req->report_uuid,
        'perusahaan' => $req->perusahaan,
        'status' => $req->status,
        'note' => $req->note,
        'petugas' => $req->petugas,
        'ts' => $req->ts,
        'payload' => json_encode($req->payload),
        'created_at' => now(),
    ]);

    return response()->json(['success' => true]);
});

Route::delete('/crm-notifikasi/{id}', function ($id) {
    DB::table('crm_notifikasi_rows')->where('id', $id)->delete();
    return response()->json(['success' => true]);
});

Route::delete('/crm-notifikasi', function () {
    DB::table('crm_notifikasi_rows')->delete();
    return response()->json(['success' => true]);
});

Route::get('/manifest-submissions', function () {

    $rows = DB::table('manifest_submissions_rows')->get();

    return response()->json($rows);
});

Route::delete('/manifest-submissions/{id}', function ($id) {

    DB::table('manifest_submissions_rows')->where('id',$id)->delete();

    return response()->json(['success'=>true]);
});

Route::get('/iwkbu', function (Request $req) {

    $page = (int)($req->page ?? 1);
    $limit = 50;
    $offset = ($page - 1) * $limit;

    $query = DB::table('iwkbu_rows');

    $total = $query->count();

    $rows = $query
        ->orderBy('updated_at','desc')
        ->offset($offset)
        ->limit($limit)
        ->get();

    return response()->json([
        'data'=>$rows,
        'total'=>$total
    ]);

});

Route::post('/iwkbu', function (Request $req) {

    DB::table('iwkbu_rows')->insert($req->all());

    return response()->json(['success'=>true]);
});

Route::delete('/iwkbu/{id}', function ($id) {

    DB::table('iwkbu_rows')
        ->where('id',$id)
        ->delete();

    return response()->json(['success'=>true]);
});

Route::get('/iwkbu-total', function () {

    $total = DB::table('iwkbu_rows')->sum('nominal');

    return response()->json([
        'total'=>$total
    ]);

});

Route::get('/iwkbu-filters', function () {

    $rows = DB::table('iwkbu_rows')->select(
        'wilayah',
        'loket',
        'trayek',
        'jenis',
        'pic',
        'badan_hukum',
        'nama_perusahaan',
        'status_bayar',
        'status_kendaraan',
        'konfirmasi',
        'golongan',
        'dok_perizinan'
    )->get();

    return response()->json($rows);
});

Route::get('/employees', function () {

    return DB::table('employees_rows')
        ->select('id','name','handle','loket')
        ->orderBy('name')
        ->get();

});

Route::get('/iwkl', function () {

    $rows = DB::table('iwkl_rows')
        ->orderBy('id','desc')
        ->get();

    return response()->json($rows);
});

Route::post('/iwkl', function (Request $req) {

    $id = DB::table('iwkl_rows')->insertGetId($req->all());

    return DB::table('iwkl_rows')->where('id',$id)->first();
});

Route::put('/iwkl/{id}', function ($id, Request $req) {

    DB::table('iwkl_rows')
        ->where('id',$id)
        ->update($req->all());

    return response()->json(['success'=>true]);
});

Route::delete('/iwkl/{id}', function ($id) {

    DB::table('iwkl_rows')->where('id',$id)->delete();

    return response()->json(['success'=>true]);
});

Route::get('/iwkl-bulanan', function (Request $req) {

    $tahun = $req->tahun;

    return DB::table('iwkl_bulanan_rows')
        ->where('tahun',$tahun)
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

Route::get('/iwkl-filters', function () {

    return DB::table('iwkl_rows')
        ->select('loket','kelas','status_pks','status_kapal','trayek')
        ->get();
});

Route::get('/iwkl-years', function () {

    return DB::table('iwkl_bulanan_rows')
        ->select('tahun')
        ->distinct()
        ->get();
});

/*
|--------------------------------------------------------------------------
| EMPLOYEES
|--------------------------------------------------------------------------
*/

Route::get('/employees', function () {

    return DB::table('employees_rows')
        ->leftJoin('samsat_rows','employees_rows.samsat_id','=','samsat_rows.id')
        ->select(
            'employees_rows.id',
            'employees_rows.name',
            'employees_rows.handle',
            'employees_rows.loket',
            'employees_rows.samsat_id',
            'samsat_rows.name as samsat_name'
        )
        ->orderBy('employees_rows.name')
        ->get();

});


Route::post('/employees', function (Request $req) {

    $id = DB::table('employees_rows')->insertGetId([
        'name' => $req->name,
        'handle' => $req->handle,
        'loket' => $req->loket,
        'samsat_id' => $req->samsat_id
    ]);

    return DB::table('employees_rows')->where('id',$id)->first();

});


Route::put('/employees/{id}', function (Request $req, $id) {

    DB::table('employees_rows')
        ->where('id',$id)
        ->update([
            'name' => $req->name,
            'loket' => $req->loket,
            'samsat_id' => $req->samsat_id
        ]);

    return response()->json(['success'=>true]);

});


Route::delete('/employees/{id}', function ($id) {

    DB::table('employees_rows')->where('id',$id)->delete();

    DB::table('rkj_entries_rows')->where('pid',$id)->delete();

    return response()->json(['success'=>true]);

});


/*
|--------------------------------------------------------------------------
| SAMSAT
|--------------------------------------------------------------------------
*/

Route::get('/samsat', function () {

    return DB::table('samsat_rows')
        ->orderBy('name')
        ->get();

});


Route::post('/samsat', function (Request $req) {

    $id = DB::table('samsat_rows')->insertGetId([
        'name' => $req->name,
        'loket' => $req->loket
    ]);

    return DB::table('samsat_rows')->where('id',$id)->first();

});


/*
|--------------------------------------------------------------------------
| RKJ ENTRIES
|--------------------------------------------------------------------------
*/

Route::get('/rkj-entries', function (Request $req) {

    $year = $req->year;
    $month = str_pad($req->month,2,'0',STR_PAD_LEFT);

    $start = "$year-$month-01";
    $end = date("Y-m-d", strtotime("$start +1 month"));

    return DB::table('rkj_entries_rows')
        ->where('date','>=',$start)
        ->where('date','<',$end)
        ->get();

});


Route::post('/rkj-entries', function (Request $req) {

    $existing = DB::table('rkj_entries_rows')
        ->where('pid',$req->pid)
        ->where('date',$req->date)
        ->first();

    if($existing){

        DB::table('rkj_entries_rows')
            ->where('id',$existing->id)
            ->update([
                'status'=>$req->status,
                'value'=>$req->value,
                'note'=>$req->note
            ]);

        return response()->json(['success'=>true]);

    }

    DB::table('rkj_entries_rows')->insert([
        'pid'=>$req->pid,
        'date'=>$req->date,
        'status'=>$req->status,
        'value'=>$req->value,
        'note'=>$req->note
    ]);

    return response()->json(['success'=>true]);

});


Route::put('/rkj-entries/{id}', function (Request $req, $id) {

    DB::table('rkj_entries_rows')
        ->where('id',$id)
        ->update([
            'status'=>$req->status,
            'value'=>$req->value,
            'note'=>$req->note
        ]);

    return response()->json(['success'=>true]);

});


Route::delete('/rkj-entries/{id}', function ($id) {

    DB::table('rkj_entries_rows')->where('id',$id)->delete();

    return response()->json(['success'=>true]);

});

Route::get('/perusahaan', function () {

    return DB::table('iwkbu_rows')
        ->select(
            'nama_perusahaan',
            'nama_pemilik',
            'hp'
        )
        ->whereNotNull('nama_perusahaan')
        ->distinct()
        ->orderBy('nama_perusahaan')
        ->get();

});

Route::get('/iwkbu', function (Request $req) {

    if($req->perusahaan){

        return DB::table('iwkbu_rows')
            ->select(
                'nopol',
                'tarif',
                'jenis',
                'tahun'
            )
            ->where('nama_perusahaan','like','%'.$req->perusahaan.'%')
            ->get();

    }

    $page = (int)($req->page ?? 1);
    $limit = 50;
    $offset = ($page - 1) * $limit;

    $query = DB::table('iwkbu_rows');

    $total = $query->count();

    $rows = $query
        ->orderBy('updated_at','desc')
        ->offset($offset)
        ->limit($limit)
        ->get();

    return response()->json([
        'data'=>$rows,
        'total'=>$total
    ]);
});

Route::get('/iwkbu/pic', function () {

    return DB::table('iwkbu_rows')
        ->select('pic','loket')
        ->whereNotNull('pic')
        ->get();

});

Route::get('/employees', function () {

    $rows = DB::table('employees_rows')
        ->leftJoin('samsat_rows','employees_rows.samsat_id','=','samsat_rows.id')
        ->select(
            'employees_rows.id',
            'employees_rows.name',
            'samsat_rows.name as samsat_name',
            'samsat_rows.loket'
        )
        ->orderBy('employees_rows.name')
        ->get();

    return $rows->map(function($r){
        return [
            'id'=>$r->id,
            'name'=>$r->name,
            'samsat'=>[
                'name'=>$r->samsat_name,
                'loket'=>$r->loket
            ]
        ];
    });

});

Route::post('/manifest-submissions', function (Request $req) {

    $id = DB::table('manifest_submissions_rows')->insertGetId([
        'tanggal' => $req->tanggal,
        'kapal' => $req->kapal,
        'rute' => $req->rute,
        'total_penumpang' => $req->total_penumpang,
        'jumlah_premi' => $req->jumlah_premi,
        'agen' => $req->agen,
        'telp' => $req->telp,
        'foto_url' => $req->foto_url,
        'sign_url' => $req->sign_url,
        'iwkl_id' => $req->iwkl_id,
        'created_at' => now()
    ]);

    return response()->json([
        'success' => true,
        'id' => $id
    ]);

});

Route::get('/iwkl', function () {

    return DB::table('iwkl_rows')
        ->select(
            'id',
            'nama_kapal',
            'rute_awal',
            'rute_akhir',
            'nama_perusahaan',
            'no_kontak'
        )
        ->orderBy('nama_kapal')
        ->get();

});

Route::get('/iwkbu', function (Request $req) {

    $query = DB::table('iwkbu_rows');

    if($req->perusahaan){
        $query->where('nama_perusahaan',$req->perusahaan);
    }

    return $query->get();
});

Route::get('/dashboard', function (Request $req) {

    $year = $req->year;

    return response()->json([

        'employees' => DB::table('employees_rows')->get(),

        'iwkbu' => DB::table('iwkbu_rows')
            ->when($year, fn($q)=>$q->whereYear('tgl_transaksi',$year))
            ->get(),

        'iwkl' => DB::table('iwkl_rows')->get(),

        'rkj' => DB::table('rkj_entries_rows')
            ->when($year, fn($q)=>$q->whereYear('date',$year))
            ->get()

    ]);

});

Route::get('/iwkbu', function (Request $req) {

    $page = (int)($req->page ?? 1);
    $limit = 50;

    $query = DB::table('iwkbu_rows');

    $total = $query->count();

    $rows = $query
        ->orderBy('id','desc')
        ->offset(($page-1)*$limit)
        ->limit($limit)
        ->get();

    return response()->json([
        'data'=>$rows,
        'total'=>$total
    ]);

});
