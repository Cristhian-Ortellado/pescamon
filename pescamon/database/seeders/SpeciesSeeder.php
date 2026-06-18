<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            // Agua dulce
            ['name' => 'Trucha Arcoíris', 'scientific_name' => 'Oncorhynchus mykiss', 'rarity' => 'uncommon', 'avg_kg' => 1.5, 'habitat' => 'Ríos fríos y claros de montaña, lagos de altura.'],
            ['name' => 'Trucha Marrón', 'scientific_name' => 'Salmo trutta', 'rarity' => 'uncommon', 'avg_kg' => 2.0, 'habitat' => 'Ríos de corriente moderada, aguas bien oxigenadas.'],
            ['name' => 'Salmón Atlántico', 'scientific_name' => 'Salmo salar', 'rarity' => 'rare', 'avg_kg' => 5.0, 'habitat' => 'Ríos con acceso al mar, migra para desovar.'],
            ['name' => 'Dorado', 'scientific_name' => 'Salminus brasiliensis', 'rarity' => 'rare', 'avg_kg' => 8.0, 'habitat' => 'Grandes ríos sudamericanos de corriente rápida.'],
            ['name' => 'Bagre Amarillo', 'scientific_name' => 'Pylodictis olivaris', 'rarity' => 'common', 'avg_kg' => 3.0, 'habitat' => 'Fondos de ríos y lagos con vegetación.'],
            ['name' => 'Carpa Común', 'scientific_name' => 'Cyprinus carpio', 'rarity' => 'common', 'avg_kg' => 4.5, 'habitat' => 'Lagos, embalses y ríos de corriente lenta.'],
            ['name' => 'Perca', 'scientific_name' => 'Perca fluviatilis', 'rarity' => 'common', 'avg_kg' => 0.5, 'habitat' => 'Lagos y embalses con vegetación acuática.'],
            ['name' => 'Lucio', 'scientific_name' => 'Esox lucius', 'rarity' => 'uncommon', 'avg_kg' => 3.5, 'habitat' => 'Aguas tranquilas con abundante vegetación.'],
            ['name' => 'Siluro', 'scientific_name' => 'Silurus glanis', 'rarity' => 'rare', 'avg_kg' => 20.0, 'habitat' => 'Grandes ríos y embalses europeos.'],
            ['name' => 'Tenca', 'scientific_name' => 'Tinca tinca', 'rarity' => 'common', 'avg_kg' => 1.0, 'habitat' => 'Aguas templadas y lentas con fondo fangoso.'],
            ['name' => 'Pejerrey', 'scientific_name' => 'Odontesthes bonariensis', 'rarity' => 'common', 'avg_kg' => 0.4, 'habitat' => 'Lagunas y embalses de agua templada.'],
            // Agua salada / mixta
            ['name' => 'Lubina Europea', 'scientific_name' => 'Dicentrarchus labrax', 'rarity' => 'uncommon', 'avg_kg' => 2.0, 'habitat' => 'Costas rocosas, estuarios y fondos arenosos.'],
            ['name' => 'Atún Rojo', 'scientific_name' => 'Thunnus thynnus', 'rarity' => 'legendary', 'avg_kg' => 150.0, 'habitat' => 'Mar abierto, aguas oceánicas templadas y cálidas.'],
            ['name' => 'Dorada', 'scientific_name' => 'Sparus aurata', 'rarity' => 'common', 'avg_kg' => 1.2, 'habitat' => 'Fondos costeros, bahías y lagunas costeras.'],
            ['name' => 'Pargo Colorado', 'scientific_name' => 'Lutjanus campechanus', 'rarity' => 'uncommon', 'avg_kg' => 2.5, 'habitat' => 'Arrecifes y fondos rocosos del Golfo de México.'],
            ['name' => 'Mero Gigante', 'scientific_name' => 'Epinephelus itajara', 'rarity' => 'legendary', 'avg_kg' => 100.0, 'habitat' => 'Aguas tropicales, arrecifes coralinos y manglares.'],
            ['name' => 'Corvina Negra', 'scientific_name' => 'Pogonias cromis', 'rarity' => 'uncommon', 'avg_kg' => 10.0, 'habitat' => 'Estuarios y fondos arenosos del Atlántico occidental.'],
            ['name' => 'Palometa / Pompano', 'scientific_name' => 'Trachinotus carolinus', 'rarity' => 'uncommon', 'avg_kg' => 1.5, 'habitat' => 'Playas y aguas costeras poco profundas.'],
            ['name' => 'Caballa', 'scientific_name' => 'Scomber scombrus', 'rarity' => 'common', 'avg_kg' => 0.6, 'habitat' => 'Aguas superficiales del Atlántico Norte y Mediterráneo.'],
            ['name' => 'Pez Espada', 'scientific_name' => 'Xiphias gladius', 'rarity' => 'legendary', 'avg_kg' => 80.0, 'habitat' => 'Mar abierto, aguas oceánicas tropicales y templadas.'],
        ];

        foreach ($species as $data) {
            Species::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'scientific_name' => $data['scientific_name'],
                    'slug' => Str::slug($data['name']),
                    'habitat_description' => $data['habitat'],
                    'rarity' => $data['rarity'],
                    'average_weight_kg' => $data['avg_kg'],
                    'image_locked_path' => 'images/species/locked-placeholder.svg',
                    'image_reference_path' => 'images/species/'.$data['rarity'].'-placeholder.jpg',
                    'is_active' => true,
                ]
            );
        }
    }
}
