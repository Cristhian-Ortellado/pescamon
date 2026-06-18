<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'code' => 'FIRST_CATCH',
                'name' => 'Primera Captura',
                'description' => '¡Registraste tu primera captura! El viaje comienza.',
                'xp_reward' => 25,
            ],
            [
                'code' => 'SPECIES_10',
                'name' => 'Coleccionista',
                'description' => 'Has capturado 10 especies distintas en tu PescaDex.',
                'xp_reward' => 100,
            ],
            [
                'code' => 'SPECIES_25',
                'name' => 'Maestro Pescador',
                'description' => 'Has capturado 25 especies distintas. ¡Leyenda!',
                'xp_reward' => 300,
            ],
            [
                'code' => 'FOUR_SEASONS',
                'name' => 'Pescador de las Cuatro Estaciones',
                'description' => 'Has pescado en las cuatro estaciones del año.',
                'xp_reward' => 150,
            ],
            [
                'code' => 'ABOVE_AVG_WEIGHT',
                'name' => 'Ejemplar Superior',
                'description' => 'Capturaste un pez por encima del peso promedio de su especie.',
                'xp_reward' => 50,
            ],
            [
                'code' => 'PERSONAL_BEST_5',
                'name' => 'Superando Límites',
                'description' => 'Has batido tu récord personal 5 veces.',
                'xp_reward' => 75,
            ],
            [
                'code' => 'NIGHT_FISHER',
                'name' => 'Pescador Nocturno',
                'description' => 'Realizaste una captura entre las 22:00 y las 05:00.',
                'xp_reward' => 40,
            ],
            [
                'code' => 'LEGENDARY_CATCH',
                'name' => 'Captura Legendaria',
                'description' => '¡Capturaste una especie de rareza legendaria!',
                'xp_reward' => 500,
            ],
        ];

        foreach ($achievements as $data) {
            Achievement::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'xp_reward' => $data['xp_reward'],
                    'icon_path' => 'images/achievements/'.$data['code'].'.svg',
                ]
            );
        }
    }
}
