<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ScamEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk endpoint laporan komunitas dan threat feed:
 *  - POST /api/reports
 *  - GET  /api/reports
 *  - GET  /api/threat-feed
 *  - GET  /api/threat-feed/{entity}
 */
class ReportsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter antar test agar throttle tidak bocor antar test.
        $this->app['cache']->flush();
    }

    /**
     * Payload laporan yang valid.
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'phone',
            'value' => '0812-3456-7890',
            'category' => 'phising',
            'description' => 'Saya menerima pesan WhatsApp yang mengaku dari kurir dan meminta mengunduh file APK.',
            'reporter_name' => 'Budi Santoso',
        ], $overrides);
    }

    public function test_store_membuat_laporan_dan_mengembalikan_201(): void
    {
        $response = $this->postJson('/api/reports', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure(['data' => ['id', 'status', 'message']]);

        $this->assertDatabaseHas('reports', [
            'type' => 'phone',
            'value' => '0812-3456-7890',
            'status' => 'pending',
        ]);

        // Entitas dibuat dengan nilai ternormalisasi dan report_count = 1.
        $this->assertDatabaseHas('scam_entities', [
            'type' => 'phone',
            'normalized_value' => '6281234567890',
            'report_count' => 1,
        ]);
    }

    public function test_store_menolak_deskripsi_terlalu_pendek(): void
    {
        $response = $this->postJson('/api/reports', $this->validPayload([
            'description' => 'Tipu!',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_store_menolak_kategori_tidak_valid(): void
    {
        $response = $this->postJson('/api/reports', $this->validPayload([
            'category' => 'spam',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('category');
    }

    public function test_index_hanya_menampilkan_laporan_terverifikasi(): void
    {
        Report::factory()->count(2)->create(['status' => 'verified']);
        Report::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/reports');

        $response->assertOk();

        $items = $response->json('data');
        $this->assertCount(2, $items);

        foreach ($items as $item) {
            $this->assertSame('verified', $item['status']);
        }
    }

    public function test_threat_feed_hanya_menampilkan_entitas_bahaya(): void
    {
        ScamEntity::factory()->create(['verdict' => 'bahaya', 'report_count' => 10]);
        ScamEntity::factory()->create(['verdict' => 'hati_hati']);
        ScamEntity::factory()->create(['verdict' => 'aman']);

        $response = $this->getJson('/api/threat-feed');

        $response->assertOk();

        $items = $response->json('data');
        $this->assertCount(1, $items);
        $this->assertSame('bahaya', $items[0]['verdict']);
    }

    public function test_threat_feed_show_mengembalikan_detail_entitas(): void
    {
        $entity = ScamEntity::factory()->create(['verdict' => 'bahaya']);

        $response = $this->getJson('/api/threat-feed/'.$entity->getKey());

        $response->assertOk()
            ->assertJsonPath('data.id', $entity->getKey());
    }

    public function test_threat_feed_show_404_untuk_entitas_tidak_ada(): void
    {
        $this->getJson('/api/threat-feed/999999')->assertNotFound();
    }
}
