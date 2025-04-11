<?php

namespace App\Modules\TempoHours\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TempoHoursController extends Controller
{
    public function sync(Request $request)
    {
        try {
            $period = $request->input('period');
            if (!$period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Period is required'
                ], 400);
            }

            // Run the sync command
            $output = [];
            $exitCode = 0;
            exec("php artisan tempo:sync --period={$period} 2>&1", $output, $exitCode);

            if ($exitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . implode("\n", $output)
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
} 