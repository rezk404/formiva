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
        // The real site: resources/content lifted into the tables the CMS
        // edits and DatabaseContent reads. Idempotent, so re-running it
        // refreshes the imported records without touching anything added
        // since through the workspace.
        (new ContentImporter(resource_path('content')))->import();

        // Sign-in accounts and unpublished demo content. Never in production:
        // the first would be a credential nobody chose, and the second would
        // put placeholder records in a real database.
        if (! app()->environment('production')) {
            $this->call([
                StaffUserSeeder::class,
                DemoContentSeeder::class,
            ]);
        }
    }
}
