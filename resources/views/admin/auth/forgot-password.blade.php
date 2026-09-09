<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset password — FORMIVA</title>@vite('resources/css/admin.css')</head>
<body class="admin-auth-body">
    <main class="admin-auth-card">
        <a class="admin-brand admin-brand-dark" href="{{ route('admin.login') }}"><span class="admin-brand-mark">F</span><span>FORMIVA <small>STAFF ACCESS</small></span></a>
        <p class="admin-kicker">Account recovery</p><h1>Reset password</h1>
        <p class="admin-auth-intro">Enter your work email and we will send instructions if an account matches.</p>
        @if($errors->any())<div class="admin-alert admin-alert-error" role="alert">{{ $errors->first() }}</div>@endif
        @if(session('status'))<div class="admin-alert admin-alert-success" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('admin.forgot-password.store') }}" class="admin-form">
            @csrf
            <label for="email">Work email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            <button class="admin-button" type="submit">Send reset link</button>
        </form>
        <a class="admin-muted-link" href="{{ route('admin.login') }}">Return to sign in</a>
    </main>
</body>
</html>
