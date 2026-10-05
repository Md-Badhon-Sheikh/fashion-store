{{--
    Status chip (text + colour, never colour alone).
    <x-admin.status-chip status="processing" />                 => "Processing", violet
    <x-admin.status-chip status="low-stock" label="Low · 2 left" size="sm" />
    Tones come from App\Support\Status::tone(): amber, blue, violet, cyan, green, red, gray.
--}}
@props(['status', 'label' => null, 'size' => null])
@php($tone = \App\Support\Status::tone($status))
<span {{ $attributes->class(['chip', 'chip--'.$tone, 'chip--sm' => $size === 'sm', 'chip--lg' => $size === 'lg']) }}>{{ $label ?? \App\Support\Status::label($status) }}</span>
