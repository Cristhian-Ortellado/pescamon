<?php

namespace App\Console\Commands;

use App\Models\Species;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateSpeciesReferenceImages extends Command
{
    protected $signature = 'species:generate-images
        {--only=* : Limitar a species puntuales por slug}
        {--force : Regenerar aunque el archivo ya exista}
        {--sleep=3 : Segundos de espera entre species para no pegarle de golpe a las APIs}';

    protected $description = 'Busca una foto por especie en iNaturalist y la edita con Gemini para usarla como imagen de referencia';

    public function handle(): int
    {
        $geminiKey = config('services.gemini.key');

        if (! $geminiKey) {
            $this->error('Falta GEMINI_API_KEY en el .env');

            return self::FAILURE;
        }

        $only = collect($this->option('only'));
        $force = (bool) $this->option('force');
        $sleep = (int) $this->option('sleep');

        $species = Species::query()
            ->when($only->isNotEmpty(), fn ($query) => $query->whereIn('slug', $only))
            ->orderBy('name')
            ->get();

        foreach ($species as $item) {
            $normalizeName = $this->normalizeName($item->name);
            $path = "images/species/{$normalizeName}-placeholder.png";

            if (! $force && Storage::disk('public')->exists($path)) {
                $this->line("Ya existe, salteo: {$item->name}");

                continue;
            }

            $this->info("Procesando {$item->name} ({$item->scientific_name})...");

            $source = $this->fetchSourcePhoto($item->scientific_name);

            if (! $source) {
                $this->warn("  Sin foto fuente en iNaturalist para {$item->name}, la salteo.");

                continue;
            }

            $edited = $this->editWithGemini($item->name, $item->scientific_name, $source['bytes'], $source['mime'], $geminiKey);

            if (! $edited) {
                $this->warn("  Gemini no devolvió imagen para {$item->name}, la salteo.");

                continue;
            }

            Storage::disk('public')->put($path, $edited['bytes']);
            $this->info("  Guardada en storage/app/public/{$path}");

            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        return self::SUCCESS;
    }

    private function normalizeName(string $name): string
    {
        return str_replace(' ', '_', strtolower(Str::ascii($name)));
    }

    /**
     * @return array{bytes: string, mime: string}|null
     */
    private function fetchSourcePhoto(string $scientificName): ?array
    {
        $taxon = Http::get('https://api.inaturalist.org/v1/taxa', [
            'q' => $scientificName,
            'rank' => 'species',
            'per_page' => 1,
        ])->json('results.0');

        if (! $taxon) {
            return null;
        }

        $photoUrl = null;

        $observation = Http::get('https://api.inaturalist.org/v1/observations', [
            'taxon_id' => $taxon['id'],
            'photos' => 'true',
            'quality_grade' => 'research',
            'order_by' => 'votes',
            'order' => 'desc',
            'per_page' => 1,
        ])->json('results.0');

        if ($observation && ! empty($observation['photos'][0]['url'])) {
            $photoUrl = str_replace('square.', 'large.', $observation['photos'][0]['url']);
        } elseif (! empty($taxon['default_photo']['medium_url'])) {
            $photoUrl = $taxon['default_photo']['medium_url'];
        }

        if (! $photoUrl) {
            return null;
        }

        $response = Http::get($photoUrl);

        if (! $response->successful()) {
            return null;
        }

        return [
            'bytes' => $response->body(),
            'mime' => $response->header('Content-Type') ?: 'image/jpeg',
        ];
    }

    /**
     * @return array{bytes: string, mime: string}|null
     */
    private function editWithGemini(string $name, string $scientificName, string $bytes, string $mime, string $apiKey): ?array
    {
        $model = config('services.gemini.model');

        $prompt = "Edita esta imagen para que solo se vea el pez {$name} ({$scientificName}) con un fondo lindo y natural acorde a su hábitat. "
            ."No agregues marcos, bordes, texto ni watermarks. El pez debe quedar centrado y ser el foco principal de la imagen.";

        $response = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mime,
                                    'data' => base64_encode($bytes),
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        if (! $response->successful()) {
            $this->warn('  Error Gemini: '.$response->body());

            return null;
        }

        $parts = $response->json('candidates.0.content.parts', []);

        foreach ($parts as $part) {
            if (! empty($part['inlineData']['data'])) {
                return [
                    'bytes' => base64_decode($part['inlineData']['data']),
                    'mime' => $part['inlineData']['mimeType'] ?? 'image/png',
                ];
            }
        }

        return null;
    }
}
