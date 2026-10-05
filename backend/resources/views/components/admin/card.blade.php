{{--
    Card.
    <x-admin.card title="Stock alerts" subtitle="…" :link="route('admin.stock-in')" link-label="Stock-in →">
        …body…
    </x-admin.card>
    With custom header actions instead of a link:
    <x-admin.card title="Notifications sent">
        <x-slot:action><button type="button" class="btn btn--sm btn--outline-strong">Resend SMS</button></x-slot:action>
        …
    </x-admin.card>
    Props: title, subtitle, link + linkLabel, as (section|div|article), headingLevel (default 2),
           flush (no padding, e.g. a table touching the edges). Extra attributes/classes merge onto the root.
--}}
@props(['title' => null, 'subtitle' => null, 'link' => null, 'linkLabel' => null, 'as' => 'section', 'headingLevel' => 2, 'flush' => false])
<{{ $as }} {{ $attributes->class(['card', 'card--flush' => $flush]) }}>
    @if ($title || isset($action) || $link)
        <div class="card__head">
            <div>
                @if ($title)
                    <h{{ $headingLevel }} class="card__title">{{ $title }}</h{{ $headingLevel }}>
                @endif
                @if ($subtitle)
                    <div class="card__subtitle">{{ $subtitle }}</div>
                @endif
            </div>
            @isset($action)
                <div class="card__actions">{{ $action }}</div>
            @else
                @if ($link)
                    <a class="card__link" href="{{ $link }}">{{ $linkLabel }}</a>
                @endif
            @endisset
        </div>
    @endif
    {{ $slot }}
</{{ $as }}>
