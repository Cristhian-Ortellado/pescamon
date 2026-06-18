<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import XpBar from '@/components/XpBar.vue';

interface MapCatch {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    caught_at: string;
    latitude: number;
    longitude: number;
}

const props = defineProps<{
    mapCatches: MapCatch[];
    userLevel: number;
    userXp: number;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const selectedCatch = ref<MapCatch | null>(null);

const rarityColor: Record<string, string> = {
    common: '#6B7280',
    uncommon: '#10B981',
    rare: '#3B82F6',
    legendary: '#F59E0B',
};

onMounted(async () => {
    if (!mapContainer.value) return;

    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    // Fix default marker icons for bundlers
    delete (L.Icon.Default.prototype as unknown as Record<string, unknown>)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    const map = L.map(mapContainer.value).setView([20, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(map);

    props.mapCatches.forEach((c) => {
        const color = rarityColor[c.species.rarity] ?? '#6B7280';

        const icon = L.divIcon({
            html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.4)"></div>`,
            className: '',
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });

        L.marker([c.latitude, c.longitude], { icon })
            .addTo(map)
            .bindPopup(
                `<b>${c.species.name}</b><br><small>${new Date(c.caught_at).toLocaleDateString('es-ES')}</small><br>
                <a href="/catches/${c.id}" class="text-blue-600 underline text-xs">Ver captura →</a>`,
            );
    });
});
</script>

<template>
    <Head title="Mapa de Capturas" />

    <div class="flex h-screen flex-col">
        <!-- Header bar -->
        <div class="flex items-center justify-between bg-white px-4 py-2 shadow dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <span class="text-xl font-bold text-blue-600">🎣 PescaDex</span>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                    Nv. {{ userLevel }}
                </span>
            </div>

            <div class="hidden w-48 md:block">
                <XpBar :xp="userXp" :level="userLevel" />
            </div>

            <nav class="flex items-center gap-3 text-sm">
                <Link href="/catches/create" class="rounded-lg bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700"> + Captura </Link>
                <Link href="/profile" class="text-gray-600 hover:text-blue-600 dark:text-gray-300">Perfil</Link>
                <Link href="/leaderboard" class="text-gray-600 hover:text-blue-600 dark:text-gray-300">Ranking</Link>
            </nav>
        </div>

        <!-- Map fills remaining space -->
        <div ref="mapContainer" class="flex-1" />

        <!-- Legend -->
        <div class="absolute bottom-8 left-4 z-[1000] rounded-lg bg-white/90 p-3 text-xs shadow backdrop-blur dark:bg-gray-800/90">
            <p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Rareza</p>
            <div v-for="(color, rarity) in rarityColor" :key="rarity" class="flex items-center gap-1.5">
                <span class="inline-block h-3 w-3 rounded-full" :style="{ background: color }" />
                <span class="capitalize text-gray-600 dark:text-gray-300">{{ rarity }}</span>
            </div>
        </div>

        <!-- Stats -->
        <div class="absolute right-4 top-16 z-[1000] rounded-lg bg-white/90 p-3 text-xs shadow backdrop-blur dark:bg-gray-800/90">
            <p class="font-semibold text-gray-700 dark:text-gray-200">{{ mapCatches.length }} capturas en el mapa</p>
        </div>
    </div>
</template>
