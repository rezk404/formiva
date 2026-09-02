@extends('layouts.app')

@section('content')
<section class="fv-contact-page" data-world="resolve" data-theme="dark">
    <div class="fv-container fv-contact-page__grid">
        <div>
            <a class="fv-detail-page__back" href="{{ route('home') }}#contact">← Back to FORMIVA</a>
            <span class="fv-contact-page__label">Start a project</span>
            <h1>Give your idea<br><em>a life.</em></h1>
            <p>Tell us what you are building. Until the CMS is connected, submitting opens your email client with the completed brief.</p>
        </div>
        <form class="fv-contact-page__form" data-contact-form data-recipient="{{ $site['contact']['new_business'] }}" novalidate>
            <label>Name<input name="name" autocomplete="name" required></label>
            <label>Email<input name="email" type="email" autocomplete="email" required></label>
            <label>Company<input name="company" autocomplete="organization"></label>
            <label>Project type<select name="type" required><option value="">Choose one</option><option>Digital product</option><option>Web experience</option><option>Commerce</option><option>Other</option></select></label>
            <label>Budget<select name="budget" required><option value="">Choose one</option><option>Under $25k</option><option>$25k–$75k</option><option>$75k+</option></select></label>
            <label class="fv-contact-page__message">What are you building?<textarea name="message" rows="5" required></textarea></label>
            <button class="fv-btn fv-btn--light" type="submit">Prepare email <span>→</span></button>
            <p class="fv-contact-page__notice" aria-live="polite"></p>
        </form>
    </div>
</section>
@endsection
