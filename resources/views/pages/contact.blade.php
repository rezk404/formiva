@extends('layouts.app')

@section('content')
<section class="fv-contact-page" data-world="resolve" data-theme="dark">
    <div class="fv-container fv-contact-page__grid">
        <div>
            <a class="fv-detail-page__back" href="{{ route('home') }}">← Back to FORMIVA</a>
            <span class="fv-contact-page__label">Project intake / 01—09</span>
            <h1>Make the next<br><em>move.</em></h1>
            <p>A useful first conversation starts with context. Share the shape of the work and we will return with a considered point of view.</p>
            <div class="fv-intake__promise"><span>01</span><p>About 4 minutes<br>Private local draft<br>No commitment</p></div>
        </div>
        <form method="POST" action="{{ route('inquiries.store') }}" class="fv-contact-page__form fv-intake" data-contact-form data-recipient="{{ $site['contact']['new_business'] }}" data-intake='@json($intake, JSON_UNESCAPED_UNICODE)'>
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" hidden>
            {{-- "of 09" matches the nine fieldsets below and is the no-JS
                 truth; the script recounts them on boot so the two can never
                 drift if a step is added. --}}
            @if (session('inquiry_success'))<p class="fv-contact-page__notice" role="status">{{ session('inquiry_success.message') }} Reference: {{ session('inquiry_success.reference') }}</p>@endif
            <div class="fv-intake__progress"><span data-intake-progress>01</span><span data-intake-total>of 09</span><div><i data-intake-bar></i></div></div>
            <fieldset data-step="1"><legend>First, who are we meeting?</legend><div class="fv-form-grid"><label>Full name<input name="name" autocomplete="name" required></label><label>Company<input name="company" autocomplete="organization" required></label><label>Work email<input name="email" type="email" autocomplete="email" required></label><label>Phone <span>(optional)</span><input name="phone" type="tel" autocomplete="tel"></label><label>Country<input name="country" autocomplete="country-name" required></label></div></fieldset>
            <fieldset data-step="2" hidden><legend>What are you building?</legend><div class="fv-choice-grid">@foreach ($intake['projectTypes'] as $type)<label class="fv-choice"><input type="radio" name="type" value="{{ $type['value'] }}" data-group="{{ $type['group'] }}" required><span>{{ $type['label'] }}</span></label>@endforeach</div></fieldset>
            <fieldset data-step="3" hidden><legend>Give us the context.</legend><div class="fv-form-grid"><label>Industry<input name="industry" placeholder="e.g. logistics, fintech" required></label><label>Company size<select name="company_size" required><option value="">Choose one</option>@foreach ($intake['companySizes'] as $size)<option>{{ $size }}</option>@endforeach</select></label><label class="fv-contact-page__message">What problem are you trying to solve?<textarea name="problem" rows="5" required></textarea></label></div></fieldset>
            <fieldset data-step="4" hidden><legend>What needs to be in the room?</legend><div class="fv-choice-grid">@foreach ($intake['services'] as $service)<label class="fv-choice"><input type="checkbox" name="services[]" value="{{ $service }}"><span>{{ $service }}</span></label>@endforeach</div><label class="fv-contact-page__message">Known features, integrations or reference URLs<textarea name="scope" rows="4"></textarea></label></fieldset>
            <fieldset data-step="5" hidden><legend>What range is realistic?</legend><p class="fv-intake__hint">Your selection helps us recommend the right shape of engagement. It is an indicative range, not a quotation.</p><div class="fv-choice-grid" data-budget-options></div>{{-- Hidden inputs are barred from constraint validation, so `required`
                 here would be inert and misleading; the rendered radios carry
                 the requirement instead. --}}<input type="hidden" name="budget"></fieldset>
            <fieldset data-step="6" hidden><legend>When should it move?</legend><div class="fv-choice-grid">@foreach ($intake['timelines'] as $timeline)<label class="fv-choice"><input type="radio" name="timeline" value="{{ $timeline }}" required><span>{{ $timeline }}</span></label>@endforeach</div></fieldset>
            <fieldset data-step="7" hidden><legend>Describe the outcome.</legend><label class="fv-contact-page__message">Detailed project brief<textarea name="message" rows="8" required></textarea></label><label class="fv-contact-page__message">Anything else we should know?<textarea name="notes" rows="4"></textarea></label></fieldset>
            <fieldset data-step="8" hidden><legend>Bring references, if useful.</legend><label class="fv-file-drop">Attach a brief, brand assets or references<input name="attachments" type="file" multiple data-file-input><span>Files stay in your browser and are not attached to the draft email — mention them and we will follow up.</span><span class="fv-file-drop__list" data-file-list aria-live="polite"></span></label></fieldset>
            <fieldset data-step="9" hidden><legend>Review the brief.</legend><div class="fv-intake__summary" data-intake-summary></div><div class="fv-intake__estimate" data-intake-estimate></div></fieldset>
            <div class="fv-intake__actions"><button class="fv-btn fv-btn--ghost" type="button" data-intake-back hidden>Back</button><button class="fv-btn fv-btn--light" type="button" data-intake-next>Continue <span>→</span></button><button class="fv-btn fv-btn--light" type="submit" data-intake-submit hidden>Send project brief <span>↗</span></button></div>
            <p class="fv-contact-page__notice" data-intake-notice role="status" aria-live="polite"></p>
        </form>
    </div>
</section>
@endsection
