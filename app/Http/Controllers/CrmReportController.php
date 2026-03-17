<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmReportController extends Controller {

public function update(Request $request, $id)
{
    try {

        $report = DB::table('crm_reports_rows')->where('id', $id)->first();

        if (!$report) {
            return response()->json([
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        // decode step4 lama
        $oldStep4 = [];

        if (!empty($report->step4)) {
            $oldStep4 = json_decode($report->step4, true) ?? [];
        }

        // data baru dari React
        $newStep4 = array_merge($oldStep4, $request->step4 ?? []);

        DB::table('crm_reports_rows')
            ->where('id', $id)
            ->update([
                'step4' => json_encode($newStep4),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'step4' => $newStep4
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);

    }
}

public function destroy($id)
{
    Log::info("DELETE MASUK: ".$id);

    DB::table('crm_reports_rows')
        ->where('id', $id)
        ->delete();

    DB::table('crm_armada_rows')
        ->where('report_id', $id)
        ->delete();

    return response()->json([
        'success' => true
    ]);
}

}
