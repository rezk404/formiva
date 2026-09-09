<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Content\ContentImporter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        (new ContentImporter(resource_path('content')))->import();
    }
}
