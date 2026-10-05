{{--
    Vertical (stacked) bar chart built from CSS — tooltips via the title attribute.
    @include('admin.partials.bar-chart', [
        'bars' => [                                         // left → right
            ['x' => '22 Sep', 'tip' => '22 Sep · Online ৳25k · POS ৳14k', 'value' => '',  // value = label above the bar (optional)
             'segments' => [['series' => 'online', 'value' => 25], ['series' => 'pos', 'value' => 14]]], // bottom → top
        ],
        'max' => 60,                                        // value at the top gridline
        'ticks' => ['60k', '40k', '20k', '0'],              // y-axis labels, top → bottom (evenly spaced)
        'height' => 210,                                    // plot height in px
        'minWidth' => 520,                                  // horizontal scroll below this width (0 = none)
        'barMax' => 28, 'gap' => 10,                        // optional bar max width & gap in px
        'label' => 'Daily revenue by channel',              // accessible name of the chart
    ])
    Series classes: online (blue), pos (orange), third (green), brand (single-measure green).
--}}
@php
    $ticks = $ticks ?? [];
    $height = $height ?? 210;
    $minWidth = $minWidth ?? 0;
    $barMax = $barMax ?? 28;
    $gap = $gap ?? 10;
    $max = max($max ?? 1, 0.0001);
    $lines = max(count($ticks) - 1, 1);
@endphp
<div class="chart chart__scroll" role="img" aria-label="{{ $label ?? 'Bar chart' }}">
    <div class="chart__body" style="--chart-h: {{ $height }}px; --chart-min: {{ $minWidth }}px; --bar-max: {{ $barMax }}px; --bar-gap: {{ $gap }}px">
        <div class="chart__y" aria-hidden="true">
            @foreach ($ticks as $tick)<span>{{ $tick }}</span>@endforeach
        </div>
        <div class="chart__main">
            <div class="chart__plot">
                @for ($i = 0; $i < $lines; $i++)
                    <div class="chart__gridline" style="top: {{ round($i / $lines * 100, 3) }}%"></div>
                @endfor
                <div class="chart__gridline chart__gridline--axis" style="bottom: 0"></div>
                <div class="chart__bars">
                    @foreach ($bars as $bar)
                        <div class="chart__bar" title="{{ $bar['tip'] ?? '' }}">
                            @if (! empty($bar['value']))<span class="chart__value">{{ $bar['value'] }}</span>@endif
                            @foreach (array_reverse($bar['segments']) as $seg)
                                <div class="chart__seg chart__seg--{{ $seg['series'] ?? 'brand' }}" style="height: {{ round(min($seg['value'] / $max, 1) * 100, 3) }}%"></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="chart__x" aria-hidden="true">
                @foreach ($bars as $bar)
                    <span @class(['is-current' => ! empty($bar['current'])])>{{ $bar['x'] ?? '' }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>
