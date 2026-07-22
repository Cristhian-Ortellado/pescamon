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
            ['name' => 'Dorado', 'scientific_name' => 'Salminus brasiliensis', 'rarity' => 'legendary', 'avg_kg' => 6.0, 'habitat' => 'Aguas de corriente rápida, cauce principal de ríos grandes.'],
            ['name' => 'Manguruyú', 'scientific_name' => 'Zungaro zungaro', 'rarity' => 'legendary', 'avg_kg' => 25.0, 'habitat' => 'Pozones profundos y aguas turbias del río Paraguay.'],
            ['name' => 'Surubí Pintado', 'scientific_name' => 'Pseudoplatystoma corruscans', 'rarity' => 'legendary', 'avg_kg' => 15.0, 'habitat' => 'Cauce principal, fondo arenoso o rocoso.'],
            ['name' => 'Surubí Atigrado', 'scientific_name' => 'Pseudoplatystoma reticulatum', 'rarity' => 'legendary', 'avg_kg' => 10.0, 'habitat' => 'Aguas profundas y lentas del río Paraná y Paraguay.'],
            ['name' => 'Patí', 'scientific_name' => 'Luciopimelodus pati', 'rarity' => 'rare', 'avg_kg' => 4.0, 'habitat' => 'Aguas profundas y lentas, especie migratoria.'],
            ['name' => 'Pacú', 'scientific_name' => 'Piaractus mesopotamicus', 'rarity' => 'rare', 'avg_kg' => 5.0, 'habitat' => 'Ambientes bien oxigenados, migratorio estacional.'],
            ['name' => 'Boga', 'scientific_name' => 'Leporinus obtusidens', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Corrientes moderadas, zonas de vegetación ribereña.'],
            ['name' => 'Armado', 'scientific_name' => 'Pterodoras granulosus', 'rarity' => 'rare', 'avg_kg' => 3.0, 'habitat' => 'Fondo arenoso o fangoso, aguas cálidas.'],
            ['name' => 'Corvina de Río', 'scientific_name' => 'Plagioscion ternetzi', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Cauce principal, aguas medias a profundas.'],
            ['name' => 'Chafalote', 'scientific_name' => 'Rhaphiodon vulpinus', 'rarity' => 'rare', 'avg_kg' => 3.0, 'habitat' => 'Aguas abiertas, superficie de ríos caudalosos.'],
            ['name' => 'Raya de Río', 'scientific_name' => 'Potamotrygon motoro', 'rarity' => 'rare', 'avg_kg' => 4.0, 'habitat' => 'Fondo arenoso, aguas someras y tranquilas.'],
            ['name' => 'Manduví', 'scientific_name' => 'Ageneiosus spp.', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Aguas medias, zonas de correntada.'],
            ['name' => 'Amarillo Grande', 'scientific_name' => 'Pimelodus albicans', 'rarity' => 'rare', 'avg_kg' => 2.0, 'habitat' => 'Fondo fangoso, ríos caudalosos.'],
            ['name' => 'Dientudo Grande', 'scientific_name' => 'Acestrorhynchus pantaneiro', 'rarity' => 'rare', 'avg_kg' => 1.0, 'habitat' => 'Aguas abiertas, cardúmenes cerca de la superficie.'],
            ['name' => 'Cachara', 'scientific_name' => 'Pseudoplatystoma sp.', 'rarity' => 'rare', 'avg_kg' => 8.0, 'habitat' => 'Pozones profundos, fondo rocoso.'],
            ['name' => 'Bagre Trompudo', 'scientific_name' => 'Sorubim lima', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Aguas someras cercanas a la orilla.'],
            ['name' => 'Palometón', 'scientific_name' => 'Serrasalmus marginatus', 'rarity' => 'rare', 'avg_kg' => 0.8, 'habitat' => 'Aguas cálidas, vegetación sumergida, ríos y arroyos de planicie de inundación.'],
            ['name' => 'Raya Chica', 'scientific_name' => 'Potamotrygon sp.', 'rarity' => 'rare', 'avg_kg' => 1.0, 'habitat' => 'Fondo arenoso de arroyos y bañados.'],
            ['name' => 'Loricaria', 'scientific_name' => 'Loricaria spp.', 'rarity' => 'rare', 'avg_kg' => 0.3, 'habitat' => 'Fondo arenoso, aguas de corriente suave.'],
            ['name' => 'Bagre Negro', 'scientific_name' => 'Rhamdia branneri', 'rarity' => 'rare', 'avg_kg' => 1.0, 'habitat' => 'Cuevas y refugios rocosos, hábitos nocturnos.'],
            ['name' => 'Rinodoras', 'scientific_name' => 'Rhinodoras dorbignyi', 'rarity' => 'rare', 'avg_kg' => 0.5, 'habitat' => 'Fondo fangoso de ríos y arroyos.'],
            ['name' => 'Cynolebias', 'scientific_name' => 'Cynolebias spp.', 'rarity' => 'rare', 'avg_kg' => 0.01, 'habitat' => 'Charcas temporarias y bañados estacionales.'],
            ['name' => 'Apistogramma', 'scientific_name' => 'Apistogramma spp.', 'rarity' => 'rare', 'avg_kg' => 0.01, 'habitat' => 'Arroyos de aguas tranquilas y vegetación densa.'],
            ['name' => 'Anchoa de Río', 'scientific_name' => 'Lycengraulis grossidens', 'rarity' => 'rare', 'avg_kg' => 0.05, 'habitat' => 'Aguas abiertas, cardúmenes migratorios.'],
            ['name' => 'Curimbatá', 'scientific_name' => 'Prochilodus sp.', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Aguas medias, fondo limoso.'],
            ['name' => 'Bagre Sapo Grande', 'scientific_name' => 'Rhamdia quelen', 'rarity' => 'rare', 'avg_kg' => 1.5, 'habitat' => 'Pozones y refugios bajo raíces.'],
            ['name' => 'Piraña Amarilla', 'scientific_name' => 'Serrasalmus maculatus', 'rarity' => 'uncommon', 'avg_kg' => 0.6, 'habitat' => 'Aguas cálidas con vegetación, cardúmenes.'],
            ['name' => 'Palometa', 'scientific_name' => 'Serrasalmus spilopleura', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Vegetación acuática, aguas tranquilas.'],
            ['name' => 'Cascarudo', 'scientific_name' => 'Hoplosternum littorale', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Fondo fangoso, aguas con poco oxígeno.'],
            ['name' => 'Mandi\'í', 'scientific_name' => 'Pimelodella gracilis', 'rarity' => 'uncommon', 'avg_kg' => 0.15, 'habitat' => 'Arroyos de corriente suave.'],
            ['name' => 'Vieja del Agua Grande', 'scientific_name' => 'Hypostomus commersoni', 'rarity' => 'uncommon', 'avg_kg' => 2.0, 'habitat' => 'Fondo rocoso, aguas de corriente moderada.'],
            ['name' => 'Bagre Sapo', 'scientific_name' => 'Rhamdia quelen', 'rarity' => 'uncommon', 'avg_kg' => 0.8, 'habitat' => 'Refugios bajo piedras y troncos.'],
            ['name' => 'Guacamayo', 'scientific_name' => 'Pterygoplichthys anisitsi', 'rarity' => 'uncommon', 'avg_kg' => 1.5, 'habitat' => 'Fondo fangoso de ríos y lagunas.'],
            ['name' => 'Chanchita', 'scientific_name' => 'Australoheros facetum', 'rarity' => 'uncommon', 'avg_kg' => 0.1, 'habitat' => 'Aguas tranquilas con vegetación.'],
            ['name' => 'Cabeza Amarga', 'scientific_name' => 'Metynnis mola', 'rarity' => 'uncommon', 'avg_kg' => 0.2, 'habitat' => 'Aguas cálidas, vegetación sumergida.'],
            ['name' => 'Tetra Ojo Rojo', 'scientific_name' => 'Moenkhausia sanctaefilomenae', 'rarity' => 'uncommon', 'avg_kg' => 0.01, 'habitat' => 'Arroyos claros con vegetación.'],
            ['name' => 'Salmón de Agua Dulce', 'scientific_name' => 'Brycon sp.', 'rarity' => 'uncommon', 'avg_kg' => 1.0, 'habitat' => 'Aguas de corriente rápida y oxigenada.'],
            ['name' => 'Mojarra Grande', 'scientific_name' => 'Astyanax abramis', 'rarity' => 'uncommon', 'avg_kg' => 0.15, 'habitat' => 'Cardúmenes en aguas abiertas.'],
            ['name' => 'Dientudo Chico', 'scientific_name' => 'Acestrorhynchus lacustris', 'rarity' => 'uncommon', 'avg_kg' => 0.2, 'habitat' => 'Aguas superficiales de ríos y lagunas.'],
            ['name' => 'Bagre Pintado Chico', 'scientific_name' => 'Pimelodus ornatus', 'rarity' => 'uncommon', 'avg_kg' => 0.3, 'habitat' => 'Fondo arenoso de ríos medianos.'],
            ['name' => 'Otocinclus', 'scientific_name' => 'Otocinclus vittatus', 'rarity' => 'uncommon', 'avg_kg' => 0.005, 'habitat' => 'Vegetación sumergida, aguas claras.'],
            ['name' => 'Palometa Plateada', 'scientific_name' => 'Metynnis lippincottianus', 'rarity' => 'uncommon', 'avg_kg' => 0.1, 'habitat' => 'Aguas cálidas con vegetación.'],
            ['name' => 'Sábalo', 'scientific_name' => 'Prochilodus lineatus', 'rarity' => 'common', 'avg_kg' => 1.5, 'habitat' => 'Fondo limoso, cardúmenes grandes.'],
            ['name' => 'Tararira', 'scientific_name' => 'Hoplias malabaricus', 'rarity' => 'common', 'avg_kg' => 1.5, 'habitat' => 'Lagunas, bañados y aguas de corriente lenta.'],
            ['name' => 'Bagre Amarillo', 'scientific_name' => 'Pimelodus maculatus', 'rarity' => 'common', 'avg_kg' => 0.8, 'habitat' => 'Fondo fangoso, muy abundante.'],
            ['name' => 'Piraña Roja', 'scientific_name' => 'Pygocentrus nattereri', 'rarity' => 'common', 'avg_kg' => 0.6, 'habitat' => 'Aguas cálidas, cardúmenes.'],
            ['name' => 'Mojarra', 'scientific_name' => 'Astyanax spp.', 'rarity' => 'common', 'avg_kg' => 0.02, 'habitat' => 'Arroyos, lagunas y bañados.'],
            ['name' => 'Tetra Buenos Aires', 'scientific_name' => 'Hyphessobrycon anisitsi', 'rarity' => 'common', 'avg_kg' => 0.005, 'habitat' => 'Aguas subtropicales de arroyos.'],
            ['name' => 'Vieja del Agua', 'scientific_name' => 'Hypostomus spp.', 'rarity' => 'common', 'avg_kg' => 0.5, 'habitat' => 'Fondo rocoso o arenoso.'],
            ['name' => 'Sabalito', 'scientific_name' => 'Astyanax fasciatus', 'rarity' => 'common', 'avg_kg' => 0.05, 'habitat' => 'Arroyos y ríos de corriente moderada.'],
            ['name' => 'Chanchito', 'scientific_name' => 'Cichlasoma dimerus', 'rarity' => 'common', 'avg_kg' => 0.1, 'habitat' => 'Aguas tranquilas con vegetación.'],
            ['name' => 'Cascarudo Chico', 'scientific_name' => 'Callichthys callichthys', 'rarity' => 'common', 'avg_kg' => 0.1, 'habitat' => 'Fondo fangoso, poco oxígeno.'],
            ['name' => 'Bagrecito', 'scientific_name' => 'Pimelodella laticeps', 'rarity' => 'common', 'avg_kg' => 0.02, 'habitat' => 'Arroyos de corriente suave.'],
            ['name' => 'Tetra Limón', 'scientific_name' => 'Hyphessobrycon bifasciatus', 'rarity' => 'common', 'avg_kg' => 0.005, 'habitat' => 'Arroyos con vegetación densa.'],
            ['name' => 'Viejita', 'scientific_name' => 'Otocinclus arnoldi', 'rarity' => 'common', 'avg_kg' => 0.01, 'habitat' => 'Aguas claras con vegetación.'],
            ['name' => 'Guppy Silvestre', 'scientific_name' => 'Poecilia reticulata', 'rarity' => 'common', 'avg_kg' => 0.001, 'habitat' => 'Arroyos y canales de agua tranquila (especie introducida).'],
            ['name' => 'Espada', 'scientific_name' => 'Xiphophorus hellerii', 'rarity' => 'common', 'avg_kg' => 0.003, 'habitat' => 'Arroyos de aguas cálidas (especie introducida).'],
            ['name' => 'Madrecita', 'scientific_name' => 'Phalloceros caudimaculatus', 'rarity' => 'common', 'avg_kg' => 0.001, 'habitat' => 'Charcas y arroyos de poca profundidad.'],
            ['name' => 'Bagre Trompita', 'scientific_name' => 'Trachelyopterus sp.', 'rarity' => 'common', 'avg_kg' => 0.1, 'habitat' => 'Fondo arenoso de arroyos.'],
            ['name' => 'Mojarrita Plateada', 'scientific_name' => 'Cheirodon spp.', 'rarity' => 'common', 'avg_kg' => 0.01, 'habitat' => 'Aguas claras de arroyos y lagunas.'],
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
