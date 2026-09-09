<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Choose password — FORMIVA</title>@vite('resources/css/admin.css')</head>
<body class="admin-auth-body">
    <main class="admin-auth-card">
        <a class="admin-brand admin-brand-dark" href="{{ route('admin.login') }}"><span class="admin-brand-mark">F</span><span>FORMIVA <small>STAFF ACCESS</small></span></a>
        <p class="admin-kicker">Account recovery</p><h1>Choose a new password</h1>
        @if($errors->any())<div class="admin-alert admin-alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.reset-password.store') }}" class="admin-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Work email</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
            <label for="password">New password</label><input id="password" name="password" type="password" required autocomplete="new-password">
            <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            <button class="admin-button" type="submit">Update password</button>
        </form>
    </main>
</body>
</html>
