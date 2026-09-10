@extends('layouts.admin', [
    'title' => 'Users — FORMIVA',
    'breadcrumbs' => [['label' => 'Users']],
])

@section('content')

<x-admin.page-header
    kicker="System"
    title="Users"
    description="Who can sign in, and what they can reach once they have. Editors manage content; admins also hold site settings and these accounts."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.users.create') }}" icon="plus">New account</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.filters :active="$active" :query="$filters['q']" placeholder="Search name or email">
    <x-admin.filter-select
        name="role"
        label="Role"
        any="Any role"
        :value="$filters['role']"
        :options="collect($roles)->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()"
    />
    <x-admin.filter-select
        name="state"
        label="State"
        any="Any"
        :value="$filters['state']"
        :options="['active' => 'Active', 'inactive' => 'Suspended']"
    />
</x-admin.filters>

<div class="admin-panel">
    @if ($users->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <x-admin.sort column="name" :sort="$sort" :direction="$direction">Person</x-admin.sort>
                        <x-admin.sort column="role" :sort="$sort" :direction="$direction">Role</x-admin.sort>
                        <th scope="col">State</th>
                        <th scope="col">Authored</th>
                        <x-admin.sort column="signed_in" :sort="$sort" :direction="$direction" default="desc">Last signed in</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $account)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.users.edit', $account) }}">{{ $account->name }}</a>
                                <span class="admin-table__secondary">{{ $account->email }}</span>
                            </td>
                            <td data-label="Role">
                                <x-admin.badge :tone="$account->isAdmin() ? 'signal' : 'neutral'">
                                    {{ $account->role->label() }}
                                </x-admin.badge>
                            </td>
                            <td data-label="State">
                                <x-admin.badge :tone="$account->is_active ? 'positive' : 'warning'">
                                    {{ $account->is_active ? 'Active' : 'Suspended' }}
                                </x-admin.badge>
                            </td>
                            <td class="admin-table__num" data-label="Authored">{{ $account->insights_count }} {{ Str::plural('insight', $account->insights_count) }}</td>
                            <td class="admin-table__num" data-label="Last signed in">{{ $account->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.button href="{{ route('admin.users.edit', $account) }}" variant="ghost" size="sm">Edit</x-admin.button>

                                    @if ($account->id === auth()->id())
                                        <span class="admin-badge admin-badge--muted admin-badge--plain">You</span>
                                    @else
                                        <x-admin.delete
                                            :action="route('admin.users.destroy', $account)"
                                            :label="'Remove '.$account->name"
                                            title="Remove this account?"
                                            :confirm="$account->name.' will lose access immediately. Entries they authored are kept and simply lose their byline.'"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$users" label="accounts" />
    @else
        <x-admin.empty
            icon="search"
            title="No accounts match that"
            description="Adjust the search or filters to find the person you are looking for."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.users.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
