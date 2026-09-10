@extends('layouts.admin', ['title' => 'Inquiries — FORMIVA', 'breadcrumbs' => [['label' => 'Inquiries']]])

@section('content')
<x-admin.page-header kicker="Operations" title="Inquiries" description="Project briefs from the public intake, ready for review and qualification.">
    <x-slot:actions><x-admin.button href="{{ route('contact') }}" target="_blank" variant="ghost" icon="external">View intake</x-admin.button></x-slot:actions>
</x-admin.page-header>

<x-admin.tabs :current="$filters['status'] ?: 'all'" :tabs="collect(['all' => 'All'])->merge(collect(\App\Enums\InquiryStatus::cases())->mapWithKeys(fn ($status) => [$status->value => ucfirst($status->value)]))->mapWithKeys(fn ($label, $key) => [$key => ['label' => $label, 'count' => $counts[$key] ?? 0]])->all()" />

<x-admin.filters :active="$active" :query="$filters['q']" :keep="['status' => $filters['status']]" placeholder="Search reference, lead or company">
    <x-admin.filter-select name="priority" label="Priority" any="Any priority" :value="$filters['priority']" :options="collect(\App\Enums\InquiryPriority::cases())->mapWithKeys(fn ($priority) => [$priority->value => ucfirst($priority->value)])->all()" />
    <x-admin.filter-select name="assigned_to" label="Assignee" any="Anyone" :value="$filters['assigned_to']" :options="$assignees->pluck('name', 'id')->all()" />
</x-admin.filters>

<div class="admin-panel">
    @if ($inquiries->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><x-admin.sort column="reference" :sort="$sort" :direction="$direction">Reference</x-admin.sort><x-admin.sort column="name" :sort="$sort" :direction="$direction">Lead</x-admin.sort><th>Project type</th><x-admin.sort column="status" :sort="$sort" :direction="$direction">Status</x-admin.sort><x-admin.sort column="priority" :sort="$sort" :direction="$direction">Priority</x-admin.sort><th>Assignee</th><x-admin.sort column="received" :sort="$sort" :direction="$direction" default="desc">Received</x-admin.sort><th><span class="admin-sr">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($inquiries as $inquiry)
                        <tr data-removable-row>
                            <td><a class="admin-table__primary" href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->reference }}</a><span class="admin-table__secondary">{{ $inquiry->email }}</span></td>
                            <td data-label="Lead"><span class="admin-table__primary">{{ $inquiry->name }}</span><span class="admin-table__secondary">{{ $inquiry->company }}</span></td>
                            <td data-label="Project type">{{ $inquiry->project_type ?: 'General' }}</td>
                            <td data-label="Status"><span class="admin-badge admin-badge--{{ $inquiry->status->value === 'new' ? 'info' : ($inquiry->status->value === 'qualified' ? 'positive' : 'neutral') }}">{{ ucfirst($inquiry->status->value) }}</span></td>
                            <td data-label="Priority"><span class="admin-badge admin-badge--{{ $inquiry->priority->value === 'high' ? 'warning' : 'neutral' }}">{{ ucfirst($inquiry->priority->value) }}</span></td>
                            <td data-label="Assignee">{{ $inquiry->assignee?->name ?? 'Unassigned' }}</td>
                            <td class="admin-table__num" data-label="Received">{{ $inquiry->created_at?->format('j M Y') }}</td>
                            <td><x-admin.button href="{{ route('admin.inquiries.show', $inquiry) }}" variant="ghost" size="sm">Review</x-admin.button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$inquiries" label="inquiries" />
    @elseif (count($active) > 0)
        <x-admin.empty icon="search" title="Nothing matches that" description="No inquiries match the current search and filters."><x-slot:actions><x-admin.button href="{{ route('admin.inquiries.index') }}" variant="ghost">Clear filters</x-admin.button></x-slot:actions></x-admin.empty>
    @else
        <x-admin.empty icon="inbox" title="No inquiries yet" description="Completed project briefs will arrive here for review."><x-slot:actions><x-admin.button href="{{ route('contact') }}" target="_blank" variant="ghost">View public intake</x-admin.button></x-slot:actions></x-admin.empty>
    @endif
</div>
@endsection
