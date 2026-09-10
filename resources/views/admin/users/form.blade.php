@php
    $self = $account->exists && $account->id === auth()->id();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-layout">
        <div>
            <x-admin.panel kicker="01" title="Account">
                <div class="admin-form-grid">
                    <x-admin.field name="name" label="Name" :value="old('name', $account->name)" required autofocus />
                    <x-admin.field name="email" label="Work email" type="email" :value="old('email', $account->email)" required />
                </div>
            </x-admin.panel>

            <x-admin.panel
                kicker="02"
                title="Password"
                :description="$account->exists
                    ? 'Leave both fields empty to keep the current password. Setting one here replaces it immediately.'
                    : 'At least twelve characters, with upper and lower case and a number.'"
            >
                <div class="admin-form-grid">
                    <x-admin.field
                        name="password"
                        label="{{ $account->exists ? 'New password' : 'Password' }}"
                        type="password"
                        autocomplete="new-password"
                        :required="! $account->exists"
                        :optional="$account->exists"
                    />
                    <x-admin.field
                        name="password_confirmation"
                        label="Confirm password"
                        type="password"
                        autocomplete="new-password"
                        :required="! $account->exists"
                        :optional="$account->exists"
                    />
                </div>
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            <x-admin.panel kicker="03" title="Access">
                @if ($self)
                    <x-admin.alert tone="info">
                        This is your own account. Role and state are fixed here — changing either would sign you
                        out of the workspace mid-request with no way back in.
                    </x-admin.alert>
                @endif

                @if ($self)
                    {{-- Submitted unchanged so validation still sees the
                         required fields; the controller pins them regardless. --}}
                    <input type="hidden" name="role" value="{{ $account->role->value }}">
                    <input type="hidden" name="is_active" value="1">

                    <div class="admin-field">
                        <span class="admin-field__label">Role</span>
                        <p><x-admin.badge tone="signal">{{ $account->role->label() }}</x-admin.badge></p>
                        <p class="admin-field__hint">{{ $account->role->description() }}</p>
                    </div>
                @else
                    <x-admin.field
                        name="role"
                        label="Role"
                        type="select"
                        :value="old('role', $account->role?->value ?? 'editor')"
                        :options="collect($roles)->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()"
                        :hint="collect($roles)->map(fn ($role) => $role->label().': '.$role->description())->implode(' ')"
                        required
                    />

                    <label class="admin-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->exists ? $account->is_active : true))>
                        <span class="admin-check__text">
                            <b>Can sign in</b>
                            <span>A suspended account is signed out on its next request and cannot sign back in.</span>
                        </span>
                    </label>
                @endif
            </x-admin.panel>

            @if ($account->exists)
                <x-admin.panel kicker="04" title="History">
                    <p class="admin-field__hint">
                        Added {{ $account->created_at?->format('j F Y') }}.<br>
                        Last signed in {{ $account->last_login_at?->format('j F Y, H:i') ?? 'never' }}.<br>
                        {{ $account->insights_count ?? 0 }} {{ Str::plural('journal entry', $account->insights_count ?? 0) }} authored.
                    </p>
                </x-admin.panel>
            @endif
        </aside>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.users.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
