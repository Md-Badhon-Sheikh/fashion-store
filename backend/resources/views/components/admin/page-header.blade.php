{{--
    Page header: h1 + subtitle + actions on the right.
    <x-admin.page-header title="Orders" subtitle="Website, Facebook, phone and POS orders in one list">
        <x-slot:actions><button type="button" class="btn btn--outline">Export</button></x-slot:actions>
    </x-admin.page-header>
    Optional slots:
        breadcrumb  -> rendered above the header:  <a href="…">Orders</a><span>/</span><span>#WB-10482</span>
        meta        -> chips rendered next to the h1 (outside the heading text):  <x-admin.status-chip status="processing" />
        default     -> extra content under the subtitle
    On phones (< 640px) the h1 is visually hidden because the topbar already shows the page
    title (@section('title')). Pass :phone-title="true" to keep it visible (pages with a meta slot keep it).
--}}
@props(['title', 'subtitle' => null, 'phoneTitle' => false])
@isset($breadcrumb)
    <nav class="breadcrumb" aria-label="Breadcrumb">{{ $breadcrumb }}</nav>
@endisset
<div {{ $attributes->class(['page-header']) }}>
    <div class="page-header__text">
        @isset($meta)
            <div class="page-header__titlebar">
                <h1 class="page-header__title">{{ $title }}</h1>
                {{ $meta }}
            </div>
        @else
            <h1 @class(['page-header__title', 'page-header__title--topbar' => ! $phoneTitle])>{{ $title }}</h1>
        @endisset
        @if ($subtitle)
            <div class="page-header__subtitle">{{ $subtitle }}</div>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="page-header__actions">{{ $actions }}</div>
    @endisset
</div>
