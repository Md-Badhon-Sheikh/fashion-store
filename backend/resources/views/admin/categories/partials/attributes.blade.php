{{--
    Attributes summary (sizes, colours, fabrics) — right column of Categories.dc.html,
    also used full-width on the "Attributes" tab.
    Vars: $attrs (CategoryData::attributes()), $headingId, $showManage (bool).
--}}
<section class="card card--stack cat-attrs" aria-labelledby="{{ $headingId }}" style="gap: 14px">
    <div class="card-title-row">
        <h2 id="{{ $headingId }}" class="card__title">Attributes</h2>
        @if ($showManage)
            <button type="button" class="link-btn fs-13" @click="tab = 'attributes'">Manage →</button>
        @endif
    </div>
    <div>
        <div class="section-label">Sizes · clothing</div>
        <ul class="size-chips" role="list">
            @foreach ($attrs['sizes'] as $s)
                <li class="size-chip size-chip--strong">{{ $s }}</li>
            @endforeach
        </ul>
        <div class="section-label cat-attrs__sub">Sizes · pants (waist) &amp; kids (age)</div>
        <ul class="size-chips" role="list">
            @foreach ($attrs['sizes2'] as $s)
                <li class="size-chip">{{ $s }}</li>
            @endforeach
        </ul>
    </div>
    <div>
        <div class="section-label">Colours</div>
        <ul class="colour-rows" role="list">
            @foreach ($attrs['colours'] as $c)
                <li class="colour-rows__item">
                    <span class="swatch swatch--sq" style="--s: 22px; --swatch: {{ $c['hex'] }}"></span>
                    <span class="colour-rows__name">{{ $c['name'] }}</span>
                    <span class="mono muted">{{ $c['hex'] }}</span>
                    <span class="colour-rows__count">{{ $c['n'] }} variants</span>
                </li>
            @endforeach
        </ul>
        <button type="button" class="pick-dashed">+ Add colour</button>
    </div>
    <div>
        <div class="section-label">Fabrics</div>
        <p class="cat-attrs__fabrics">{{ implode(' · ', $attrs['fabrics']) }}</p>
    </div>
</section>
