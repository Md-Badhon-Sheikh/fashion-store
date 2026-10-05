{{--
    Users & roles — Users.dc.html.
    Alpine component `usersPage` (script at the bottom):
      q, roleFilter        staff table search + role filter
      active[id]           account active switch per user
      currentRole          role card selected → permission matrix + special permissions shown for it
      perms[role][m][a]    module × action grants (edited per role, "Reset" restores the defaults)
      special[role][i]     POS & sales special permissions
      invite / newInvites  invite form state and invites sent in this session
    Phones (< 640px): the staff table turns into stacked cards (users.css).
--}}
@extends('admin.layouts.app')

@section('title', 'Users & roles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/users.css') }}">
@endpush

@php
    $roleChip = fn (string $role) => 'chip us-chip--'.($roleTones[$role] ?? 'gray');
    $permCount = collect($permissions[$activeRole])->flatten()->filter()->count();
    $permTotal = count($modules) * count($actions);

    $alpineConfig = [
        'users' => array_map(fn ($u) => ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'phone' => $u['phone'], 'role' => $u['role'], 'active' => $u['active']], $users),
        'role' => $activeRole,
        'perms' => $permissions,
        'special' => $specialDefaults,
        'roles' => array_column($roles, 'name'),
        'branch' => $branches[0],
    ];
@endphp

