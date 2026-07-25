<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import FishPhoto from '@/components/pescadex/FishPhoto.vue';
import { rarityMeta, timeAgo } from '@/lib/pescadex';

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
const peek = computed(() => props.mapCatches[0] ?? null);
const locating = ref(false);

// Leaflet is loaded lazily; keep the references so the locate button can reuse
// the same map instance after mount. (leaflet ships no types → typed as any.)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let leaflet: any = null;
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let mapInstance: any = null;
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let userMarker: any = null;

const rarityColor: Record<string, string> = {
    common: '#64748b',
    uncommon: '#34d399',
    rare: '#a335ee',
    legendary: '#FF8000',
};

const filters = ['Especie', 'Peso mín', 'Método'];

function userLocationIcon() {
    return leaflet.divIcon({
        html: '<span style="display:block;width:16px;height:16px;border-radius:50%;background:#2d9bf0;border:3px solid #fff;box-shadow:0 0 0 5px rgba(45,155,240,.25)"></span>',
        className: '',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });
}

// Center the map on the user's current (browser) location and drop a marker.
// Silently keeps the current view if permission is denied or unavailable.
function locateUser(recenter = true) {
    if (!mapInstance || !leaflet || !('geolocation' in navigator)) return;

    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const latlng: [number, number] = [pos.coords.latitude, pos.coords.longitude];

            if (!userMarker) {
                userMarker = leaflet
                    .marker(latlng, { icon: userLocationIcon(), zIndexOffset: 1000 })
                    .addTo(mapInstance);
            } else {
                userMarker.setLatLng(latlng);
            }

            if (recenter) {
                mapInstance.setView(latlng, 14, { animate: true });
            }
            locating.value = false;
        },
        () => {
            locating.value = false;
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 },
    );
}

onMounted(async () => {
    if (!mapContainer.value) return;

    leaflet = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    mapInstance = leaflet.map(mapContainer.value, { zoomControl: false }).setView([40.4, -3.7], 5);

    leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(mapInstance);

    const points: [number, number][] = [];

    props.mapCatches.forEach((c) => {
        const color = rarityColor[c.species.rarity] ?? '#64748b';
        const icon = leaflet.divIcon({
            html: `<span style="display:block;width:20px;height:20px;border-radius:50% 50% 50% 2px;transform:rotate(45deg);background:${color};border:2.5px solid #eaf6ff;box-shadow:0 6px 14px rgba(0,0,0,.4)"></span>`,
            className: '',
            iconSize: [20, 20],
            iconAnchor: [10, 10],
        });
        const marker = leaflet.marker([c.latitude, c.longitude], { icon }).addTo(mapInstance);
        marker.on('click', () => router.visit(`/catches/${c.id}`));
        points.push([c.latitude, c.longitude]);
    });

    // Fallback framing until (and unless) geolocation resolves below.
    if (points.length) {
        mapInstance.fitBounds(points as [number, number][], { padding: [60, 60], maxZoom: 9 });
    }

    // Default: center on the authenticated user's current location.
    locateUser(true);
});
</script>

<template>
    <Head title="Mapa" />

    <div class="map-screen">
        <div ref="mapContainer" class="map" />

        <!-- top overlay -->
        <div class="top">
            <div class="search">
                <Icon name="search" :size="19" color="var(--text-faint)" />
                <span class="placeholder">Buscar especie o zona…</span>
                <span class="filter-btn"><Icon name="sliders-horizontal" :size="16" color="var(--teal)" /></span>
            </div>
            <div class="chips">
                <span v-for="(f, i) in filters" :key="f" class="chip" :class="{ active: i === 0 }">
                    {{ f }} <Icon name="chevron-down" :size="13" />
                </span>
            </div>
        </div>

        <!-- peek card -->
        <Link v-if="peek" :href="`/catches/${peek.id}`" class="peek">
            <div class="thumb"><FishPhoto :rarity="peek.species.rarity" :stripe="7" /></div>
            <div class="peek-body">
                <div class="peek-title">
                    <span class="name">{{ peek.species.name }}</span>
                    <RarityBadge :rarity="peek.species.rarity" variant="outline" />
                </div>
                <div class="peek-sub">{{ timeAgo(peek.caught_at) }}</div>
            </div>
            <Icon name="chevron-right" :size="20" color="var(--text-faint)" />
        </Link>
        <div v-else class="peek empty">
            <Icon name="map-pin" :size="20" color="var(--text-faint)" />
            <span>Aún no hay capturas en el mapa. ¡Registra la primera!</span>
        </div>

        <!-- locate button -->
        <button
            class="locate-btn"
            :class="{ active: locating }"
            aria-label="Centrar en mi ubicación"
            @click="locateUser(true)"
        >
            <Icon name="locate-fixed" :size="21" :color="locating ? 'var(--text-faint)' : 'var(--teal)'" />
        </button>

        <!-- FAB -->
        <Link href="/catches/create" class="fab">
            <Icon name="plus" :size="19" /> Registrar
        </Link>
    </div>
</template>

<style scoped>
.map-screen {
    position: absolute;
    inset: 0;
    overflow: hidden;
}
.map {
    position: absolute;
    inset: 0;
    background: var(--map-base);
    z-index: 1;
}
:deep(.leaflet-container) {
    background: var(--map-base);
}
.top {
    position: absolute;
    top: 14px;
    left: 14px;
    right: 14px;
    z-index: 12;
}
.search {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 12px 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}
.placeholder {
    color: var(--text-muted);
    font-size: 14.5px;
}
.filter-btn {
    margin-left: auto;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: rgba(45, 212, 191, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
}
.chips {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.chip {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 7px 12px;
    background: var(--glass-bg);
    border: 1px solid var(--border-strong);
    color: var(--text-soft);
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
}
.chip.active {
    background: rgba(45, 212, 191, 0.16);
    border-color: rgba(45, 212, 191, 0.5);
    color: var(--teal-chip-text);
}
.peek {
    position: absolute;
    left: 14px;
    right: 14px;
    bottom: 90px;
    z-index: 14;
    background: var(--glass-strong);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 12px;
    display: flex;
    gap: 12px;
    align-items: center;
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.45);
    color: var(--text);
}
.peek.empty {
    font-size: 12.5px;
    color: var(--text-muted);
}
.thumb {
    width: 50px;
    height: 50px;
    border-radius: 13px;
    overflow: hidden;
    flex: none;
}
.peek-body {
    flex: 1;
    min-width: 0;
}
.peek-title {
    display: flex;
    align-items: center;
    gap: 6px;
}
.peek-title .name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
}
.peek-sub {
    font-size: 11.5px;
    color: var(--text-faint);
    margin-top: 2px;
}
.locate-btn {
    position: absolute;
    right: 16px;
    bottom: 224px;
    z-index: 16;
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--glass-strong);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
    cursor: pointer;
}
.locate-btn.active {
    opacity: 0.7;
}
.fab {
    position: absolute;
    right: 16px;
    bottom: 160px;
    z-index: 16;
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14.5px;
    padding: 13px 18px;
    border-radius: 999px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.5);
}

@media (min-width: 1024px) {
    .peek {
        bottom: 20px;
    }
    .fab {
        bottom: 90px;
    }
    .locate-btn {
        bottom: 154px;
    }
}
</style>
