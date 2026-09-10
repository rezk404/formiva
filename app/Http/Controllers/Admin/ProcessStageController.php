<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SettingType;
use App\Http\Requests\Admin\ProcessFramingRequest;
use App\Http\Requests\Admin\ProcessStageRequest;
use App\Models\ProcessStage;
use App\Models\Setting;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The process chapter.
 *
 * Stages are a sequence, not a table of unrelated rows — the public page
 * draws them as one continuous line — so the index is a sequence editor: the
 * stages in order, each showing where it sits on the timeline and what it
 * hands over.
 */
final class ProcessStageController extends AdminController
{
    public function index(): View
    {
        $this->authorize('viewAny', ProcessStage::class);

        $framing = (array) Setting::retrieve('content', 'process', []);
        $stages = ProcessStage::query()->ordered()->get();

        return view('admin.process.index', [
            'stages' => $stages,
            'eyebrow' => (string) ($framing['eyebrow'] ?? 'The system'),
            'headline' => array_pad(array_values((array) ($framing['headline'] ?? [])), 2, ''),
            'lede' => (string) ($framing['lede'] ?? ''),
            'span' => [
                'start' => (int) ($stages->min('span_start') ?? 0),
                'end' => (int) ($stages->max('span_end') ?? 100),
            ],
            'about' => $this->aboutCounts(),
        ]);
    }

    public function framing(ProcessFramingRequest $request): RedirectResponse
    {
        Setting::put('content', 'process', [
            'eyebrow' => $request->validated('eyebrow'),
            'headline' => array_values((array) $request->validated('headline')),
            'lede' => $request->validated('lede'),
        ], SettingType::Json);

        return $this->saved('admin.process.index', 'Chapter framing saved.');
    }

    public function create(): View
    {
        $this->authorize('create', ProcessStage::class);

        $last = ProcessStage::query()->ordered()->get()->last();

        return view('admin.process.create', [
            'stage' => new ProcessStage([
                'index_label' => str_pad((string) (ProcessStage::query()->count() + 1), 2, '0', STR_PAD_LEFT),
                // A new stage starts where the last one ended, which is the
                // sequence's own default and almost always what is wanted.
                'span_start' => $last?->span_end ?? 0,
                'span_end' => min(100, ($last?->span_end ?? 0) + 10),
                'weight' => 0.5,
                'overlap' => false,
            ]),
        ]);
    }

    public function store(ProcessStageRequest $request): RedirectResponse
    {
        $stage = ProcessStage::query()->create([
            ...$request->validated(),
            'position' => Ordering::nextPosition(ProcessStage::query()),
        ]);

        return $this->saved('admin.process.index', "“{$stage->title}” added to the sequence.");
    }

    public function edit(ProcessStage $stage): View
    {
        $this->authorize('update', $stage);

        return view('admin.process.edit', ['stage' => $stage]);
    }

    public function update(ProcessStageRequest $request, ProcessStage $stage): RedirectResponse
    {
        $stage->update($request->validated());

        return $this->saved('admin.process.index', "“{$stage->title}” saved.");
    }

    public function destroy(ProcessStage $stage): RedirectResponse
    {
        $this->authorize('delete', $stage);

        // The chapter is a line: with nothing on it there is nothing to draw,
        // and the public page would render an empty rule.
        if (ProcessStage::query()->count() <= 1) {
            return back()->withErrors([
                'stage' => 'The process needs at least one stage. Edit this one instead of removing it.',
            ]);
        }

        $title = $stage->title;
        $stage->delete();

        Ordering::resequence(ProcessStage::query());

        return $this->saved('admin.process.index', "“{$title}” removed from the sequence.");
    }

    public function move(Request $request, ProcessStage $stage): RedirectResponse
    {
        $this->authorize('update', $stage);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($stage, $direction, ProcessStage::query());

        return $this->savedBack($moved ? 'Sequence updated.' : 'Already at that end of the sequence.');
    }
}
