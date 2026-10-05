{{--
    Accessible on/off switch (button role="switch") with its own Alpine state + hidden form value.
    <x-admin.toggle name="cod_enabled" :checked="true" label="Cash on delivery" />
    <x-admin.toggle name="free_delivery" label="Free delivery over threshold" :show-label="false" />
    Props: name (hidden input name, optional), checked, label (required: accessible name),
           showLabel (render the label next to the switch, default true), hint (small text under label),
           disabled, model (bind to an outer Alpine expression instead of local state, e.g. model="form.cod").
--}}
@props(['name' => null, 'checked' => false, 'label', 'showLabel' => true, 'hint' => null, 'disabled' => false, 'model' => null])
@php
    $id = 'tg-'.\Illuminate\Support\Str::random(8);
    $state = $model ?: 'on';
@endphp
<span {{ $attributes->class(['switch-field']) }} @unless ($model) x-data="{ on: {{ $checked ? 'true' : 'false' }} }" @endunless>
    <button type="button" role="switch" class="switch"
        aria-checked="{{ $checked ? 'true' : 'false' }}" :aria-checked="({{ $state }}) ? 'true' : 'false'"
        @if ($showLabel) aria-labelledby="{{ $id }}" @else aria-label="{{ $label }}" @endif
        @if ($disabled) aria-disabled="true" @else x-on:click="{{ $state }} = !({{ $state }})" @endif></button>
    @if ($showLabel)
        <span class="switch-field__text">
            <span id="{{ $id }}">{{ $label }}</span>
            @if ($hint)<span class="switch-field__hint">{{ $hint }}</span>@endif
        </span>
    @endif
    @if ($name)
        <input type="hidden" name="{{ $name }}" value="{{ $checked ? 1 : 0 }}" :value="({{ $state }}) ? 1 : 0">
    @endif
</span>
