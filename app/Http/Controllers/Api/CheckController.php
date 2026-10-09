<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckRequest;
use App\Services\ScamCheckService;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint pengecekan penipuan (POST /api/check).
 */
class CheckController extends Controller
{
    /**
     * Periksa sebuah nilai dan kembalikan verdict beserta penjelasannya.
     */
    public function __invoke(CheckRequest $request, ScamCheckService $service): JsonResponse
    {
        $result = $service->check(
            $request->validated('type'),
            $request->validated('value')
        );

        return response()->json([
            'data' => [
                'verdict' => $result['verdict'],
                'verdict_label' => $this->verdictLabel($result['verdict']),
                'risk_score' => $result['risk_score'],
                'report_count' => $result['report_count'],
                'explanation' => $result['explanation'],
                'checked_at' => $result['checked_at'],
            ],
        ]);
    }

    /**
     * Label verdict yang ramah untuk ditampilkan ke pengguna.
     */
    protected function verdictLabel(string $verdict): string
    {
        return match ($verdict) {
            'bahaya' => 'BAHAYA',
            'hati_hati' => 'HATI-HATI',
            default => 'AMAN',
        };
    }
}
