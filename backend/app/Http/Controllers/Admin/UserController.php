<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\UsersData;
use Illuminate\View\View;

/**
 * Users & roles (design: Users.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => UsersData::users(),
            'roles' => UsersData::roles(),
            'roleTones' => UsersData::roleTones(),
            'modules' => UsersData::modules(),
            'actions' => UsersData::actions(),
            'permissions' => UsersData::permissions(),
            'special' => UsersData::specialPermissions(),
            'specialDefaults' => UsersData::specialDefaults(),
            'branches' => UsersData::branches(),
            'invites' => UsersData::pendingInvites(),
            'activeRole' => 'Sales Staff',
        ]);
    }
}
