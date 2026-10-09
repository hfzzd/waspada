<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScamEntity;
use Illuminate\Http\JsonResponse;

/**
 * Feed intelijen ancaman publik (untuk integrasi pihak ketiga
 * dan ekstensi browser).
 *
 *  - GET /api/threat-feed       : entitas dengan verdict "bahaya".
 *  - GET /api/threat-feed/{id} : detail satu entitas.
 */
class ThreatFeedController extends Controller
{
    /**
     * Daftar entitas berbahaya, diurutkan dari laporan terbanyak.
     */
    public function index(): JsonResponse
    {
        $entities = ScamEntity::where('verdict', 'bahaya')
            ->orderByDesc('report_count')
            ->paginate(20);

        return response()->json($entities);
    }

    /**
     * Detail satu entitas ancaman.
     */
    public function show(ScamEntity $entity): JsonResponse
    {
        return response()->json([
            'data' => $entity,
        ]);
    }
}
