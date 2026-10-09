<?php

namespace Database\Factories;

use App\Models\ScamEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScamEntity>
 */
class ScamEntityFactory extends Factory
{
    protected $model = ScamEntity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['phone', 'bank_account', 'ewallet', 'url', 'email', 'other']);

        $value = match ($type) {
            'phone' => '+62' . $this->faker->unique()->numerify('8##########'),
            'bank_account' => $this->faker->unique()->numerify('##########'),
            'ewallet' => '08' . $this->faker->unique()->numerify('##########'),
            'url' => 'https://' . $this->faker->unique()->domainWord() . $this->faker->randomElement(['.xyz', '.top', '.site']) . '/login',
            'email' => $this->faker->unique()->safeEmail(),
            default => $this->faker->unique()->word(),
        };

        $metadata = match ($type) {
            'bank_account' => ['bank' => $this->faker->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI'])],
            'ewallet' => ['provider' => $this->faker->randomElement(['DANA', 'OVO', 'GoPay'])],
            default => null,
        };

        return [
            'type' => $type,
            'value' => $value,
            'normalized_value' => $this->normalize($type, $value),
            'verdict' => $this->faker->randomElement(['aman', 'hati_hati', 'bahaya', 'bahaya']),
            'risk_score' => $this->faker->numberBetween(0, 100),
            'report_count' => $this->faker->numberBetween(1, 50),
            'first_reported_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
            'last_reported_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'metadata' => $metadata,
        ];
    }

    /**
     * Normalisasi sederhana: huruf kecil, buang karakter non-alnum,
     * nomor HP Indonesia 0xxx -> 62xxx.
     */
    protected function normalize(string $type, string $value): string
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]/', '', $value));

        if (in_array($type, ['phone', 'ewallet'], true) && str_starts_with($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        }

        return $normalized;
    }
}
