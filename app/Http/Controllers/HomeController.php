<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\ContentRepository;
use Illuminate\Contracts\View\View;

/**
 * Composes the public experience.
 *
 * This is presentation assembly, not business logic — the controller reads
 * from the content contract and hands the result to Blade. It gains no
 * responsibilities when the CMS arrives; only the bound implementation of
 * ContentRepository changes.
 */
class HomeController extends Controller
{
    public function __construct(private readonly ContentRepository $content)
    {
    }

    public function index(): View
    {
        return view('pages.home', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'projects' => $this->content->featuredProjects(),
            'caseStudy' => $this->content->caseStudy(),
            'studio' => $this->content->studio(),
            'process' => $this->content->process(),
            'team' => $this->content->team(),
            'testimonials' => $this->content->testimonials(),
            'insights' => $this->content->insights(),
            'clients' => $this->content->clients(),
        ]);
    }

    public function project(string $slug): View
    {
        $project = $this->content->project($slug);
        abort_unless($project, 404);

        return view('pages.project', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'project' => $project,
            'projects' => $this->content->projects(),
            'neighbors' => $this->content->projectNeighbors($slug),
            'title' => "{$project['name']} — FORMIVA",
            'description' => $project['description'],
        ]);
    }

    public function work(): View
    {
        return view('pages.work.index', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'projects' => $this->content->projects(),
            'title' => 'Work — FORMIVA',
            'description' => 'Selected digital products, web experiences, and systems by FORMIVA.',
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'title' => 'Services — FORMIVA',
            'description' => 'Digital products, business systems and digital experience — what FORMIVA builds, and why a business needs each one.',
        ]);
    }

    public function studio(): View
    {
        return view('pages.studio', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'studio' => $this->content->studio(),
            'process' => $this->content->process(),
            'team' => $this->content->team(),
            'title' => 'Studio — FORMIVA',
            'description' => 'Who FORMIVA is, how the studio works, and the people behind the digital products and business systems it builds.',
        ]);
    }

    public function insight(string $slug): View
    {
        $insight = $this->content->insight($slug);
        abort_unless($insight, 404);

        return view('pages.insight', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'insight' => $insight,
            'insights' => $this->content->insights(),
            'title' => "{$insight['title']} — FORMIVA",
            'description' => $insight['dek'],
        ]);
    }

    public function insightsIndex(): View
    {
        return view('pages.insights-index', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'insights' => $this->content->insights(),
            'title' => 'Insights — FORMIVA',
            'description' => 'Notes on design, technology, and motion from FORMIVA.',
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'site' => $this->content->site(),
            'services' => $this->content->services(),
            'intake' => $this->content->intake(),
            'title' => 'Start a project — FORMIVA',
            'description' => 'Start a project with FORMIVA.',
        ]);
    }
}
