<?php

namespace Database\Seeders;

use App\Services\CompanyService;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function __construct(
        private readonly CompanyService $service
    ) {}

    public function run(): void
    {
        // edrpou is stored as a string (not int) to preserve leading zeros.
        // Re-running this seeder is safe: upsert() handles duplicates and updates gracefully.

        // Create first company — version 1
        $this->service->upsert([
            'name'    => 'ТОВ Українська енергетична біржа',
            'edrpou'  => '37027819',
            'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44',
        ]);

        // Update address — version 2
        $this->service->upsert([
            'name'    => 'ТОВ Українська енергетична біржа',
            'edrpou'  => '37027819',
            'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44, 4 поверх',
        ]);

        // Duplicate — no new version created
        $this->service->upsert([
            'name'    => 'ТОВ Українська енергетична біржа',
            'edrpou'  => '37027819',
            'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44, 4 поверх',
        ]);

        // Second company — version 1
        $this->service->upsert([
            'name'    => 'ТОВ Нафтогаз України',
            'edrpou'  => '20077720',
            'address' => '01001, Україна, м. Київ, вул. Богдана Хмельницького, 6',
        ]);
    }
}
