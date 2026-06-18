<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

interface MapCatch {
    id: number;
    caught_at: string;
    latitude: number;
    longitude: number;
    user_name: string;
}

interface SpeciesData {
    id: number;
    name: string;
    scientific_name: string;
    slug: string;
    rarity: string;
    habitat_description: string | null;
    average_weight_kg: number | null;
    image_reference_path: string | null;
    image_locked_path: string | null;
}

const props = defineProps<{
    species: SpeciesData;
    userCaught: boolean;
    personalBest: { id: number; weight_kg: string; caught_at: string } | null;
    mapCatches: MapCatch[];
    stats: { total_catches: number; avg_weight_kg: number };
}>();

const mapContainer = ref<HTMLElement | null>(null);

const rarityLabel: Record<string, { label: string; classes: string }> = {
    common: { label: 'Común', classes: 'bg-gray-100 text-gray-700' },
    uncommon: { label: 'Poco común', classes: 'bg-emerald-100 text-emerald-700' },
    rare: { label: 'Rara', classes: 'bg-blue-100 text-blue-700' },
    legendary: { label: 'Legendaria', classes: 'bg-amber-100 text-amber-700' },
};

onMounted(async () => {
    if (!mapContainer.value || props.mapCatches.length === 0) return;

    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    const first = props.mapCatches[0];
    const map = L.map(mapContainer.value).setView([first.latitude, first.longitude], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);

    const bounds: [number, number][] = [];

    props.mapCatches.forEach((c) => {
        bounds.push([c.latitude, c.longitude]);
        L.circleMarker([c.latitude, c.longitude], {
            radius: 6,
            fillColor: '#3B82F6',
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.85,
        })
            .addTo(map)
            .bindPopup(`<b>${props.species.name}</b><br>${c.user_name}<br><small>${new Date(c.caught_at).toLocaleDateString('es-ES')}</small>`);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }
});
</script>

<template>
    <Head :title="species.name" />

    <div class="mx-auto max-w-4xl px-4 py-8">
        <Link href="/dashboard" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600">
            ← Volver al mapa
        </Link>

        <div class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-800">
            <!-- Species header -->
            <div class="flex flex-col gap-6 p-6 sm:flex-row">
                <!-- Image / silhouette -->
                <div class="flex h-48 w-48 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                    <img
                        v-if="userCaught && species.image_reference_path"
                        :src="`/storage/${species.image_reference_path}`"
                        :alt="species.name"
                        class="h-full w-full object-cover"
                    />
                    <img
                        v-else-if="species.image_locked_path"
                        :src="`/storage/${species.image_locked_path}`"
                        :alt="`${species.name} (bloqueado)`"
                        class="h-full w-full object-cover opacity-50 grayscale"
                    />
                    <span v-else class="text-7xl opacity-30">🐟</span>
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ species.name }}</h1>
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', rarityLabel[species.rarity]?.classes]">
                            ⭐ {{ rarityLabel[species.rarity]?.label ?? species.rarity }}
                        </span>
                        <span v-if="userCaught" class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            ✓ Capturado
                        </span>
                        <span v-else class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
                            🔒 Sin capturar
                        </span>
                    </div>

                    <p class="mt-1 text-sm italic text-gray-500">{{ species.scientific_name }}</p>

                    <p v-if="species.habitat_description" class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ species.habitat_description }}
                    </p>

                    <!-- Stats -->
                    <div class="mt-4 flex flex-wrap gap-3">
                        <div class="rounded-xl bg-gray-50 px-4 py-2 text-center dark:bg-gray-700">
                            <p class="text-xs text-gray-500">Peso promedio</p>
                            <p class="font-bold text-gray-900 dark:text-white">
                                {{ species.average_weight_kg ? species.average_weight_kg + ' kg' : 'N/D' }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-4 py-2 text-center dark:bg-gray-700">
                            <p class="text-xs text-gray-500">Capturas totales</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ stats.total_catches }}</p>
                        </div>
                        <div v-if="stats.avg_weight_kg" class="rounded-xl bg-gray-50 px-4 py-2 text-center dark:bg-gray-700">
                            <p class="text-xs text-gray-500">Peso medio registrado</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ stats.avg_weight_kg }} kg</p>
                        </div>
                    </div>

                    <!-- Personal best -->
                    <div v-if="personalBest" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">🏆 Tu récord personal</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ personalBest.weight_kg }} kg · {{ new Date(personalBest.caught_at).toLocaleDateString('es-ES') }}
                        </p>
                        <Link :href="`/catches/${personalBest.id}`" class="mt-1 inline-block text-xs text-blue-600 hover:underline">
                            Ver captura →
                        </Link>
                    </div>

                    <Link href="/catches/create" class="mt-4 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        🎣 Registrar captura
                    </Link>
                </div>
            </div>

            <!-- Map of catches -->
            <div v-if="mapCatches.length > 0" class="border-t border-gray-100 p-6 dark:border-gray-700">
                <h2 class="mb-3 font-semibold text-gray-800 dark:text-white">
                    Dónde se ha capturado esta especie ({{ mapCatches.length }})
                </h2>
                <div ref="mapContainer" class="h-64 w-full rounded-xl" />
            </div>
            <div v-else class="border-t border-gray-100 p-6 dark:border-gray-700">
                <p class="text-sm text-gray-400">Aún no hay capturas registradas con ubicación para esta especie.</p>
            </div>
        </div>
    </div>
</template>
