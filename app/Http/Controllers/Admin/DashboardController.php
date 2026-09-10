<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\InquiryStatus;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Insight;
use App\Models\Inquiry;
use App\Models\IntakeOption;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The workspace.
 *
 * Every number here is counted from a table — nothing is modelled, projected
 * or invented — and the counting is done in four aggregate queries rather
 * than the twenty separate ones the shape of the screen would suggest.
 *
 * The order of the page is the order of a working morning: what needs a
 * decision, then what the studio touched last, then how the whole body of
 * content stands. Attention comes first because it is the only part anyone
 * has to act on.
 */
final class DashboardController extends AdminController
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $insights = $this->statusCounts(Insight::query());
        $services = $this->statusCounts(Service::query());
        $projects = $this->projectCounts();
        $inquiries = $this->inquiryCounts();
        $flags = $this->flags();

        return view('admin.dashboard', [
            'user' => $user,
            'metrics' => $this->metrics($insights, $services, $projects, $inquiries),
            'health' => [
                'Projects' => ['route' => 'admin.projects.index', 'counts' => $projects],
                'Insights' => ['route' => 'admin.insights.index', 'counts' => $insights],
                'Services' => ['route' => 'admin.services.index', 'counts' => $services],
            ],
            'recent' => $this->recent(),
            'attention' => $this->attention($insights, $services, $projects, $flags, $inquiries),
            'actions' => $this->quickActions($user),
        ]);
    }

    /**
     * One grouped count per content type, keyed by status value. Four rows
     * back from the database instead of four round trips per type.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return array<string, int>
     */
    private function statusCounts(Builder $query): array
    {
        $counts = $query->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $shape = [];

        foreach (ContentStatus::cases() as $status) {
            $shape[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        $shape['total'] = array_sum($shape);

        return $shape;
    }

    /** @return array<string, int> */
    private function projectCounts(): array
    {
        $counts = Project::query()->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $shape = [];
        foreach (ProjectStatus::cases() as $status) {
            $shape[$status->value] = (int) ($counts[$status->value] ?? 0);
        }
        $shape['total'] = array_sum($shape);

        return $shape;
    }

    /** @return array<string, int> */
    private function inquiryCounts(): array
    {
        $counts = Inquiry::query()->toBase()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')->all();
        $shape = [];
        foreach (\App\Enums\InquiryStatus::cases() as $status) $shape[$status->value] = (int) ($counts[$status->value] ?? 0);
        $shape['total'] = array_sum($shape);

        return $shape;
    }

    /**
     * The handful of yes/no facts the attention panel asks about, gathered in
     * one query each rather than one per sentence.
     *
     * @return array<string, int>
     */
    private function flags(): array
    {
        $now = now();

        return [
            'due' => Insight::query()->due()->count() + Service::query()->due()->count() + Project::query()->due()->count(),
            'soon' => Insight::query()
                ->scheduled()
                ->where('published_at', '<=', $now->copy()->addDays(3))
                ->count(),
            'uncategorised' => Insight::query()->whereNull('category_id')->count(),
            'unlisted_team' => TeamMember::query()->where('is_published', false)->count(),
            'hidden_quotes' => Testimonial::query()->where('is_published', false)->count(),
            'withdrawn_options' => IntakeOption::query()->where('is_active', false)->count(),
            'live_quotes' => Testimonial::query()->where('is_published', true)->count(),
            'positions' => StudioPosition::query()->count(),
            'site_settings' => Setting::query()->where('group', 'content')->where('key', 'site')->count(),
        ];
    }

    /**
     * Headline counts. Six aggregates, no relations, no models hydrated —
     * this is the first thing rendered after sign-in and should not wait.
     *
     * @param  array<string, int>  $insights
     * @param  array<string, int>  $services
     * @return array<int, array<string, mixed>>
     */
    private function metrics(array $insights, array $services, array $projects, array $inquiries): array
    {
        $projectTotals = Project::query()->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = ? and (published_at is null or published_at <= ?) then 1 else 0 end) as live', [
                ContentStatus::Published->value,
                now()->toDateTimeString(),
            ])
            ->first();

        return [
            [
                'label' => 'Projects',
                'value' => (int) ($projectTotals->total ?? 0),
                'detail' => $projects[ProjectStatus::Published->value].' live · '.$projects[ProjectStatus::Draft->value].' draft',
                'href' => route('admin.projects.index'),
            ],
            [
                'label' => 'Inquiries',
                'value' => $inquiries['total'],
                'detail' => $inquiries['new'].' new · '.$inquiries['qualified'].' qualified',
                'href' => route('admin.inquiries.index'),
            ],
            [
                'label' => 'Clients',
                'value' => Client::query()->count(),
                'detail' => 'On the books',
                'href' => null,
            ],
            [
                'label' => 'Insights',
                'value' => $insights['total'],
                'detail' => $insights[ContentStatus::Published->value].' published',
                'href' => route('admin.insights.index'),
            ],
            [
                'label' => 'Services',
                'value' => $services['total'],
                'detail' => $services[ContentStatus::Published->value].' published',
                'href' => route('admin.services.index'),
            ],
            [
                'label' => 'Studio',
                'value' => TeamMember::query()->count(),
                'detail' => TeamMember::query()->where('is_published', true)->count().' listed',
                'href' => route('admin.team.index'),
            ],
        ];
    }

    /**
     * One list, not three. What the studio touched last is a single timeline
     * regardless of which table a row came from, and reading it that way is
     * how anyone actually remembers where they left off.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recent()
    {
        $inquiries = Inquiry::query()
            ->select(['id', 'reference', 'name', 'company', 'status', 'priority', 'created_at', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Inquiry $inquiry): array => [
                'kind' => 'Inquiry',
                'title' => $inquiry->reference.' · '.$inquiry->name,
                'meta' => $inquiry->company ?: 'Project brief',
                'by' => $inquiry->priority->value === 'high' ? 'High priority' : null,
                'status' => $inquiry->status,
                'at' => null,
                'updated' => $inquiry->updated_at,
                'href' => route('admin.inquiries.show', $inquiry),
            ]);

        $projects = Project::query()
            ->select(['id', 'name', 'slug', 'status', 'published_at', 'updated_at', 'client_id'])
            ->with('client:id,name')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Project $project): array => [
                'kind' => 'Project',
                'title' => $project->name,
                'meta' => $project->client?->name ?? 'Unassigned',
                'by' => null,
                'status' => $project->status,
                'at' => $project->published_at,
                'updated' => $project->updated_at,
                'href' => route('admin.projects.edit', $project),
            ]);

        $insights = Insight::query()
            ->select(['id', 'title', 'slug', 'status', 'published_at', 'updated_at', 'category_id', 'author_id'])
            ->with(['category:id,name', 'author:id,name'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (Insight $insight): array => [
                'kind' => 'Insight',
                'title' => $insight->title,
                'meta' => $insight->category?->name ?? 'Uncategorised',
                'by' => $insight->author?->name,
                'status' => $insight->status,
                'at' => $insight->published_at,
                'updated' => $insight->updated_at,
                'href' => route('admin.insights.edit', $insight),
            ]);

        $services = Service::query()
            ->select(['id', 'title', 'slug', 'status', 'published_at', 'updated_at'])
            ->withCount('items')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Service $service): array => [
                'kind' => 'Service',
                'title' => $service->title,
                'meta' => $service->items_count.' '.str('capability')->plural($service->items_count),
                'by' => null,
                'status' => $service->status,
                'at' => $service->published_at,
                'updated' => $service->updated_at,
                'href' => route('admin.services.edit', $service),
            ]);

        return $inquiries->concat($projects)->concat($insights)->concat($services)
            ->sortByDesc('updated')
            ->take(6)
            ->values();
    }

    /**
     * Only things a person can act on, phrased as the decision rather than
     * the count. An empty panel is the normal, good state.
     *
     * @param  array<string, int>  $insights
     * @param  array<string, int>  $services
     * @param  array<string, int>  $flags
     * @return array<int, array<string, mixed>>
     */
    private function attention(array $insights, array $services, array $projects, array $flags, array $inquiries): array
    {
        $signals = [];

        if ($inquiries['new'] > 0) {
            $signals[] = [
                'tone' => 'info',
                'title' => $inquiries['new'].' new '.str('inquiry')->plural($inquiries['new']),
                'body' => 'Fresh project briefs are waiting for a first review.',
                'href' => route('admin.inquiries.index', ['status' => 'new']),
                'action' => 'Review inquiries',
            ];
        }

        if ($flags['due'] > 0) {
            $signals[] = [
                'tone' => 'warning',
                'title' => $flags['due'].' scheduled '.str('item')->plural($flags['due']).' past due',
                'body' => 'Their moment has passed and they are still not live. The publish-due command promotes them, or publish them by hand now.',
                'href' => route('admin.insights.index', ['status' => ContentStatus::Scheduled->value]),
                'action' => 'Publish now',
            ];
        }

        $drafts = $insights[ContentStatus::Draft->value] + $services[ContentStatus::Draft->value] + $projects[ProjectStatus::Draft->value];

        if ($drafts > 0) {
            $signals[] = [
                'tone' => 'neutral',
                'title' => $drafts.' '.str('draft')->plural($drafts).' waiting',
                'body' => 'Written but not published. Nothing here is visible to a visitor yet.',
                'href' => route('admin.projects.index', ['status' => ProjectStatus::Draft->value]),
                'action' => 'Review drafts',
            ];
        }

        if ($flags['soon'] > 0) {
            $signals[] = [
                'tone' => 'info',
                'title' => $flags['soon'].' publishing within three days',
                'body' => 'Worth a last read before it goes out on its own.',
                'href' => route('admin.insights.index', ['status' => ContentStatus::Scheduled->value]),
                'action' => 'Check the queue',
            ];
        }

        if ($flags['uncategorised'] > 0) {
            $signals[] = [
                'tone' => 'warning',
                'title' => $flags['uncategorised'].' '.str('entry')->plural($flags['uncategorised']).' with no category',
                'body' => 'The journal index falls back to “Notes” for these, and the category filter cannot find them.',
                'href' => route('admin.insights.index'),
                'action' => 'Assign categories',
            ];
        }

        if ($flags['live_quotes'] === 0 && $flags['hidden_quotes'] > 0) {
            $signals[] = [
                'tone' => 'warning',
                'title' => 'No testimonial is live',
                'body' => 'The proof chapter is hidden from the homepage entirely until one is published.',
                'href' => route('admin.testimonials.index', ['state' => 'hidden']),
                'action' => 'Publish a quote',
            ];
        }

        if ($flags['positions'] === 0) {
            $signals[] = [
                'tone' => 'warning',
                'title' => 'The studio has no positions',
                'body' => 'About is what explains who FORMIVA is. Without a position the chapter has nothing to argue.',
                'href' => route('admin.about.overview'),
                'action' => 'Write the overview',
            ];
        }

        if ($flags['site_settings'] === 0 && Gate::allows('viewAny', Setting::class)) {
            $signals[] = [
                'tone' => 'warning',
                'title' => 'Site settings have never been saved',
                'body' => 'The public site is falling back to the values shipped in the repository.',
                'href' => route('admin.settings.edit'),
                'action' => 'Review settings',
            ];
        }

        if ($flags['unlisted_team'] > 0) {
            $signals[] = [
                'tone' => 'neutral',
                'title' => $flags['unlisted_team'].' hidden from the studio page',
                'body' => 'Their records exist but /studio does not show them.',
                'href' => route('admin.team.index', ['state' => 'hidden']),
                'action' => 'Review the team',
            ];
        }

        if ($flags['withdrawn_options'] > 0) {
            $signals[] = [
                'tone' => 'neutral',
                'title' => $flags['withdrawn_options'].' intake '.str('option')->plural($flags['withdrawn_options']).' switched off',
                'body' => 'Visitors cannot choose these on /start-a-project.',
                'href' => route('admin.intake.index', ['state' => 'inactive']),
                'action' => 'Review intake',
            ];
        }

        return $signals;
    }

    /**
     * Shown only where the signed-in user's policies allow the thing — an
     * editor never sees a settings shortcut that would 403.
     *
     * @return array<int, array<string, string>>
     */
    private function quickActions(User $user): array
    {
        $candidates = [
            ['ability' => 'create', 'subject' => Project::class, 'label' => 'New project', 'meta' => 'Work · body of work', 'icon' => 'projects', 'href' => route('admin.projects.create')],
            ['ability' => 'create', 'subject' => Insight::class, 'label' => 'Write an insight', 'meta' => 'Journal', 'icon' => 'insights', 'href' => route('admin.insights.create')],
            ['ability' => 'create', 'subject' => Service::class, 'label' => 'Add a service', 'meta' => 'Capability index', 'icon' => 'services', 'href' => route('admin.services.create')],
            ['ability' => 'create', 'subject' => Testimonial::class, 'label' => 'Add a testimonial', 'meta' => 'About · proof', 'icon' => 'testimonials', 'href' => route('admin.testimonials.create')],
            ['ability' => 'create', 'subject' => TeamMember::class, 'label' => 'Add a team member', 'meta' => 'About · people', 'icon' => 'team', 'href' => route('admin.team.create')],
            ['ability' => 'viewAny', 'subject' => StudioPosition::class, 'label' => 'Edit About', 'meta' => 'Story and positions', 'icon' => 'studio', 'href' => route('admin.about.overview')],
            ['ability' => 'viewAny', 'subject' => Setting::class, 'label' => 'Site settings', 'meta' => 'Configuration', 'icon' => 'settings', 'href' => route('admin.settings.edit')],
        ];

        $allowed = [];

        foreach ($candidates as $candidate) {
            if (Gate::forUser($user)->allows($candidate['ability'], $candidate['subject'])) {
                $allowed[] = [
                    'label' => $candidate['label'],
                    'meta' => $candidate['meta'],
                    'icon' => $candidate['icon'],
                    'href' => $candidate['href'],
                ];
            }
        }

        return $allowed;
    }
}
