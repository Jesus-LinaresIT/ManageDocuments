<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios de ejemplo
        $users = [
            [
                'name' => 'Lic. William Antonio Geliz',
                'email' => 'william.geliz@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Director de Proyección Social'
            ],
            [
                'name' => 'Dr. Carlos Antonio Aguirre Ayala',
                'email' => 'carlos.aguirre@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Decano/a',
                'unit' => 'FICA'
            ],
            [
                'name' => 'Dra. Lissette Cristalina Canales',
                'email' => 'lissette.canales@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Decano/a',
                'unit' => 'FACE'
            ],
            [
                'name' => 'Lic. Manuel Eduardo Rodriguez',
                'email' => 'manuel.rodriguez@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Decano/a',
                'unit' => 'FADE'
            ],
            [
                'name' => 'Licda. Ana Arely Villalta',
                'email' => 'ana.villalta@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Decano/a',
                'unit' => 'FACS'
            ],
            [
                'name' => 'Lic. Henry Antonio Cerritos Santana',
                'email' => 'henry.cerritos@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Coordinador de Proyección Social',
                'unit' => 'FICA'
            ],
            [
                'name' => 'Licda. Blanca Ruth Galvez',
                'email' => 'blanca.galvez@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Coordinador de Proyección Social',
                'unit' => 'FACE'
            ],
            [
                'name' => 'Lic. Rene Ricardo Romero',
                'email' => 'rene.romero@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Coordinador de Proyección Social',
                'unit' => 'FADE'
            ],
            [
                'name' => 'Dr. Julio Cesar Martinez',
                'email' => 'julio.martinez@utec.edu.sv',
                'password' => bcrypt('password'),
                'role' => 'Coordinador de Proyección Social',
                'unit' => 'FACS'
            ]
        ];

        foreach ($users as $userData) {
            // Verificar si el usuario ya existe
            $existingUser = User::where('email', $userData['email'])->first();
            
            if ($existingUser) {
                // Actualizar datos del usuario existente
                $existingUser->update([
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]);
                
                // Asignar rol si no lo tiene
                if (!$existingUser->hasRole($userData['role'])) {
                    $role = Role::where('name', $userData['role'])->first();
                    if ($role) {
                        $existingUser->assignRole($role);
                    }
                }
                
                // Agregar unidad si existe
                if (isset($userData['unit'])) {
                    $existingUser->unit = $userData['unit'];
                    $existingUser->save();
                }
            } else {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ]);

                // Asignar rol
                $role = Role::where('name', $userData['role'])->first();
                if ($role) {
                    $user->assignRole($role);
                }

                // Agregar unidad si existe
                if (isset($userData['unit'])) {
                    $user->unit = $userData['unit'];
                    $user->save();
                }
            }
        }
    }
}
