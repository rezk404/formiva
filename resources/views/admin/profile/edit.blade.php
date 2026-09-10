@extends('layouts.admin', [
    'title' => 'Profile — FORMIVA',
    'breadcrumbs' => [['label' => 'Profile']],
])

@section('content')

<x-admin.page-header
    kicker="System"
    title="Your profile"
    description="Keep your staff details and sign-in credentials current. Changing your password signs out every other session you have open."
/>

<div class="admin-grid admin-grid--2">
    <x-admin.panel kicker="Account" title="Profile information">
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')

            <x-admin.field name="name" label="Name" :value="old('name', auth()->user()->name)" required />
            <x-admin.field name="email" label="Work email" type="email" :value="old('email', auth()->user()->email)" required />

            <x-admin.button class="admin-form-submit">Save details</x-admin.button>
        </form>
    </x-admin.panel>

    <x-admin.panel kicker="Security" title="Change password">
        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf
            @method('PUT')

            <x-admin.field
                name="current_password"
                label="Current password"
                type="password"
                autocomplete="current-password"
                required
            />

            <x-admin.field
                name="password"
                label="New password"
                type="password"
                autocomplete="new-password"
                hint="At least twelve characters, with upper and lower case and a number."
                required
            />

            <x-admin.field
                name="password_confirmation"
                label="Confirm new password"
                type="password"
                autocomplete="new-password"
                required
            />

            <x-admin.button class="admin-form-submit">Update password</x-admin.button>
        </form>
    </x-admin.panel>
</div>

<x-admin.panel kicker="Access" title="What this account can reach">
    <p class="admin-field__hint">
        You are signed in as <strong>{{ auth()->user()->role->label() }}</strong>.
        {{ auth()->user()->role->description() }}
    </p>
</x-admin.panel>

@endsection
