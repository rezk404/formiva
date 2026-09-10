@props(['record', 'url' => null, 'rows' => []])

{{--
    The read-only half of an editor: what this record currently is, rather
    than anything you change here. The controls that change it live in the
    publishing bar above the form, where they can be their own forms.
--}}

<x-admin.panel kicker="At a glance" title="This record">
    <dl class="admin-facts">
        <div>
            <dt>State</dt>
            <dd><x-admin.status :status="$record->status" :at="$record->published_at" /></dd>
        </div>

        @if ($record->published_at)
            <div>
                <dt>{{ $record->isLive() ? 'Dated' : 'Set for' }}</dt>
                <dd>{{ $record->published_at->format('j M Y, H:i') }}</dd>
            </div>
        @endif

        @foreach ($rows as $term => $value)
            <div>
                <dt>{{ $term }}</dt>
                <dd>{{ $value }}</dd>
            </div>
        @endforeach

        <div>
            <dt>Last saved</dt>
            <dd>{{ $record->updated_at?->diffForHumans() ?? 'Never' }}</dd>
        </div>
    </dl>

    @if ($url)
        <a class="admin-facts__link" href="{{ $url }}" target="_blank" rel="noopener">
            <x-admin.icon name="external" />
            {{ $record->isLive() ? 'View on the site' : 'Preview the public URL' }}
        </a>
    @endif
</x-admin.panel>
