@extends('layouts.admin', [
    'title' => $account->name.' — Users — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => $account->name],
    ],
])

@section('content')

<x-admin.page-header kicker="System" :title="$account->name" :description="$account->email">
    <x-slot:actions>
        @if ($account->id !== auth()->id())
            <x-admin.delete
                :action="route('admin.users.destroy', $account)"
                variant="danger"
                label="Remove account"
                title="Remove this account?"
                :confirm="$account->name.' will lose access immediately. Entries they authored are kept and simply lose their byline.'"
            />
        @endif
    </x-slot:actions>
</x-admin.page-header>

@include('admin.users.form', [
    'action' => route('admin.users.update', $account),
    'method' => 'PUT',
    'submit' => 'Save account',
])

@endsection
