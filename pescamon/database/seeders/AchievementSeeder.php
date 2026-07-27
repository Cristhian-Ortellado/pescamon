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
            [
                'code' => 'SPECIES_50',
                'name' => 'Ictiólogo',
                'description' => 'Has capturado 50 especies distintas. Un verdadero experto.',
                'xp_reward' => 600,
            ],
            [
                'code' => 'SPECIES_75',
                'name' => 'Gran Ictiólogo',
                'description' => 'Has capturado 75 especies distintas. Dominio total del PescaDex.',
                'xp_reward' => 900,
            ],
            [
                'code' => 'CATCH_100',
                'name' => 'Club del Centenar',
                'description' => 'Has registrado 100 capturas en total.',
                'xp_reward' => 250,
            ],
            [
                'code' => 'CATCH_500',
                'name' => 'Fanático de la Pesca',
                'description' => 'Has registrado 500 capturas en total.',
                'xp_reward' => 700,
            ],
            [
                'code' => 'PERSONAL_BEST_10',
                'name' => 'Rompe Récords',
                'description' => 'Has batido tu récord personal 10 veces.',
                'xp_reward' => 150,
            ],
            [
                'code' => 'STREAK_7',
                'name' => 'Guerrero Semanal',
                'description' => 'Pescaste durante 7 días consecutivos.',
                'xp_reward' => 120,
            ],
            [
                'code' => 'STREAK_30',
                'name' => 'Pescador Dedicado',
                'description' => 'Pescaste durante 30 días consecutivos.',
                'xp_reward' => 400,
            ],
            [
                'code' => 'DAWN_PATROL',
                'name' => 'Patrulla del Amanecer',
                'description' => 'Realizaste una captura entre las 05:00 y las 07:00.',
                'xp_reward' => 40,
            ],
            [
                'code' => 'PERFECT_WEEKEND',
                'name' => 'Fin de Semana Perfecto',
                'description' => 'Pescaste tanto el sábado como el domingo en la misma semana.',
                'xp_reward' => 60,
            ],
            [
                'code' => 'MULTI_SPECIES_DAY',
                'name' => 'Cinco en un Día',
                'description' => 'Capturaste 5 especies distintas en el mismo día.',
                'xp_reward' => 130,
            ],
            [
                'code' => 'RARE_COMBO',
                'name' => 'Combo Raro',
                'description' => 'Capturaste dos especies raras o superiores en el mismo día.',
                'xp_reward' => 180,
            ],
            [
                'code' => 'DOUBLE_WEIGHT',
                'name' => 'Captura Colosal',
                'description' => 'Capturaste un pez con el doble del peso promedio de su especie.',
                'xp_reward' => 90,
            ],
            [
                'code' => 'ALL_TACKLE',
                'name' => 'Pescador Versátil',
                'description' => 'Has capturado peces usando 5 tipos distintos de carnada o señuelos.',
                'xp_reward' => 90,
            ],
            [
                'code' => 'FIRST_RIVER_CATCH',
                'name' => 'Novato de Río',
                'description' => 'Registraste tu primera captura en un río.',
                'xp_reward' => 30,
            ],
            [
                'code' => 'FIRST_LAKE_CATCH',
                'name' => 'Novato de Lago',
                'description' => 'Registraste tu primera captura en un lago.',
                'xp_reward' => 30,
            ],
            [
                'code' => 'FIRST_SEA_CATCH',
                'name' => 'Novato de Mar',
                'description' => 'Registraste tu primera captura en el mar.',
                'xp_reward' => 30,
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
