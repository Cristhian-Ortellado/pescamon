<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Caña Spinning Pro 2.10m',
                'description' => 'Caña de spinning de dos tramos en grafito IM7, acción media-rápida y potencia 10-30 lb. Ideal para dorado y tararira en río. Anillas de acero inoxidable con inserto cerámico y portacarrete de aluminio.',
                'price' => 850000,
                'image' => 'cana-spinning-pro-2-10m.png',
            ],
            [
                'name' => 'Reel Frontal 4000 - 6 Rulemanes',
                'description' => 'Carrete frontal con 6 rulemanes + 1, freno delantero de discos múltiples y bobina de aluminio extra. Recuperación 5.2:1. Robusto y suave para jornadas largas.',
                'price' => 620000,
                'image' => 'reel-frontal-4000-6-rulemanes.png',
            ],
            [
                'name' => 'Señuelo Poppers Superficie (Pack x3)',
                'description' => 'Set de 3 poppers de superficie de 9 cm, colores surtidos alta visibilidad. Anzuelos triples de acero al carbono. Explosivos para ataques de superficie de tararira y dorado.',
                'price' => 145000,
                'image' => 'senuelo-poppers-superficie-pack-x3.png',
            ],
            [
                'name' => 'Línea Multifilamento 0.20mm 150m',
                'description' => 'Multifilamento trenzado de 4 hebras, 0.20 mm / 20 lb, 150 metros. Mínima elasticidad para clavadas firmes y máxima sensibilidad. Color verde musgo camuflado.',
                'price' => 190000,
                'image' => 'linea-multifilamento-0-20mm-150m.png',
            ],
            [
                'name' => 'Caja de Aparejos 4 Bandejas',
                'description' => 'Caja organizadora de doble tapa con 4 bandejas regulables y cierre hermético. Compartimentos ajustables para señuelos, plomadas, anzuelos y accesorios.',
                'price' => 175000,
                'image' => 'caja-de-aparejos-4-bandejas.png',
            ],
            [
                'name' => 'Kit de Anzuelos Surtidos (100u)',
                'description' => 'Estuche con 100 anzuelos de acero al carbono en tamaños surtidos del 1 al 12. Alta penetración y resistencia a la corrosión. Incluye organizador plástico.',
                'price' => 65000,
                'image' => 'kit-de-anzuelos-surtidos-100u.png',
            ],
            [
                'name' => 'Chaleco de Pesca Multibolsillos',
                'description' => 'Chaleco técnico transpirable con 14 bolsillos, portaherramientas y anilla D. Talle regulable. Perfecto para vadeo y pesca embarcada.',
                'price' => 320000,
                'image' => 'chaleco-de-pesca-multibolsillos.png',
            ],
            [
                'name' => 'Red de Desembarque Mango Telescópico',
                'description' => 'Copo de goma antienredo y mango telescópico de aluminio extensible hasta 1.8 m. Liviana y resistente, cuida al pez para devolución.',
                'price' => 235000,
                'image' => 'red-de-desembarque-mango-telescopico.png',
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'is_active' => true,
                ],
            );

            $this->attachImage($product, $data['image']);
        }
    }

    /**
     * Publish the packaged product image onto the public disk and attach it
     * as the product cover (position 0). Idempotent across re-seeds.
     */
    private function attachImage(Product $product, string $filename): void
    {
        $source = base_path('files/products/'.$filename);
        $path = 'products/'.$filename;

        if (File::exists($source) && ! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, File::get($source));
        }

        $product->images()->updateOrCreate(
            ['position' => 0],
            ['path' => $path],
        );
    }
}
