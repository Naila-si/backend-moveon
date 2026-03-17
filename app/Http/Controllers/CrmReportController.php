<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmReportController extends Controller {

public function update(Request $request, $id)
{
    try {

        $report = DB::table('crm_reports_rows')
            ->where('pk_id', $id) // ✅ FIX
            ->first();

        if (!$report) {
            return response()->json([
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        $oldStep4 = [];

        if (!empty($report->step4)) {
            $oldStep4 = json_decode($report->step4, true) ?? [];
        }

        $newStep4 = array_merge($oldStep4, $request->step4 ?? []);

        DB::table('crm_reports_rows')
            ->where('pk_id', $id) // ✅ FIX
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
    try {

        DB::table('crm_reports_rows')
            ->where('pk_id', $id)
            ->delete();

        return response()->json([
            'success' => true
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
}
