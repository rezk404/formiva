@extends('layouts.admin', ['title' => 'Profile — FORMIVA', 'header' => 'Profile'])

@section('content')
<div class="admin-page-intro"><p class="admin-kicker">Account</p><h1>Your profile</h1><p>Keep your staff details and sign-in credentials current.</p></div>
<div class="admin-form-grid">
    <section class="admin-panel"><p class="admin-kicker">Details</p><h2>Profile information</h2><form method="POST" action="{{ route('admin.profile.update') }}" class="admin-form">@csrf @method('PUT')<label for="name">Name</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required><label for="email">Work email</label><input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required><button class="admin-button" type="submit">Save details</button></form></section>
    <section class="admin-panel"><p class="admin-kicker">Security</p><h2>Change password</h2><form method="POST" action="{{ route('admin.profile.password') }}" class="admin-form">@csrf @method('PUT')<label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password"><label for="password">New password</label><input id="password" name="password" type="password" required autocomplete="new-password"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"><button class="admin-button" type="submit">Update password</button></form></section>
</div>
@endsection
