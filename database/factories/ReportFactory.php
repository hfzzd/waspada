<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['phone', 'bank_account', 'ewallet', 'url', 'email']);

        $value = match ($type) {
            'phone' => '+62' . $this->faker->unique()->numerify('8##########'),
            'bank_account' => $this->faker->unique()->numerify('##########'),
            'ewallet' => '08' . $this->faker->unique()->numerify('##########'),
            'url' => 'https://' . $this->faker->unique()->domainWord() . $this->faker->randomElement(['.xyz', '.top', '.site']) . '/login',
            default => $this->faker->unique()->safeEmail(),
        };

        return [
            'scam_entity_id' => null,
            'reporter_name' => $this->faker->name(),
            'reporter_contact' => '08' . $this->faker->numerify('##########'),
            'type' => $type,
            'value' => $value,
            'category' => $this->faker->randomElement(Report::CATEGORIES),
            'description' => $this->faker->randomElement($this->descriptions()),
            'evidence_url' => $this->faker->optional(0.4)->url(),
            'status' => $this->faker->randomElement(['pending', 'pending', 'verified', 'rejected']),
        ];
    }

    /**
     * Contoh deskripsi laporan yang realistis di Indonesia.
     *
     * @return list<string>
     */
    protected function descriptions(): array
    {
        return [
            'Saya menerima pesan WhatsApp yang mengaku dari kurir paket dan meminta saya mengunduh file APK untuk melacak paket.',
            'Ditawari investasi dengan keuntungan 10% per hari lewat grup Telegram, diminta transfer ke rekening pribadi.',
            'Mendapat SMS bahwa saya memenangkan undian berhadiah mobil dan diminta membayar biaya administrasi terlebih dahulu.',
            'Kenalan dari aplikasi kencan meminta saya mentransfer uang dengan alasan darurat keluarga.',
            'Ditawari pinjaman online tanpa agunan dengan bunga sangat rendah, diminta membayar biaya pencairan di muka.',
            'Penjual di marketplace meminta pembayaran ke rekening pribadi di luar aplikasi dengan harga jauh lebih murah.',
            'Menerima email yang mengaku dari bank meminta saya memverifikasi akun lewat tautan yang mencurigakan.',
        ];
    }
}
