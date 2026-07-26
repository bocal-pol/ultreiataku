<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Pilgrimage\Database\Seeders\PilgrimageSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PilgrimageSeeder::class,
        ]);
    }
}
