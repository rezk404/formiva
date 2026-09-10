<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Insight;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\Ordering;
use Illuminate\Database\Seeder;

/**
 * Enough unpublished content to exercise the CMS properly.
 *
 * Every record here is a draft, a schedule or an archive — nothing this
 * seeder creates is visible on the public site. That is deliberate: the
 * imported content from resources/content is the real site, and demo data
 * that leaked into it would be indistinguishable from a mistake.
 *
 * What it gives you is the states a screen has to handle: status badges with
 * something in each, a filter that returns results, a list long enough to
 * paginate, and an attention panel with real signals in it.
 */
final class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $author = User::query()->where('email', 'editor@formiva.test')->first()
            ?? User::query()->first();

        $category = Category::query()->firstOrCreate(
            ['type' => CategoryType::Insight, 'slug' => 'practice'],
            ['name' => 'Practice', 'position' => Ordering::nextPosition(Category::query()->where('type', CategoryType::Insight))],
        );

        // A journal long enough to page through, in every state.
        Insight::factory()->count(9)->draft()->create([
            'author_id' => $author?->id,
            'category_id' => $category->id,
        ]);

        Insight::factory()->count(3)->scheduled()->create([
            'author_id' => $author?->id,
            'category_id' => $category->id,
        ]);

        Insight::factory()->count(2)->archived()->create([
            'author_id' => $author?->id,
            'category_id' => $category->id,
        ]);

        // One entry with no category, so the dashboard's "missing category"
        // signal has something honest to report.
        Insight::factory()->draft()->uncategorised()->create(['author_id' => $author?->id]);

        Service::factory()->count(2)->draft()->create([
            'position' => Ordering::nextPosition(Service::query()),
        ]);

        Testimonial::factory()->count(3)->hidden()->create([
            'position' => Ordering::nextPosition(Testimonial::query()),
        ]);

        TeamMember::factory()->count(2)->hidden()->create([
            'position' => Ordering::nextPosition(TeamMember::query()),
        ]);

        Ordering::resequence(Service::query());
        Ordering::resequence(Testimonial::query());
        Ordering::resequence(TeamMember::query());

        $this->command?->info('Demo content added — all of it unpublished, so the public site is unchanged.');
    }
}