@section('content')
<div class="us" x-data="usersPage">

    <x-admin.page-header title="Users & roles"
        subtitle="{{ count($users) }} staff accounts · {{ count($roles) }} roles · role-based permissions for admin panel and POS">
        <x-slot:actions>
            <a class="btn btn--outline" href="{{ route('admin.activity-log') }}">View activity log</a>
            <a class="btn btn--brand" href="#invite" @click.prevent="openInvite()"><x-admin.icon name="plus" :size="18" />Invite user</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Staff users --}}
    <section class="card" aria-labelledby="us-staff-title">
        <div class="card__head us-head">
            <h2 id="us-staff-title" class="card__title">Staff users</h2>
            <div class="cluster us-filters">
                <label for="us-q" class="visually-hidden">Search users</label>
                <input id="us-q" class="input input--sm us-search" type="search" placeholder="Search name or phone" x-model="q" autocomplete="off">
                <label for="us-role" class="visually-hidden">Filter by role</label>
                <select id="us-role" class="select select--sm us-role-filter" x-model="roleFilter">
                    <option value="">All roles</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r['name'] }}">{{ $r['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table us-table" style="--table-min: 860px">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Last login</th>
                        <th scope="col">Status</th>
                        <th scope="col">2FA</th>
                        <th scope="col" class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr x-show="showUser({{ $u['id'] }})">
                            <td class="us-c-user">
                                <div class="us-user">
                                    <span class="avatar us-avatar us-avatar--{{ $u['avatar'] }}" aria-hidden="true">{{ $u['initials'] }}</span>
                                    <span class="us-user__text">
                                        <span class="us-user__name">{{ $u['name'] }}</span>
                                        <span class="cell-sub">{{ $u['email'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="us-c-role"><span class="{{ $roleChip($u['role']) }}">{{ $u['role'] }}</span></td>
                            <td class="us-c-phone nowrap"><span class="us-label">Phone </span>{{ $u['phone'] }}</td>
                            <td class="us-c-last">
                                <span class="us-label">Last login </span>{{ $u['last_login'] }}
                                <span class="cell-sub">{{ $u['where'] }}</span>
                            </td>
                            <td class="us-c-status">
                                <div class="us-status">
                                    <button type="button" role="switch" class="switch"
                                        aria-label="Account active: {{ $u['name'] }}"
                                        aria-checked="{{ $u['active'] ? 'true' : 'false' }}"
                                        :aria-checked="active[{{ $u['id'] }}].toString()"
                                        @click="active[{{ $u['id'] }}] = !active[{{ $u['id'] }}]"></button>
                                    <span @class(['chip', 'chip--green' => $u['active'], 'chip--gray' => ! $u['active']])
                                        :class="{ 'chip--green': active[{{ $u['id'] }}], 'chip--gray': !active[{{ $u['id'] }}] }"
                                        x-text="active[{{ $u['id'] }}] ? 'Active' : 'Inactive'">{{ $u['active'] ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </td>
                            <td class="us-c-tfa">
                                <span class="us-label">2FA </span><span @class(['fs-12 fw-700', 'text-success' => $u['two_factor'], 'us-off' => ! $u['two_factor']])>{{ $u['two_factor'] ? 'On' : 'Off' }}</span>
                            </td>
                            <td class="us-c-actions num">
                                <div class="us-actions">
                                    <button type="button" class="btn btn--outline-strong btn--xs">Edit</button>
                                    <button type="button" class="btn btn--outline-strong btn--xs">Reset password</button>
                                    <button type="button" class="icon-btn icon-btn--sm" aria-label="More actions for {{ $u['name'] }}"><x-admin.icon name="more" :size="16" /></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div x-show="visibleUsers === 0" x-cloak>
            <x-admin.empty-state icon="user" title="No users found" message="No staff account matches this search or role." />
        </div>
    </section>

    {{-- Roles --}}
    <section class="stack stack--md" aria-labelledby="us-roles-title">
        <div class="split split--baseline">
            <h2 id="us-roles-title" class="card__title">Roles</h2>
            <button type="button" class="btn btn--outline-strong btn--sm us-new-role">+ New role</button>
        </div>
        <div class="grid-auto us-roles">
            @foreach ($roles as $r)
                <button type="button" class="us-role" aria-pressed="{{ $r['name'] === $activeRole ? 'true' : 'false' }}"
                    :aria-pressed="(currentRole === @js($r['name'])).toString()" @click="pickRole(@js($r['name']))"
                    aria-controls="us-perms">
                    <span class="us-role__top">
                        <span class="us-role__name">{{ $r['name'] }}</span>
                        <span class="{{ $roleChip($r['name']) }} chip--sm">{{ $r['members'] }} {{ $r['members'] === 1 ? 'member' : 'members' }}</span>
                    </span>
                    <span class="us-role__desc">{{ $r['description'] }}</span>
                    <span class="us-role__cta" x-text="currentRole === @js($r['name']) ? 'Editing permissions below' : 'Edit permissions →'">{{ $r['name'] === $activeRole ? 'Editing permissions below' : 'Edit permissions →' }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <div class="row-wrap">
        {{-- Permission matrix --}}
        <section id="us-perms" class="card card--stack col-main" aria-labelledby="us-perms-title">
            <div class="split split--baseline">
                <div>
                    <h2 id="us-perms-title" class="card__title">Permissions: <span x-text="currentRole">{{ $activeRole }}</span></h2>
                    <div class="card__subtitle" aria-live="polite"><span x-text="permCount">{{ $permCount }}</span> of {{ $permTotal }} module permissions granted · changes apply at next login</div>
                </div>
                <div class="cluster">
                    <button type="button" class="btn btn--outline-strong" @click="resetPerms()">Reset</button>
                    <button type="button" class="btn btn--brand">Save permissions</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table us-matrix" style="--table-min: 560px">
                    <thead>
                        <tr>
                            <th scope="col">Module</th>
                            @foreach ($actions as $a)
                                <th scope="col" class="text-center">{{ $a }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $mi => $m)
                            <tr>
                                <th scope="row" class="us-matrix__module">{{ $m }}</th>
                                @foreach ($actions as $ai => $a)
                                    @php($granted = $permissions[$activeRole][$mi][$ai])
                                    <td class="text-center">
                                        <button type="button" role="checkbox" class="us-check"
                                            aria-label="{{ $m }}: {{ $a }}"
                                            aria-checked="{{ $granted ? 'true' : 'false' }}"
                                            :aria-checked="perms[currentRole][{{ $mi }}][{{ $ai }}].toString()"
                                            @click="togglePerm({{ $mi }}, {{ $ai }})">
                                            <span class="us-check__box"><x-admin.icon name="check" :size="14" :stroke="3.2" /></span>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="us-special">
                <h3 class="us-special__title">Special permissions (POS &amp; sales)</h3>
                @foreach ($special as $i => $sp)
                    <div class="us-special__row">
                        <div>
                            <div class="fw-600 fs-14" id="us-sp-{{ $i }}">{{ $sp['name'] }}</div>
                            <div class="fs-12 muted">{{ $sp['note'] }}</div>
                        </div>
                        <button type="button" role="switch" class="switch" aria-labelledby="us-sp-{{ $i }}"
                            aria-checked="{{ $specialDefaults[$activeRole][$i] ? 'true' : 'false' }}"
                            :aria-checked="special[currentRole][{{ $i }}].toString()"
                            @click="special[currentRole][{{ $i }}] = !special[currentRole][{{ $i }}]"></button>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Invite --}}
        <section id="invite" class="card col-side us-invite" aria-labelledby="us-invite-title">
            <form class="stack stack--md" @submit.prevent="sendInvite()">
                <h2 id="us-invite-title" class="card__title">Invite user</h2>
                <p class="fs-13 muted">They get a link to set their own password. Link expires in 48 hours.</p>
                <label class="field">Display name
                    <input class="input" type="text" name="name" placeholder="e.g. Sales Staff 05" required x-ref="inviteName" x-model="invite.name" autocomplete="off">
                </label>
                <label class="field">Mobile number
                    <input class="input" type="tel" name="phone" inputmode="tel" placeholder="01XXXXXXXXX" required
                        pattern="01[3-9][0-9]{8}" title="11-digit mobile number starting with 01" x-model="invite.phone" autocomplete="off">
                </label>
                <label class="field">Email (optional)
                    <input class="input" type="email" name="email" placeholder="name@[DOMAIN]" x-model="invite.email" autocomplete="off">
                </label>
                <label class="field">Role
                    <select class="select" name="role" x-model="invite.role">
                        @foreach (['Sales Staff', 'Inventory Staff', 'Manager', 'Admin'] as $opt)
                            <option>{{ $opt }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">Branch / counter
                    <select class="select" name="branch" x-model="invite.branch">
                        @foreach ($branches as $b)
                            <option>{{ $b }}</option>
                        @endforeach
                    </select>
                </label>
                <fieldset class="us-fieldset">
                    <legend class="field__label">Send invite by</legend>
                    <label class="check us-check-row"><input type="checkbox" name="by_sms" checked x-model="invite.sms">SMS</label>
                    <label class="check us-check-row"><input type="checkbox" name="by_email" x-model="invite.email_invite">Email</label>
                    <label class="check us-check-row"><input type="checkbox" name="require_2fa" checked x-model="invite.twofa">Require 2FA on first login</label>
                </fieldset>
                <button type="submit" class="btn btn--brand btn--block">Send invite</button>
                <p class="alert alert--success" x-show="sent" x-cloak role="status" x-text="sent"></p>

                <div class="us-pending">
                    <div class="fw-700 us-pending__title">Pending invites</div>
                    @foreach ($invites as $inv)
                        <div class="us-pending__row"><span>{{ $inv['name'] }} · {{ $inv['phone'] }}</span><x-admin.status-chip status="pending" size="sm" /></div>
                    @endforeach
                    <template x-for="(inv, i) in newInvites" :key="i">
                        <div class="us-pending__row"><span x-text="inv.name + ' · ' + inv.phone"></span><span class="chip chip--amber chip--sm">Pending</span></div>
                    </template>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const cfg = @js($alpineConfig);
        const clone = (value) => JSON.parse(JSON.stringify(value));

        Alpine.data('usersPage', () => ({
            q: '',
            roleFilter: '',
            active: Object.fromEntries(cfg.users.map((u) => [u.id, u.active])),
            currentRole: cfg.role,
            perms: clone(cfg.perms),
            special: clone(cfg.special),
            invite: { name: '', phone: '', email: '', role: 'Sales Staff', branch: cfg.branch, sms: true, email_invite: false, twofa: true },
            newInvites: [],
            sent: '',

            /* Staff table filter (name, email or phone digits) */
            showUser(id) {
                const u = cfg.users.find((x) => x.id === id);
                const q = this.q.trim().toLowerCase();
                const digits = q.replace(/\D/g, '');
                const hit = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
                    || (digits !== '' && u.phone.replace(/\D/g, '').includes(digits));
                return hit && (!this.roleFilter || u.role === this.roleFilter);
            },
            get visibleUsers() { return cfg.users.filter((u) => this.showUser(u.id)).length; },

            /* Roles + permission matrix */
            pickRole(name) { this.currentRole = name; },
            togglePerm(m, a) { this.perms[this.currentRole][m][a] = !this.perms[this.currentRole][m][a]; },
            get permCount() { return this.perms[this.currentRole].flat().filter(Boolean).length; },
            resetPerms() {
                this.perms[this.currentRole] = clone(cfg.perms[this.currentRole]);
                this.special[this.currentRole] = clone(cfg.special[this.currentRole]);
            },

            /* Invite */
            openInvite() {
                document.getElementById('invite').scrollIntoView({ behavior: 'smooth', block: 'start' });
                this.$nextTick(() => this.$refs.inviteName.focus({ preventScroll: true }));
            },
            sendInvite() {
                const p = this.invite.phone.trim();
                const masked = p.length >= 6 ? p.slice(0, 3) + '•••••' + p.slice(-3) : p;
                this.newInvites.push({ name: this.invite.name.trim(), phone: masked });
                this.sent = 'Invite sent to ' + this.invite.name.trim() + '.';
                this.invite.name = '';
                this.invite.phone = '';
                this.invite.email = '';
            },
        }));
    });
</script>
@endpush
