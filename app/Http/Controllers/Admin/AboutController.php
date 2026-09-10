<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SettingType;
use App\Http\Requests\Admin\AboutOverviewRequest;
use App\Http\Requests\Admin\AboutStatsRequest;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\StudioStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * About — everything that explains who FORMIVA is.
 *
 * The studio story, the positions, the people, the process, the proof and
 * the numbers are one subject with five screens, not five subjects. They
 * were four unrelated menu entries before, which made a writer decide which
 * database table their sentence belonged to before they could write it.
 *
 * The tables stay exactly as they were. This is navigation and framing.
 */
final class AboutController extends AdminController
{
    public function overview(): View
    {
        $this->authorize('viewAny', StudioPosition::class);

        $framing = (array) Setting::retrieve('content', 'studio', []);

        return view('admin.about.overview', [
            'eyebrow' => (string) ($framing['eyebrow'] ?? 'The studio'),
            'headline' => array_pad(array_values((array) ($framing['headline'] ?? [])), 2, ''),
            'story' => array_values((array) ($framing['story'] ?? [])) ?: [''],
            'positions' => StudioPosition::query()->ordered()->get(),
            'counts' => $this->aboutCounts(),
        ]);
    }

    public function updateOverview(AboutOverviewRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            Setting::put('content', 'studio', [
                'eyebrow' => $request->validated('eyebrow'),
                'headline' => array_values((array) $request->validated('headline')),
                'story' => array_values((array) $request->validated('story')),
            ], SettingType::Json);

            $this->sync(StudioPosition::class, $request->positions(), ['index_label', 'title', 'body']);
        });

        return $this->saved('admin.about.overview', 'Studio overview saved.');
    }

    public function stats(): View
    {
        $this->authorize('viewAny', StudioStat::class);

        return view('admin.about.stats', [
            'stats' => StudioStat::query()->ordered()->get(),
            'counts' => $this->aboutCounts(),
        ]);
    }

    public function updateStats(AboutStatsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $this->sync(StudioStat::class, $request->stats(), ['value', 'suffix', 'label', 'note']);
        });

        return $this->saved('admin.about.stats', 'Studio statistics saved.');
    }

    /**
     * Reconciles a nested repeater with its table.
     *
     * Rows arriving with a known id keep it, so a record referenced anywhere
     * else survives a reorder; anything no longer submitted is removed, and
     * position follows submitted order.
     *
     * @param  class-string<Model>  $model
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $columns
     */
    private function sync(string $model, array $rows, array $columns): void
    {
        $existing = $model::query()->pluck('id')->all();
        $kept = [];

        foreach ($rows as $position => $row) {
            $attributes = ['position' => $position];

            foreach ($columns as $column) {
                $attributes[$column] = $row[$column] ?? '';
            }

            $id = isset($row['id']) ? (int) $row['id'] : 0;

            if ($id > 0 && in_array($id, $existing, true)) {
                $model::query()->whereKey($id)->update($attributes);
                $kept[] = $id;

                continue;
            }

            $kept[] = $model::query()->create($attributes)->id;
        }

        $model::query()->whereNotIn('id', $kept === [] ? [0] : $kept)->delete();
    }
}
