<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Content\CachedContent;
use App\Content\ContentRepository;
use App\Http\Controllers\Controller;
use App\Models\ProcessStage;
use App\Models\StudioPosition;
use App\Models\StudioStat;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Shared ground for the CMS controllers.
 *
 * Three things every module needs and none of them should re-invent: policy
 * authorisation, telling the public cache that something changed, and
 * phrasing a flash message the same way twice.
 *
 * The trait lives here rather than on the framework's base Controller so the
 * public HomeController keeps its current, deliberately bare surface.
 *
 * Where authorisation is asked, once each:
 *
 *   - Actions that take a Form Request (store, update, publish) are gated by
 *     that request's authorize(), which runs before validation — so a refusal
 *     is a 403 rather than a redirect full of field errors.
 *   - Everything else (index, create, edit, destroy, move, toggle) calls
 *     $this->authorize() here.
 *
 * Both routes end at the same policy. Nothing checks a role directly.
 */
abstract class AdminController extends Controller
{
    use AuthorizesRequests;

    /**
     * Invalidate the public content cache after a write.
     *
     * A no-op unless FORMIVA_CONTENT_CACHE is on, in which case the bound
     * repository is a CachedContent wrapper and a version bump is what makes
     * an edit visible on the site immediately rather than up to an hour
     * later. Called from every mutating action rather than from a model
     * observer, so the trigger stays visible where the write happens.
     */
    protected function flushPublicContent(): void
    {
        $content = app(ContentRepository::class);

        if ($content instanceof CachedContent) {
            $content->flush();
        }
    }

    /** Redirect with a success flash, after invalidating public content. */
    protected function saved(string $route, string $message, array $parameters = []): RedirectResponse
    {
        $this->flushPublicContent();

        return redirect()->route($route, $parameters)->with('status', $message);
    }

    /** Return to the previous screen with a success flash. */
    protected function savedBack(string $message): RedirectResponse
    {
        $this->flushPublicContent();

        return back()->with('status', $message);
    }

    /**
     * Applies a whitelisted sort to an index query.
     *
     * The column always comes from $allowed, never from the request, so a
     * hand-edited ?sort= can reorder a list but cannot reach a column the
     * screen does not show.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, string>  $allowed  ui key => column
     * @return array{0: string, 1: string}  the resolved key and direction
     */
    protected function applySort(Builder $query, array $allowed, string $default, ?string $sort, ?string $direction): array
    {
        $key = array_key_exists((string) $sort, $allowed) ? (string) $sort : $default;
        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($allowed[$key], $direction);

        // A stable tiebreak. Without it, two records sharing a position or a
        // publish date swap places between page loads and pagination starts
        // repeating or skipping rows.
        $query->orderBy($query->getModel()->getQualifiedKeyName(), 'asc');

        return [$key, $direction];
    }

    /**
     * The filter values currently in play, for the "clear" control and for
     * keeping the query string across pagination links.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function activeFilters(array $filters): array
    {
        return array_filter($filters, static fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * Sizes for the About sub-navigation.
     *
     * Every screen in that area renders the same local nav, and the nav
     * carries counts so the shape of the chapter is legible from any part of
     * it. Five aggregates, no models hydrated — cheap enough to be honest
     * about, and it lives here because five controllers need the same answer.
     *
     * @return array<string, int>
     */
    protected function aboutCounts(): array
    {
        return [
            'positions' => StudioPosition::query()->count(),
            'team' => TeamMember::query()->count(),
            'process' => ProcessStage::query()->count(),
            'testimonials' => Testimonial::query()->count(),
            'stats' => StudioStat::query()->count(),
        ];
    }
}
