{{--
    Tab strip bound to an Alpine variable of the surrounding x-data.
    <div x-data="{ tab: 'all' }">
        <x-admin.tabs model="tab" label="Order status" :tabs="[
            'all' => ['label' => 'All', 'count' => '1,412'],
            'pending' => ['label' => 'Pending', 'count' => 12],
            'hidden' => 'Hidden (4)',
        ]" />
        <div x-show="tab === 'all'">…</div>
    </div>
    Props: tabs (key => label string | ['label' => …, 'count' => …]), model (Alpine var, default 'tab'),
           label (aria-label of the tablist), active (initial key rendered by the server, default first),
           variant ('line' = underlined desktop tabs | 'pill' = scrolling chip row used on phones).
--}}
@props(['tabs' => [], 'model' => 'tab', 'label' => 'Tabs', 'active' => null, 'variant' => 'line'])
@php($active = (string) ($active ?? array_key_first($tabs)))
<div role="tablist" aria-label="{{ $label }}" {{ $attributes->class([$variant === 'pill' ? 'pill-tabs' : 'tabs']) }}>
    @foreach ($tabs as $key => $tab)
        @php($t = is_array($tab) ? $tab : ['label' => $tab])
        <button type="button" role="tab" class="{{ $variant === 'pill' ? 'pill-tab' : 'tab' }}"
            aria-selected="{{ (string) $key === $active ? 'true' : 'false' }}"
            x-bind:aria-selected="({{ $model }} === {{ \Illuminate\Support\Js::from((string) $key) }}).toString()"
            x-on:click="{{ $model }} = {{ \Illuminate\Support\Js::from((string) $key) }}">
            {{ $t['label'] }}
            @isset($t['count'])
                <span class="{{ $variant === 'pill' ? 'pill-tab__count' : 'tab__count' }}">{{ $t['count'] }}</span>
            @endisset
        </button>
    @endforeach
</div>
