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
            // Agua dulce: common, uncommon, rare, legendary
            ['name' => 'Dorado', 'normalize_name' => 'dorado', 'scientific_name' => 'Salminus brasiliensis', 'rarity' => 'legendary', 'avg_kg' => 6.0, 'habitat' => 'Aguas de corriente rápida, cauce principal de grandes ríos.'],
            ['name' => 'Manguruyú', 'normalize_name' => 'manguruyu', 'scientific_name' => 'Zungaro zungaro', 'rarity' => 'legendary', 'avg_kg' => 25.0, 'habitat' => 'Pozones profundos del río Paraguay y Paraná.'],
            ['name' => 'Surubí Pintado', 'normalize_name' => 'surubi_pintado', 'scientific_name' => 'Pseudoplatystoma corruscans', 'rarity' => 'legendary', 'avg_kg' => 15.0, 'habitat' => 'Fondos arenosos y rocosos de grandes ríos.'],
            ['name' => 'Surubí Atigrado', 'normalize_name' => 'surubi_atigrado', 'scientific_name' => 'Pseudoplatystoma reticulatum', 'rarity' => 'legendary', 'avg_kg' => 10.0, 'habitat' => 'Aguas profundas y lentas del Paraná y Paraguay.'],
            ['name' => 'Pacú', 'normalize_name' => 'pacu', 'scientific_name' => 'Piaractus mesopotamicus', 'rarity' => 'legendary', 'avg_kg' => 6.0, 'habitat' => 'Ríos caudalosos y bosques inundados.'],

            ['name' => 'Patí', 'normalize_name' => 'pati', 'scientific_name' => 'Luciopimelodus pati', 'rarity' => 'rare', 'avg_kg' => 4.0, 'habitat' => 'Canales profundos de grandes ríos.'],
            ['name' => 'Boga', 'normalize_name' => 'boga', 'scientific_name' => 'Leporinus obtusidens', 'rarity' => 'rare', 'avg_kg' => 2.5, 'habitat' => 'Corrientes moderadas con vegetación ribereña.'],
            ['name' => 'Armado', 'normalize_name' => 'armado', 'scientific_name' => 'Pterodoras granulosus', 'rarity' => 'rare', 'avg_kg' => 3.0, 'habitat' => 'Fondos fangosos y arenosos.'],
            ['name' => 'Pirapitá', 'normalize_name' => 'pirapita', 'scientific_name' => 'Brycon hilarii', 'rarity' => 'rare', 'avg_kg' => 3.0, 'habitat' => 'Ríos de aguas rápidas y claras.'],
            ['name' => 'Pirá Pytã', 'normalize_name' => 'pira_pyta', 'scientific_name' => 'Brycon orbignyanus', 'rarity' => 'rare', 'avg_kg' => 5.0, 'habitat' => 'Grandes ríos con corrientes fuertes.'],
            ['name' => 'Chafalote', 'normalize_name' => 'chafalote', 'scientific_name' => 'Rhaphiodon vulpinus', 'rarity' => 'rare', 'avg_kg' => 3.0, 'habitat' => 'Superficie de grandes ríos.'],
            ['name' => 'Corvina de Río', 'normalize_name' => 'corvina_de_rio', 'scientific_name' => 'Plagioscion ternetzi', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Canales profundos.'],
            ['name' => 'Raya de Río', 'normalize_name' => 'raya_de_rio', 'scientific_name' => 'Potamotrygon motoro', 'rarity' => 'rare', 'avg_kg' => 4.0, 'habitat' => 'Fondos arenosos de ríos y lagunas.'],
            ['name' => 'Curimbatá', 'normalize_name' => 'curimbata', 'scientific_name' => 'Prochilodus lineatus', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Grandes cardúmenes en ríos.'],
            ['name' => 'Cachara', 'normalize_name' => 'cachara', 'scientific_name' => 'Pseudoplatystoma fasciatum', 'rarity' => 'rare', 'avg_kg' => 8.0, 'habitat' => 'Pozones profundos.'],

            ['name' => 'Manduví', 'normalize_name' => 'manduvi', 'scientific_name' => 'Ageneiosus valenciennesi', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Ríos de corriente moderada.'],
            ['name' => 'Amarillo', 'normalize_name' => 'amarillo', 'scientific_name' => 'Pimelodus maculatus', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Fondos fangosos de grandes ríos.'],
            ['name' => 'Doncella', 'normalize_name' => 'doncella', 'scientific_name' => 'Sorubim lima', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Orillas y canales profundos.'],
            ['name' => 'Palometón', 'normalize_name' => 'palometon', 'scientific_name' => 'Serrasalmus marginatus', 'rarity' => 'rare', 'avg_kg' => 0.8, 'habitat' => 'Vegetación sumergida.'],
            ['name' => 'Dientudo', 'normalize_name' => 'dientudo', 'scientific_name' => 'Acestrorhynchus pantaneiro', 'rarity' => 'rare', 'avg_kg' => 1.0, 'habitat' => 'Superficie de ríos.'],
            ['name' => 'Bagre Negro', 'normalize_name' => 'bagre_negro', 'scientific_name' => 'Rhamdia quelen', 'rarity' => 'rare', 'avg_kg' => 1.2, 'habitat' => 'Pozones y cuevas naturales.'],
            ['name' => 'Raya Chica', 'normalize_name' => 'raya_chica', 'scientific_name' => 'Potamotrygon sp.', 'rarity' => 'rare', 'avg_kg' => 1.0, 'habitat' => 'Arroyos y bañados.'],
            ['name' => 'Moncholo', 'normalize_name' => 'moncholo', 'scientific_name' => 'Parauchenipterus galeatus', 'rarity' => 'rare', 'avg_kg' => 0.8, 'habitat' => 'Ríos y lagunas.'],
            ['name' => 'Mbusu', 'normalize_name' => 'mbusu', 'scientific_name' => 'Gymnotus carapo', 'rarity' => 'rare', 'avg_kg' => 0.7, 'habitat' => 'Lagunas y esteros.'],
            ['name' => 'Anguila Criolla', 'normalize_name' => 'anguila_criolla', 'scientific_name' => 'Synbranchus marmoratus', 'rarity' => 'rare', 'avg_kg' => 0.8, 'habitat' => 'Bañados y zonas pantanosas.'],

            ['name' => 'Piraña Amarilla', 'normalize_name' => 'pirana_amarilla', 'scientific_name' => 'Serrasalmus maculatus', 'rarity' => 'uncommon', 'avg_kg' => 0.6, 'habitat' => 'Lagunas y vegetación acuática.'],
            ['name' => 'Palometa', 'normalize_name' => 'palometa', 'scientific_name' => 'Serrasalmus spilopleura', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Aguas tranquilas.'],
            ['name' => 'Cascarudo', 'normalize_name' => 'cascarudo', 'scientific_name' => 'Hoplosternum littorale', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Fondos fangosos.'],
            ['name' => 'Mandi\'í', 'normalize_name' => 'mandi\'i', 'scientific_name' => 'Pimelodella gracilis', 'rarity' => 'uncommon', 'avg_kg' => 0.15, 'habitat' => 'Arroyos.'],
            ['name' => 'Vieja del Agua Grande', 'normalize_name' => 'vieja_del_agua_grande', 'scientific_name' => 'Hypostomus commersoni', 'rarity' => 'uncommon', 'avg_kg' => 2.0, 'habitat' => 'Fondos rocosos.'],
            ['name' => 'Bagre Sapo', 'normalize_name' => 'bagre_sapo', 'scientific_name' => 'Rhamdia quelen', 'rarity' => 'uncommon', 'avg_kg' => 0.8, 'habitat' => 'Raíces y troncos sumergidos.'],
            ['name' => 'Guacamayo', 'normalize_name' => 'guacamayo', 'scientific_name' => 'Pterygoplichthys anisitsi', 'rarity' => 'uncommon', 'avg_kg' => 1.5, 'habitat' => 'Lagunas y ríos lentos.'],
            ['name' => 'Chanchita', 'normalize_name' => 'chanchita', 'scientific_name' => 'Australoheros facetus', 'rarity' => 'uncommon', 'avg_kg' => 0.12, 'habitat' => 'Vegetación acuática.'],
            ['name' => 'Mojarra Grande', 'normalize_name' => 'mojarra_grande', 'scientific_name' => 'Astyanax abramis', 'rarity' => 'uncommon', 'avg_kg' => 0.15, 'habitat' => 'Cardúmenes en ríos.'],
            ['name' => 'Dientudo Chico', 'normalize_name' => 'dientudo_chico', 'scientific_name' => 'Acestrorhynchus lacustris', 'rarity' => 'uncommon', 'avg_kg' => 0.2, 'habitat' => 'Aguas superficiales.'],
            ['name' => 'Bagre Pintado Chico', 'normalize_name' => 'bagre_pintado_chico', 'scientific_name' => 'Pimelodus ornatus', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Fondos arenosos.'],
            ['name' => 'Cabeza Amarga', 'normalize_name' => 'cabeza_amarga', 'scientific_name' => 'Metynnis mola', 'rarity' => 'uncommon', 'avg_kg' => 0.2, 'habitat' => 'Lagunas con vegetación.'],
            ['name' => 'Morenita', 'normalize_name' => 'morenita', 'scientific_name' => 'Gymnogeophagus balzanii', 'rarity' => 'uncommon', 'avg_kg' => 0.25, 'habitat' => 'Lagunas y arroyos.'],
            ['name' => 'Boguero', 'normalize_name' => 'boguero', 'scientific_name' => 'Schizodon borellii', 'rarity' => 'uncommon', 'avg_kg' => 1.0, 'habitat' => 'Grandes ríos.'],
            ['name' => 'Bagre Trompita', 'normalize_name' => 'bagre_trompita', 'scientific_name' => 'Trachelyopterus galeatus', 'rarity' => 'uncommon', 'avg_kg' => 0.1, 'habitat' => 'Arroyos y lagunas.'],

            ['name' => 'Sábalo', 'normalize_name' => 'sabalo', 'scientific_name' => 'Prochilodus lineatus', 'rarity' => 'common', 'avg_kg' => 1.5, 'habitat' => 'Cardúmenes en grandes ríos.'],
            ['name' => 'Tararira', 'normalize_name' => 'tararira', 'scientific_name' => 'Hoplias malabaricus', 'rarity' => 'common', 'avg_kg' => 1.5, 'habitat' => 'Lagunas y esteros.'],
            ['name' => 'Bagre Amarillo', 'normalize_name' => 'bagre_amarillo', 'scientific_name' => 'Pimelodus maculatus', 'rarity' => 'common', 'avg_kg' => 0.8, 'habitat' => 'Fondos fangosos.'],
            ['name' => 'Piraña Roja', 'normalize_name' => 'pirana_roja', 'scientific_name' => 'Pygocentrus nattereri', 'rarity' => 'common', 'avg_kg' => 0.6, 'habitat' => 'Cardúmenes en aguas cálidas.'],
            ['name' => 'Mojarra', 'normalize_name' => 'mojarra', 'scientific_name' => 'Astyanax spp.', 'rarity' => 'common', 'avg_kg' => 0.03, 'habitat' => 'Arroyos y lagunas.'],
            ['name' => 'Vieja del Agua', 'normalize_name' => 'vieja_del_agua', 'scientific_name' => 'Hypostomus spp.', 'rarity' => 'common', 'avg_kg' => 0.5, 'habitat' => 'Fondos rocosos y arenosos.'],
            ['name' => 'Sabalito', 'normalize_name' => 'sabalito', 'scientific_name' => 'Astyanax fasciatus', 'rarity' => 'common', 'avg_kg' => 0.05, 'habitat' => 'Arroyos.'],
            ['name' => 'Chanchito', 'normalize_name' => 'chanchito', 'scientific_name' => 'Cichlasoma dimerus', 'rarity' => 'common', 'avg_kg' => 0.1, 'habitat' => 'Vegetación acuática.'],
            ['name' => 'Cascarudo Chico', 'normalize_name' => 'cascarudo_chico', 'scientific_name' => 'Callichthys callichthys', 'rarity' => 'common', 'avg_kg' => 0.1, 'habitat' => 'Fondos fangosos.'],
            ['name' => 'Bagrecito', 'normalize_name' => 'bagrecito', 'scientific_name' => 'Pimelodella laticeps', 'rarity' => 'common', 'avg_kg' => 0.02, 'habitat' => 'Arroyos pequeños.'],
            ['name' => 'Madrecita', 'normalize_name' => 'madrecita', 'scientific_name' => 'Phalloceros caudimaculatus', 'rarity' => 'common', 'avg_kg' => 0.001, 'habitat' => 'Charcas y arroyos.'],
            ['name' => 'Mojarrita Plateada', 'normalize_name' => 'mojarrita_plateada', 'scientific_name' => 'Cheirodon interruptus', 'rarity' => 'common', 'avg_kg' => 0.01, 'habitat' => 'Aguas claras.'],
            ['name' => 'Piquirí', 'normalize_name' => 'piquiri', 'scientific_name' => 'Cyphocharax spilotus', 'rarity' => 'common', 'avg_kg' => 0.3, 'habitat' => 'Arroyos y ríos lentos.'],
            ['name' => 'Pira\'i', 'normalize_name' => 'pira\'i', 'scientific_name' => 'Odontostilbe pequira', 'rarity' => 'common', 'avg_kg' => 0.01, 'habitat' => 'Cardúmenes de arroyos y lagunas.'],
            ['name' => 'Dentón', 'normalize_name' => 'denton', 'scientific_name' => 'Raphiodon vulpinus', 'rarity' => 'common', 'avg_kg' => 2.0, 'habitat' => 'Grandes ríos de corriente rápida.'],
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
                    'image_reference_path' => 'images/species/'.$data['normalize_name'].'-placeholder.png',
                    'is_active' => true,
                ]
            );
        }
    }
}
