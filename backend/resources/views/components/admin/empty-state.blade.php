{{--
    Empty state.
    <x-admin.empty-state icon="orders" title="No orders" message="No orders with this status in the selected date range." />
    Slot content (e.g. a button) renders under the message.
--}}
@props(['title' => null, 'message' => null, 'icon' => null])
<div {{ $attributes->class(['empty-state']) }}>
    @if ($icon)
        <span class="empty-state__icon"><x-admin.icon :name="$icon" :size="26" /></span>
    @endif
    @if ($title)
        <div class="empty-state__title">{{ $title }}</div>
    @endif
    @if ($message)
        <div>{{ $message }}</div>
    @endif
    {{ $slot }}
</div>
