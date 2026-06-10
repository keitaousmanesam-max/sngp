<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminNationalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'keitaousmanesam@gmail.com'],
            [
                'nom'                  => 'Keita',
                'prenom'               => 'Administrateur',
                'email'                => 'keitaousmanesam@gmail.com',
                'telephone'            => '+224 000 000 000',
                'password'             => Hash::make('Admin@2026'),
                'premiere_connexion'   => false,
                'actif'                => true,
                'pharmacie_id'         => null,
            ]
        );

        $admin->assignRole('admin_national');

        $this->command->info('Administrateur national créé avec succès !');
        $this->command->info('Email    : keitaousmanesam@gmail.com');
        $this->command->info('Password : Admin@2026');
    }
}