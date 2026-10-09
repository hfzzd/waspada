<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data awal: taksonomi penipuan Indonesia + contoh entitas.
        $this->call([
            ScamEntitySeeder::class,
        ]);

        // Contoh data dummy untuk pengembangan lokal (opsional):
        // \App\Models\ScamEntity::factory(20)->create();
        // \App\Models\Report::factory(50)->create();
    }
}
