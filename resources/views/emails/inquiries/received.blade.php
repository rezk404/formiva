<h1>New project inquiry: {{ $inquiry->reference }}</h1>
<p><strong>{{ $inquiry->name }}</strong> from {{ $inquiry->company }} submitted a project brief.</p>
<p>Project type: {{ $inquiry->project_type }}<br>Services: {{ implode(', ', $inquiry->services ?? []) }}</p>
<p>{{ $inquiry->problem }}</p>
<p><a href="{{ route('admin.inquiries.show', $inquiry) }}">Open inquiry in the workspace</a></p>
