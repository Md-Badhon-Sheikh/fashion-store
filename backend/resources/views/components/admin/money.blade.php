{{-- Formatted Taka amount with en-IN grouping. <x-admin.money :amount="214600" /> => ৳2,14,600 --}}
@props(['amount'])
<span {{ $attributes->class(['tabular']) }}>{{ \App\Support\DemoData::money($amount) }}</span>
