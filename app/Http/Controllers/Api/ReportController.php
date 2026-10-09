<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Report;
use App\Models\ScamEntity;
use App\Services\PhoneNormalizer;
use App\Services\ScamScorer;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint laporan penipuan komunitas.
 *
 *  - POST /api/reports : kirim laporan baru (status awal "pending").
 *  - GET  /api/reports : daftar laporan yang sudah terverifikasi.
 */
class ReportController extends Controller
{
    public function __construct(
        protected ScamScorer $scorer
    ) {}

    /**
     * Simpan laporan baru dan perbarui entitas terkait.
     *
     * Setiap laporan menambah report_count pada ScamEntity yang cocok
     * (dibuat jika belum ada), lalu skor dan verdict dihitung ulang
     * dengan ScamScorer.
     */
    public function store(ReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        $normalized = $data['type'] === 'phone'
            ? PhoneNormalizer::normalize($data['value'])
            : trim($data['value']);

        $report = Report::create([
            'type' => $data['type'],
            'value' => $data['value'],
            'category' => $data['category'],
            'description' => $data['description'],
            'reporter_name' => $data['reporter_name'] ?? null,
            'reporter_contact' => $data['reporter_contact'] ?? null,
            'evidence_url' => $data['evidence_url'] ?? null,
            'status' => 'pending',
        ]);

        $entity = ScamEntity::firstOrCreate(
            [
                'type' => $data['type'],
                'normalized_value' => $normalized,
            ],
            [
                'value' => $data['value'],
                'report_count' => 0,
                'risk_score' => 0,
                'verdict' => 'aman',
                'metadata' => [],
            ]
        );

        $entity->increment('report_count');
        $entity->refresh();

        $keywords = $this->scorer->extractDangerKeywords(
            $data['description'].' '.$data['value']
        );

        $scored = $this->scorer->score([
            'report_count' => (int) $entity->report_count,
            'entity_age_days' => $entity->created_at
                ? $entity->created_at->diffInDays(now())
                : 0,
            'danger_keywords' => $keywords,
        ]);

        $entity->update([
            'risk_score' => $scored['score'],
            'verdict' => $scored['verdict'],
            'metadata' => array_merge($entity->metadata ?? [], [
                'danger_keywords' => $keywords,
                'last_reported_at' => now()->toIso8601String(),
            ]),
        ]);

        return response()->json([
            'data' => [
                'id' => $report->getKey(),
                'status' => $report->status,
                'message' => 'Terima kasih! Laporanmu sudah kami terima dan akan diverifikasi oleh tim moderator.',
            ],
        ], 201);
    }

    /**
     * Daftar laporan komunitas yang sudah terverifikasi, terbaru dulu.
     */
    public function index(): JsonResponse
    {
        $reports = Report::where('status', 'verified')
            ->latest()
            ->paginate(15);

        return response()->json($reports);
    }
}
