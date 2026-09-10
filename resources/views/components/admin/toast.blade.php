@props(['tone' => 'info', 'message'])

<div class="admin-toast admin-toast--{{ $tone }}" role="status" data-toast>
    <x-admin.icon :name="$tone === 'success' ? 'check' : ($tone === 'error' ? 'alert' : 'info')" />
    <span>{{ $message }}</span>
    <button type="button" class="admin-toast__close" data-toast-close aria-label="Dismiss notification">×</button>
</div>
