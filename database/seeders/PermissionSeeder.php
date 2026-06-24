<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Permissions atomiques de l'application.
     * Les RÔLES sont créés dynamiquement depuis l'interface d'admin :
     * on ne seede ici que les permissions + un rôle Administrateur de bootstrap.
     */
    public const PERMISSIONS = [
        // Machines
        'machines.consulter',
        'machines.gerer',
        // Pannes
        'pannes.consulter',
        'pannes.creer',
        'pannes.deleguer',
        // Interventions
        'interventions.consulter',
        'interventions.prendre_en_charge',
        // Rapports
        'rapports.consulter',
        // Administration
        'utilisateurs.gerer',
        'roles.gerer',
    ];

    public function run(): void
    {
        // Vide le cache des permissions Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Création des permissions (idempotent)
        foreach (self::PERMISSIONS as $nom) {
            Permission::firstOrCreate([
                'name' => $nom,
                'guard_name' => 'web',
            ]);
        }

        // Rôle Administrateur de bootstrap (toutes les permissions).
        // Permet de se connecter et de créer les autres rôles depuis l'interface.
        $admin = Role::firstOrCreate([
            'name' => 'Administrateur',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions(self::PERMISSIONS);

        // Utilisateur administrateur par défaut.
        // ⚠️ Change l'email/mot de passe après la première connexion.
        $user = User::firstOrCreate(
            ['email' => 'admin@gmao.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'actif' => true,
            ],
        );
        $user->assignRole($admin);
    }
}
