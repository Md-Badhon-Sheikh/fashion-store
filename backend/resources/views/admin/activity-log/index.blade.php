{{--
    Activity log — ActivityLog.dc.html.
    Alpine component `activityPage` (script at the bottom):
      type                 action-type quick filter (chips; '' = all), also set by the "Action type" select on Apply
      user, keyword        applied by the filter form (draft* hold the unapplied values)
      open[i]              expanded rows showing the before / after diff
    Phones (< 768px): each entry renders as a stacked card (activity-log.css).
--}}
@extends('admin.layouts.app')

@section('title', 'Activity log')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/activity-log.css') }}">
@endpush

@php
    $typeCounts = collect($entries)->countBy('action');
    $alpineConfig = [
        'entries' => array_map(fn ($e) => [
            'user' => $e['user'],
            'action' => $e['action'],
            'search' => mb_strtolower(implode(' ', [$e['id'], $e['target'], $e['details'], $e['ip'], $e['device'], $e['user']])),
        ], $entries),
    ];
@endphp

@section('content')
<div class="al" x-data="activityPage">

    <x-admin.page-header title="Activity log" subtitle="Every staff action in admin panel and POS · read-only · kept for 365 days">
        <x-slot:actions>
            <button type="button" class="btn btn--outline"><x-admin.icon name="download" :size="18" />Export CSV</button>
            <button type="button" class="btn btn--outline"><x-admin.icon name="download" :size="18" />Export PDF</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="kpi-grid">
        @foreach ($kpis as $k)
            <x-admin.kpi :label="$k['label']" :value="$k['value']" :sub="$k['sub']" :tone="$k['tone']" :sub-tone="$k['sub_tone']" />
        @endforeach
    </div>

    {{-- Filters --}}
    <section class="card card--stack al-filter" aria-label="Filter activity">
        <form class="form-row al-filter__form" @submit.prevent="apply()">
            <label class="field">User
                <select class="select" name="user" x-model="draftUser">
                    <option value="">All users</option>
                    @foreach ($users as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Action type
                <select class="select" name="action" x-model="draftType">
                    <option value="">All actions</option>
                    @foreach ($types as $t => $dot)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field al-field-date">From
                <input class="input" type="date" name="from" value="{{ $dateValue }}">
            </label>
            <label class="field al-field-date">To
                <input class="input" type="date" name="to" value="{{ $dateValue }}">
            </label>
            <label class="field al-field-wide">Target / keyword
                <input class="input" type="search" name="q" placeholder="Order no., SKU, IP" x-model="draftKeyword" autocomplete="off">
            </label>
            <button type="submit" class="btn btn--primary al-apply">Apply</button>
        </form>
        <div class="al-chips" role="group" aria-label="Quick filter by action type">
            <button type="button" class="al-chip" aria-pressed="true" :aria-pressed="(type === '').toString()" @click="pick('')">
                All <span class="al-chip__n">{{ count($entries) }}</span>
            </button>
            @foreach ($types as $t => $dot)
                <button type="button" class="al-chip" aria-pressed="false" :aria-pressed="(type === @js($t)).toString()" @click="pick(@js($t))">
                    {{ $t }} <span class="al-chip__n">{{ $typeCounts[$t] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Timeline --}}
    <section class="card" aria-labelledby="al-title">
        <div class="card__head">
            <h2 id="al-title" class="card__title">Timeline · {{ $dateLabel }}</h2>
            <span class="fs-13 muted" aria-live="polite">Showing <span x-text="shown">{{ count($entries) }}</span> events · newest first</span>
        </div>
        <div class="table-wrap">
            <table class="table al-table" style="--table-min: 1020px">
                <thead>
                    <tr>
                        <th scope="col">Time</th>
                        <th scope="col">User</th>
                        <th scope="col">Action</th>
                        <th scope="col">Target</th>
                        <th scope="col">Details</th>
                        <th scope="col">IP / device</th>
                        <th scope="col">Severity</th>
                        <th scope="col"><span class="visually-hidden">Expand</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $i => $e)
                        @php($isOpen = $i === 0)
                        <tr @class(['al-row', 'is-expanded' => $isOpen]) x-show="show({{ $i }})" :class="{ 'is-expanded': open[{{ $i }}] }">
                            <td class="al-c-time nowrap text-2">{{ $e['time'] }}</td>
                            <td class="al-c-user">
                                <span class="fw-700 al-user">{{ $e['user'] }}</span>
                                <span class="cell-sub">{{ $e['role'] }}</span>
                            </td>
                            <td class="al-c-action nowrap">
                                <span class="al-action"><span class="al-dot al-dot--{{ $types[$e['action']] }}" aria-hidden="true"></span>{{ $e['action'] }}</span>
                            </td>
                            <td class="al-c-target"><a class="al-target" href="{{ $e['href'] }}">{{ $e['target'] }}</a></td>
                            <td class="al-c-details text-2">{{ $e['details'] }}</td>
                            <td class="al-c-ip fs-13">
                                <span class="al-ip">{{ $e['ip'] }}</span>
                                <span class="muted al-device">{{ $e['device'] }}</span>
                            </td>
                            <td class="al-c-sev">
                                <span class="chip chip--{{ $severityTones[$e['severity']] }}">{{ ucfirst($e['severity']) }}</span>
                            </td>
                            <td class="al-c-toggle num">
                                <button type="button" class="icon-btn icon-btn--sm al-toggle"
                                    aria-controls="al-detail-{{ $i }}"
                                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}" :aria-expanded="open[{{ $i }}].toString()"
                                    aria-label="{{ $isOpen ? 'Hide' : 'Show' }} details for {{ $e['action'] }} {{ $e['target'] }}"
                                    :aria-label="(open[{{ $i }}] ? 'Hide' : 'Show') + @js(' details for '.$e['action'].' '.$e['target'])"
                                    @click="open[{{ $i }}] = !open[{{ $i }}]">
                                    <x-admin.icon name="chevron-down" :size="16" :stroke="2.2" />
                                </button>
                            </td>
                        </tr>
                        <tr id="al-detail-{{ $i }}" class="al-detail" x-show="show({{ $i }}) && open[{{ $i }}]" @unless ($isOpen) x-cloak @endunless>
                            <td colspan="8">
                                <div class="al-diff">
                                    <div class="al-diff__head">
                                        <span class="fw-700">Change details · before / after</span>
                                        <span class="muted">Event ID {{ $e['id'] }} · {{ $dateLabel }}, {{ $e['time'] }}</span>
                                    </div>
                                    <div class="table-wrap">
                                        <table class="table al-diff__table" style="--table-min: 560px">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="al-diff__field">Field</th>
                                                    <th scope="col">Before</th>
                                                    <th scope="col">After</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($e['diff'] as [$field, $before, $after])
                                                    <tr>
                                                        <th scope="row" class="al-diff__field">{{ $field }}</th>
                                                        <td><span class="al-before"><span aria-hidden="true">− </span><span class="visually-hidden">Before: </span>{{ $before }}</span></td>
                                                        <td><span class="al-after"><span aria-hidden="true">+ </span><span class="visually-hidden">After: </span>{{ $after }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="fs-12 muted">{{ $e['note'] }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div x-show="shown === 0" x-cloak>
            <x-admin.empty-state icon="clock" title="No activity" message="No activity of this type in the selected date range." />
        </div>
        <div class="table-footer al-foot">
            <span>Page {{ $pagination['page'] }} of {{ $pagination['pages'] }} · {{ $pagination['total'] }} events</span>
            <div class="cluster" style="--gap: 6px">
                <button type="button" class="btn btn--outline-strong btn--sm">Previous</button>
                <button type="button" class="btn btn--outline-strong btn--sm">Next</button>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const cfg = @js($alpineConfig);

        Alpine.data('activityPage', () => ({
            type: '',
            user: '',
            keyword: '',
            draftType: '',
            draftUser: '',
            draftKeyword: '',
            open: cfg.entries.map((e, i) => i === 0),

            pick(type) { this.type = type; this.draftType = type; },
            apply() {
                this.type = this.draftType;
                this.user = this.draftUser;
                this.keyword = this.draftKeyword.trim().toLowerCase();
            },
            show(i) {
                const e = cfg.entries[i];
                return (!this.type || e.action === this.type)
                    && (!this.user || e.user === this.user)
                    && (!this.keyword || e.search.includes(this.keyword));
            },
            get shown() { return cfg.entries.filter((e, i) => this.show(i)).length; },
        }));
    });
</script>
@endpush
