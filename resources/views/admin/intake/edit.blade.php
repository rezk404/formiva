@extends('layouts.admin', [
    'title' => $option->label.' — Intake options — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Intake options', 'url' => route('admin.intake.index')],
        ['label' => $option->label],
    ],
])

@section('content')

<x-admin.page-header kicker="Configuration" :title="$option->label" :description="$option->kind->step()">
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.intake.destroy', $option)"
            variant="danger"
            label="Delete option"
            title="Delete this option?"
            :confirm="'“'.$option->label.'” will no longer be offered. Briefs already submitted keep the answer as recorded. Withdrawing it instead keeps the record.'"
        />
    </x-slot:actions>
</x-admin.page-header>

@include('admin.intake.form', [
    'action' => route('admin.intake.update', $option),
    'method' => 'PUT',
    'submit' => 'Save option',
])

@endsection
