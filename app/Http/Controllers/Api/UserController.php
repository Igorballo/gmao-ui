<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /** Liste des utilisateurs actifs (pour l'assignation/délégation). */
    public function index(Request $request)
    {
        abort_unless($request->user()->can('pannes.deleguer'), 403);

        return User::where('actif', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]);
    }
}
