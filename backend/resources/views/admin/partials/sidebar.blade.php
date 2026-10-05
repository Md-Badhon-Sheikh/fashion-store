{{--
    Sidebar navigation (Dashboard.dc.html on desktop, M-AdminMenu.dc.html as the < 1024px drawer).
    Groups/links/badges come from App\Support\AdminNav::groups(); active state uses request()->routeIs().
    Blocks marked .sidebar__drawer-only (profile, menu search, footer) only show in the drawer.
--}}
@php
    $navGroups = \App\Support\AdminNav::groups();
    $user = \App\Support\DemoData::currentUser();
@endphp
<aside id="admin-sidebar" class="sidebar" aria-label="Admin menu">
    <div class="sidebar__head">
        <a href="{{ route('admin.dashboard') }}" class="brand-mark" aria-label="YOUR BRAND Admin, dashboard">
            <span class="brand-mark__name">YOUR BRAND</span><span class="brand-mark__tag">Admin</span>
        </a>
        <button type="button" class="sidebar__close" x-ref="drawerClose" @click="closeDrawer()" aria-label="Close menu">
            <x-admin.icon name="close" :size="22" :stroke="2" />
        </button>
    </div>

    <div class="sidebar__top sidebar__drawer-only">
        <div class="profile-card">
            <div class="profile-card__who">
                <span class="avatar avatar--lg">{{ $user['initials'] }}</span>
                <div class="profile-card__text">
                    <div class="profile-card__name">{{ $user['name'] }}</div>
                    <div class="profile-card__role">{{ $user['role'] }} · {{ $user['permissions'] }}</div>
                </div>
            </div>
            <div class="profile-card__tags">
                <span class="pill-mint">{{ $user['channel'] }}</span>
                <span class="pill-outline-dark">{{ $user['counter'] }}</span>
            </div>
            <a class="profile-card__link" href="{{ route('admin.settings') }}">My profile &amp; password<x-admin.icon name="chevron-right" :size="18" :stroke="2" /></a>
        </div>

        <div class="menu-search">
            <x-admin.icon name="search" :size="18" :stroke="2" />
            <label for="menu-search" class="visually-hidden">Search menu</label>
            <input id="menu-search" type="search" placeholder="Search menu" x-model="navq" autocomplete="off">
        </div>
    </div>

    <div class="sidebar__groups">
        @foreach ($navGroups as $group)
            @php($groupNames = array_map(fn ($i) => mb_strtolower($i['label']), $group['items']))
            <nav class="nav-group" aria-label="{{ $group['label'] }}"
                x-show="!navq || {{ \Illuminate\Support\Js::from($groupNames) }}.some(n => n.includes(navq.toLowerCase()))">
                <div class="nav-group__label">{{ $group['label'] }}</div>
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}"
                        @class(['nav-link', 'is-active' => $item['is_active']])
                        @if ($item['is_active']) aria-current="page" @endif
                        x-show="!navq || {{ \Illuminate\Support\Js::from(mb_strtolower($item['label'])) }}.includes(navq.toLowerCase())">
                        <x-admin.icon :name="$item['icon']" class="nav-link__icon" />
                        <span class="nav-link__label">{{ $item['label'] }}</span>
                        @if ($item['badge'])
                            <span class="nav-badge"><span class="visually-hidden">(</span>{{ $item['badge'] }}<span class="visually-hidden"> new)</span></span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endforeach
    </div>

    <div class="sidebar__foot sidebar__drawer-only">
        <a class="sidebar__foot-link" href="{{ \App\Support\DemoData::storeUrl() }}" target="_blank" rel="noopener">
            <x-admin.icon name="external" />View online store
        </a>
        <a class="btn-logout" href="{{ route('admin.login') }}">
            <x-admin.icon name="logout" :stroke="1.9" />Logout
        </a>
        <div class="sidebar__meta">{{ $user['last_login'] }} · {{ $user['version'] }}</div>
    </div>
</aside>
