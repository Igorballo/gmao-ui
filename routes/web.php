<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Machines\Index as MachinesIndex;
use App\Livewire\Pannes\Index as PannesIndex;
use App\Livewire\Pannes\Show as PannesShow;
use App\Livewire\Rapports\Index as RapportsIndex;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\Rapport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Connexion (invités uniquement)
Route::get('/login', Login::class)->middleware('guest')->name('login');

// Zone authentifiée
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/machines', MachinesIndex::class)
        ->middleware('permission:machines.consulter')
        ->name('machines.index');

    Route::get('/pannes', PannesIndex::class)
        ->middleware('permission:pannes.consulter')
        ->name('pannes.index');

    Route::get('/pannes/{panne}', PannesShow::class)
        ->middleware('permission:pannes.consulter')
        ->name('pannes.show');

    Route::get('/utilisateurs', UsersIndex::class)
        ->middleware('permission:utilisateurs.gerer')
        ->name('users.index');

    Route::get('/roles', RolesIndex::class)
        ->middleware('permission:roles.gerer')
        ->name('roles.index');

    Route::get('/rapports', RapportsIndex::class)
        ->middleware('permission:rapports.consulter')
        ->name('rapports.index');

    Route::get('/rapports/{rapport}/telecharger', function (Rapport $rapport) {
        abort_unless($rapport->chemin_pdf && Storage::disk('local')->exists($rapport->chemin_pdf), 404);

        return Storage::disk('local')->download(
            $rapport->chemin_pdf,
            'rapport-'.$rapport->date_rapport->format('Y-m-d').'.pdf',
        );
    })->middleware('permission:rapports.consulter')->name('rapports.telecharger');
});
