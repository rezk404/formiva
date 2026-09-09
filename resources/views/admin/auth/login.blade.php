<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in — FORMIVA</title>@vite('resources/css/admin.css')</head>
<body class="admin-auth-body">
    <main class="admin-auth-card">
        <a class="admin-brand admin-brand-dark" href="{{ url('/') }}"><span class="admin-brand-mark">F</span><span>FORMIVA <small>STAFF ACCESS</small></span></a>
        <p class="admin-kicker">Internal workspace</p>
        <h1>Sign in</h1>
        <p class="admin-auth-intro">Access is limited to the FORMIVA team.</p>
        @if($errors->any())<div class="admin-alert admin-alert-error" role="alert">{{ $errors->first() }}</div>@endif
        @if(session('status'))<div class="admin-alert admin-alert-success" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form">
            @csrf
            <label for="email">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            <label class="admin-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> <span>Keep me signed in</span></label>
            <button class="admin-button" type="submit">Sign in</button>
        </form>
        <a class="admin-muted-link" href="{{ route('admin.forgot-password') }}">Forgot your password?</a>
    </main>
</body>
</html>
