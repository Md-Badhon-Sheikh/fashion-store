{{-- Phone bottom tab bar (< 640px), from M-AdminDashboard / M-AdminOrders. "More" opens the sidebar drawer. --}}
<nav class="tabbar" aria-label="Admin sections">
    @foreach (\App\Support\AdminNav::tabbar() as $tab)
        @php($on = request()->routeIs($tab['active']))
        <a href="{{ route($tab['route']) }}" class="tabbar__item" @if ($on) aria-current="page" @endif>
            <span class="tabbar__icon">
                <x-admin.icon :name="$tab['icon']" :size="22" :stroke="$on ? 1.9 : 1.8" />
                @if ($tab['badge'])
                    <span class="tabbar__badge">{{ $tab['badge'] }}<span class="visually-hidden"> new</span></span>
                @endif
            </span>
            {{ $tab['label'] }}
        </a>
    @endforeach
    <button type="button" class="tabbar__item" @click="openDrawer()" aria-controls="admin-sidebar">
        <span class="tabbar__icon"><x-admin.icon name="more" :size="22" /></span>
        More
    </button>
</nav>
