<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProvinceCitySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/iran-provinces-cities.json');

        if (! is_file($path)) {
            throw new RuntimeException('Iran province/city dataset is missing: ' . $path);
        }

        $dataset = json_decode(
            file_get_contents($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $now = now();
        $provinceRows = collect($dataset['provinces'] ?? [])
            ->map(fn (array $province): array => [
                'id' => (int) $province['id'],
                'name' => trim((string) $province['name']),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        $cityRows = collect($dataset['cities'] ?? [])
            ->map(fn (array $city): array => [
                'id' => (int) $city['id'],
                'province_id' => (int) $city['province_id'],
                'name' => trim((string) $city['name']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        if (count($provinceRows) !== 31 || $cityRows->isEmpty()) {
            throw new RuntimeException('The Iran location dataset is incomplete or malformed.');
        }

        DB::transaction(function () use ($provinceRows, $cityRows): void {
            DB::table('provinces')->upsert(
                $provinceRows,
                ['id'],
                ['name', 'updated_at']
            );

            foreach ($cityRows->chunk(500) as $chunk) {
                DB::table('cities')->upsert(
                    $chunk->all(),
                    ['id'],
                    ['province_id', 'name', 'updated_at']
                );
            }
        });

        $this->command?->info(sprintf(
            'Imported %d provinces and %d cities (dataset %s, official source year %s).',
            count($provinceRows),
            $cityRows->count(),
            $dataset['dataset_version'] ?? 'unknown',
            $dataset['official_source_year'] ?? 'unknown'
        ));
    }
}
