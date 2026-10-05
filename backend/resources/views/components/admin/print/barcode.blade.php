{{--
    Printed barcode strip + human-readable value (Invoice.dc.html ".bc" pattern).
    <x-admin.print.barcode value="WB-10482" size="lg|md|sm" />
    Visual stand-in: swap the strip for a real Code 128 renderer when wiring printing.
--}}
@props(['value', 'size' => 'lg'])
<div {{ $attributes->class(['doc-barcode', 'doc-barcode--'.$size]) }}>
    <div class="doc-barcode__bars" aria-hidden="true"></div>
    <div class="doc-barcode__value">{{ $value }}</div>
</div>
