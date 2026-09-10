@extends('layouts.admin', ['title' => $inquiry->reference.' — Inquiries — FORMIVA', 'breadcrumbs' => [['label' => 'Inquiries', 'url' => route('admin.inquiries.index')], ['label' => $inquiry->reference]]])

@section('content')
<x-admin.page-header kicker="Inquiry" :title="$inquiry->reference" :description="$inquiry->name.' · '.$inquiry->company">
    <x-slot:actions><x-admin.button href="mailto:{{ $inquiry->email }}" variant="ghost">Email lead</x-admin.button></x-slot:actions>
</x-admin.page-header>

<div class="admin-layout">
    <div>
        <x-admin.panel kicker="Lead" title="Contact context">
            <dl class="admin-detail-list"><div><dt>Name</dt><dd>{{ $inquiry->name }}</dd></div><div><dt>Company</dt><dd>{{ $inquiry->company }}</dd></div><div><dt>Email</dt><dd><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd></div><div><dt>Phone</dt><dd>{{ $inquiry->phone ?: '—' }}</dd></div><div><dt>Country</dt><dd>{{ $inquiry->country }}</dd></div></dl>
        </x-admin.panel>
        <x-admin.panel kicker="Project" title="The brief">
            <dl class="admin-detail-list"><div><dt>Type</dt><dd>{{ $inquiry->project_type }}</dd></div><div><dt>Industry</dt><dd>{{ $inquiry->industry }}</dd></div><div><dt>Company size</dt><dd>{{ $inquiry->company_size }}</dd></div><div><dt>Services</dt><dd>{{ implode(', ', $inquiry->services ?? []) }}</dd></div><div><dt>Budget</dt><dd>{{ $inquiry->budget_range ?: '—' }}</dd></div><div><dt>Timeline</dt><dd>{{ $inquiry->timeline }}</dd></div></dl>
            <div class="admin-inquiry-copy"><h3>Problem</h3><p>{{ $inquiry->problem }}</p><h3>Scope</h3><p>{{ $inquiry->scope ?: '—' }}</p><h3>Message</h3><p>{{ $inquiry->message }}</p><h3>Notes from intake</h3><p>{{ $inquiry->notes ?: '—' }}</p></div>
        </x-admin.panel>
        <x-admin.panel kicker="Internal" title="Notes">
            <div class="admin-note-list">@forelse ($inquiry->inquiryNotes as $note)<article class="admin-note"><p>{{ $note->body }}</p><small>{{ $note->user->name }} · {{ $note->created_at->diffForHumans() }}</small></article>@empty<p class="admin-field__hint">No internal notes yet.</p>@endforelse</div>
            <form method="POST" action="{{ route('admin.inquiries.notes.store', $inquiry) }}" data-async class="admin-note-form">@csrf<textarea name="body" class="admin-textarea" rows="3" placeholder="Add an internal note..." required></textarea><x-admin.button size="sm">Add note</x-admin.button></form>
        </x-admin.panel>
    </div>
    <aside class="admin-layout__aside">
        <x-admin.panel kicker="Workflow" title="Move this inquiry">
            <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" data-async>@csrf @method('PUT')<x-admin.field name="status" label="Status" type="select" :value="$inquiry->status->value" :options="collect($statuses)->mapWithKeys(fn ($status) => [$status->value => ucfirst($status->value)])->all()" /><x-admin.field name="priority" label="Priority" type="select" :value="$inquiry->priority->value" :options="collect($priorities)->mapWithKeys(fn ($priority) => [$priority->value => ucfirst($priority->value)])->all()" /><x-admin.field name="assigned_to" label="Assignee" type="select" :value="$inquiry->assigned_to" :options="$assignees->pluck('name', 'id')->all()" prompt="Unassigned" /><x-admin.field name="declined_reason" label="Decline reason" type="textarea" rows="2" :value="$inquiry->declined_reason" optional /><x-admin.button block>Save workflow</x-admin.button></form>
        </x-admin.panel>
        <x-admin.panel kicker="Conversion" title="Client relationship">
            @if ($inquiry->client)<p class="admin-field__hint">Converted to <strong>{{ $inquiry->client->name }}</strong>.</p>@else<form method="POST" action="{{ route('admin.inquiries.convert', $inquiry) }}" data-async>@csrf<x-admin.field name="client_id" label="Existing client" type="select" :options="$clients->pluck('name', 'id')->all()" prompt="Create a new client" /><x-admin.field name="client_name" label="New client name" hint="Leave blank to use the company from the inquiry." optional /><x-admin.button variant="publish" block>Convert to client</x-admin.button></form>@endif
        </x-admin.panel>
        <x-admin.panel kicker="Activity" title="History" flush><div class="admin-activity-list">@forelse ($inquiry->activityLogs as $activity)<div class="admin-activity"><strong>{{ $activity->description }}</strong><small>{{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</small></div>@empty<p class="admin-field__hint">No activity yet.</p>@endforelse</div></x-admin.panel>
    </aside>
</div>
@endsection
