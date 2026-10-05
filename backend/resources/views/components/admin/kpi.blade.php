{{--
    KPI tile.
    Static:  <x-admin.kpi label="Total products" value="412" sub="1,986 size/colour variants" />
    Tone:    tone="default|warn|pending|danger|success" (colours the value)
    Range-aware (Alpine): pass arrays keyed by range and place the tile inside an x-data scope that
    defines `range`, e.g. x-data="{ range: 'today' }":
             <x-admin.kpi :label="$k['label']" :value="$k['value']" :sub="$k['sub']" />
    where each prop is either a string or ['today' => '…', 'd7' => '…', 'd30' => '…'].
    The first array entry is rendered on the server; Alpine swaps the text when `range` changes.
    Optional: rangeVar (default 'range'), subTone ('up' | 'down') to colour the sub line.
--}}
@props(['label', 'value', 'sub' => null, 'tone' => 'default', 'rangeVar' => 'range', 'subTone' => null])
@php
    $first = fn ($v) => is_array($v) ? reset($v) : $v;
    $bind = fn ($v) => is_array($v) ? \Illuminate\Support\Js::from($v)->toHtml().'['.$rangeVar.']' : null;
@endphp
<div {{ $attributes->class(['kpi', 'kpi--'.$tone => $tone !== 'default']) }}>
    <div class="kpi__label" @if (is_array($label)) x-text="{{ $bind($label) }}" @endif>{{ $first($label) }}</div>
    <div class="kpi__value" @if (is_array($value)) x-text="{{ $bind($value) }}" @endif>{{ $first($value) }}</div>
    @if ($sub !== null)
        <div @class(['kpi__sub', 'kpi__sub--'.$subTone => $subTone]) @if (is_array($sub)) x-text="{{ $bind($sub) }}" @endif>{{ $first($sub) }}</div>
    @endif
</div>
