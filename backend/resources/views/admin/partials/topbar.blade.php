{{--
    Topbar. Desktop: global search, Scan barcode, Open POS, notifications, user.
    < 1024px: adds the hamburger that opens the sidebar drawer.
    < 640px (M-AdminDashboard): hamburger + page title + bell + avatar; pages can replace
    the right-hand icons with @section('mobile_actions').
--}}
@php
    $user = \App\Support\DemoData::currentUser();
    $unread = \App\Support\DemoData::navBadges()['notifications'] ?? 0;
@endphp
<header class="topbar">
    <button type="button" class="icon-btn icon-btn--ghost topbar__menu" @click="openDrawer()"
        aria-label="Open menu" aria-controls="admin-sidebar" :aria-expanded="drawer.toString()" aria-expanded="false">
        <x-admin.icon name="menu" :size="22" :stroke="2" />
    </button>

    <div class="topbar__title" aria-hidden="true">@yield('title', 'Admin')</div>

    <form class="topbar__search" role="search" action="{{ route('admin.orders.index') }}" method="get">
        <x-admin.icon name="search" :size="18" :stroke="2" />
        <label for="admin-search" class="visually-hidden">Search</label>
        <input id="admin-search" name="q" type="search" placeholder="Search product, SKU / barcode, order no., customer phone" autocomplete="off">
    </form>

    <div class="topbar__actions">
        <button type="button" class="btn btn--outline topbar__scan">
            <x-admin.icon name="barcode" :size="18" />Scan barcode
        </button>
        <a class="btn btn--brand topbar__pos" href="{{ route('admin.pos') }}">Open POS</a>

        @hasSection('mobile_actions')
            <span class="show-sm cluster" style="--gap: 4px">@yield('mobile_actions')</span>
        @endif

        <a href="{{ route('admin.notifications') }}" @class(['icon-btn', 'hide-sm' => View::hasSection('mobile_actions')])
            aria-label="Notifications, {{ $unread }} new">
            <x-admin.icon name="bell" :size="20" />
            @if ($unread)<span class="bell-dot" aria-hidden="true"></span>@endif
        </a>

        <a href="{{ route('admin.settings') }}" @class(['topbar__user', 'hide-sm' => View::hasSection('mobile_actions')])
            aria-label="Account: {{ $user['name'] }}, {{ $user['role'] }}">
            <span class="avatar" aria-hidden="true">{{ $user['initials'] }}</span>
            <span class="topbar__user-text" aria-hidden="true">
                <span class="topbar__user-name">{{ $user['name'] }}</span>
                <span class="topbar__user-role">{{ $user['role'] }}</span>
            </span>
        </a>
    </div>
</header>
