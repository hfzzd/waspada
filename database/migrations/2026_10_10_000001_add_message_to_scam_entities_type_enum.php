<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan 'message' ke enum scam_entities.type (temuan WAS-SEC-006).
     *
     * Nilai 'message' sudah diterima validasi (CheckRequest, ReportRequest)
     * dan ditawarkan di UI halaman /cek, tetapi ditolak kolom enum di
     * MySQL sehingga POST /api/reports bertipe message error 500.
     * Diubah via pernyataan mentah karena ->change() membutuhkan
     * doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `scam_entities` MODIFY `type` ENUM('phone', 'bank_account', 'ewallet', 'url', 'email', 'message', 'other') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `scam_entities` MODIFY `type` ENUM('phone', 'bank_account', 'ewallet', 'url', 'email', 'other') NOT NULL");
    }
};
