<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import FishPhoto from '@/components/pescadex/FishPhoto.vue';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import { rarityMeta, storageUrl, timeAgo } from '@/lib/pescadex';

interface MapCatch {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    weight_kg: number;
    image_path: string | null;
    location_name: string | null;
    caught_at: string;
    latitude: number;
    longitude: number;
    user: { id: number; name: string; avatar: string | null };
}

defineProps<{
    userLevel: number;
    userXp: number;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const query = ref('');
const locating = ref(false);
const loadingData = ref(false);

// Catches currently loaded for the visible map viewport. Refilled by
// fetchViewport() as the user pans/zooms — not shipped with the page.
const loaded = ref<MapCatch[]>([]);

// Catches matching the search box (species name, zone or angler), scoped to
// what is loaded for the current viewport. Empty query shows everything loaded.
const filteredCatches = computed(() => {
    const q = query.value.trim().toLowerCase();
    const speciesId = selectedSpeciesId.value;

    return loaded.value.filter((c) => {
        if (speciesId !== null && c.species.id !== speciesId) {
            return false;
        }

        if (!q) {
            return true;
        }

        return (
            c.species.name.toLowerCase().includes(q) ||
            (c.location_name?.toLowerCase().includes(q) ?? false) ||
            c.user.name.toLowerCase().includes(q)
        );
    });
});

const peek = computed(() => filteredCatches.value[0] ?? null);

// Leaflet is loaded lazily; keep the references so the locate button can reuse
// the same map instance after mount. (leaflet ships no types → typed as any.)

let leaflet: any = null;

let mapInstance: any = null;

let userMarker: any = null;

// Catch id → marker, so the viewport sync and search can reuse / toggle each
// marker without recreating it on overlapping pans.

const markerStore = new Map<number, { c: MapCatch; marker: any }>();

const rarityColor: Record<string, string> = {
    common: '#64748b',
    uncommon: '#34d399',
    rare: '#a335ee',
    legendary: '#FF8000',
};

// 'Especie' filter: pick one of the species currently on the map.
const speciesOpen = ref(false);
const selectedSpeciesId = ref<number | null>(null);

const speciesOptions = computed(() => {
    const seen = new Map<number, { id: number; name: string; rarity: string }>();
    loaded.value.forEach((c) => {
        if (!seen.has(c.species.id)) {
            seen.set(c.species.id, {
                id: c.species.id,
                name: c.species.name,
                rarity: c.species.rarity,
            });
        }
    });

    return Array.from(seen.values()).sort((a, b) => a.name.localeCompare(b.name));
});

const selectedSpeciesName = computed(
    () => speciesOptions.value.find((s) => s.id === selectedSpeciesId.value)?.name ?? null,
);

const chipWrap = ref<HTMLElement | null>(null);
onClickOutside(chipWrap, () => {
    speciesOpen.value = false;
});

function selectSpecies(id: number | null) {
    selectedSpeciesId.value = id;
    speciesOpen.value = false;
}

function escapeHtml(value: string): string {
    return value.replace(
        /[&<>"']/g,
        (ch) =>
            ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[ch] as string,
    );
}

// Striped placeholder gradient matching FishPhoto, keyed by rarity.
function photoGradient(rarity: string): string {
    const [a, b] = rarityMeta(rarity).photo;

    return `repeating-linear-gradient(135deg, ${a}, ${a} 7px, ${b} 7px, ${b} 14px)`;
}

// Builds the Leaflet popup content shown when a catch marker is clicked:
// a small card with the fish photo, species/weight and the angler.
function buildPopup(c: MapCatch): HTMLElement {
    const meta = rarityMeta(c.species.rarity);
    const photo = storageUrl(c.image_path);
    const mediaStyle = photo
        ? `background-image:url('${photo}')`
        : `background:${photoGradient(c.species.rarity)}`;
    const avatarStyle = c.user.avatar
        ? `background-image:url('${c.user.avatar}')`
        : 'background:repeating-linear-gradient(135deg,var(--avatar-a),var(--avatar-a) 4px,var(--avatar-b) 4px,var(--avatar-b) 8px)';

    const el = document.createElement('div');
    el.className = 'map-pop';
    el.innerHTML = `
        <div class="mp-media" style="${mediaStyle}"></div>
        <div class="mp-body">
            <div class="mp-row">
                <span class="mp-name">${escapeHtml(c.species.name)}</span>
                <span class="mp-rarity" style="color:${meta.color};border-color:${meta.color}">${meta.short}</span>
            </div>
            <div class="mp-weight">${c.weight_kg} kg</div>
            <div class="mp-user">
                <span class="mp-avatar" style="${avatarStyle}"></span>
                <span class="mp-uname">${escapeHtml(c.user.name)}</span>
                <span class="mp-time">· ${timeAgo(c.caught_at)}</span>
            </div>
            <a class="mp-link" href="/catches/${c.id}">Ver captura →</a>
        </div>`;

    // Keep navigation inside the SPA instead of a full page reload.
    el.querySelector('.mp-link')?.addEventListener('click', (e) => {
        e.preventDefault();
        router.visit(`/catches/${c.id}`);
    });

    return el;
}

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
    if (!mapInstance || !leaflet || !('geolocation' in navigator)) {
        return;
    }

    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const latlng: [number, number] = [
                pos.coords.latitude,
                pos.coords.longitude,
            ];

            if (!userMarker) {
                userMarker = leaflet
                    .marker(latlng, {
                        icon: userLocationIcon(),
                        zIndexOffset: 1000,
                    })
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

// Build a single catch marker (teardrop pin coloured by rarity, lazy popup).
function createMarker(c: MapCatch) {
    const color = rarityColor[c.species.rarity] ?? '#64748b';
    const icon = leaflet.divIcon({
        html: `<span style="display:block;width:20px;height:20px;border-radius:50% 50% 50% 2px;transform:rotate(45deg);background:${color};border:2.5px solid #eaf6ff;box-shadow:0 6px 14px rgba(0,0,0,.4)"></span>`,
        className: '',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
    });
    const marker = leaflet.marker([c.latitude, c.longitude], { icon });
    // Show a small info popup (user + catch) instead of navigating away.
    // The content is built lazily the first time the marker is opened.
    marker.bindPopup(() => buildPopup(c), {
        closeButton: true,
        minWidth: 210,
        maxWidth: 230,
        className: 'map-pop-wrap',
    });

    return marker;
}

// Show/hide markers already on the map according to the active search and
// species filter (does not touch the viewport-loaded set).
function applyFilter() {
    if (!mapInstance) {
        return;
    }

    const visibleIds = new Set(filteredCatches.value.map((c) => c.id));
    markerStore.forEach(({ marker }, id) => {
        const shouldShow = visibleIds.has(id);
        const onMap = mapInstance.hasLayer(marker);

        if (shouldShow && !onMap) {
            marker.addTo(mapInstance);
        } else if (!shouldShow && onMap) {
            mapInstance.removeLayer(marker);
        }
    });
}

// Reconcile the marker set with the catches returned for the current viewport:
// drop markers that fell out of the response, create the newcomers, reuse the
// rest. Keeps memory bounded to roughly what is on screen.
function syncMarkers(list: MapCatch[]) {
    loaded.value = list;
    const ids = new Set(list.map((c) => c.id));

    markerStore.forEach(({ marker }, id) => {
        if (!ids.has(id)) {
            mapInstance.removeLayer(marker);
            markerStore.delete(id);
        }
    });

    list.forEach((c) => {
        if (!markerStore.has(c.id)) {
            markerStore.set(c.id, { c, marker: createMarker(c) });
        }
    });

    applyFilter();
}

// Fetch the catches inside the current map bounds. In-flight requests are
// aborted so only the latest viewport wins.
let fetchAbort: AbortController | null = null;
let fetchTimer: ReturnType<typeof setTimeout> | undefined;

async function fetchViewport() {
    if (!mapInstance) {
        return;
    }

    const b = mapInstance.getBounds();
    const params = new URLSearchParams({
        north: b.getNorth().toFixed(6),
        south: b.getSouth().toFixed(6),
        east: b.getEast().toFixed(6),
        west: b.getWest().toFixed(6),
    });

    fetchAbort?.abort();
    fetchAbort = new AbortController();
    loadingData.value = true;

    try {
        const res = await fetch(`/map/catches?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            signal: fetchAbort.signal,
        });

        if (!res.ok) {
            return;
        }

        const data = await res.json();
        syncMarkers(data.catches as MapCatch[]);
    } catch {
        // Aborted (superseded request) or network error → keep current markers.
    } finally {
        loadingData.value = false;
    }
}

// Debounce viewport fetches so a pan/zoom gesture triggers a single request.
function scheduleFetch() {
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(fetchViewport, 300);
}

onMounted(async () => {
    if (!mapContainer.value) {
        return;
    }

    leaflet = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    mapInstance = leaflet
        .map(mapContainer.value, { zoomControl: false })
        .setView([40.4, -3.7], 5);

    leaflet
        .tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        })
        .addTo(mapInstance);

    // Refetch markers whenever the visible area settles after a pan/zoom.
    mapInstance.on('moveend', scheduleFetch);

    // Initial load for the default view, then center on the user's location
    // (which pans the map and triggers another debounced fetch for that area).
    fetchViewport();
    locateUser(true);
});

onUnmounted(() => {
    clearTimeout(fetchTimer);
    fetchAbort?.abort();
    mapInstance?.remove();
});

// Live search / species filter: toggle visibility of the loaded markers.
// No reframing — with viewport loading the matches are already on screen.
watch(filteredCatches, applyFilter);
</script>

<template>
    <Head title="Mapa" />

    <div class="map-screen">
        <div ref="mapContainer" class="map" />

        <!-- top overlay -->
        <div class="top">
            <div class="search">
                <Icon name="search" :size="19" color="var(--text-faint)" />
                <input
                    v-model="query"
                    type="text"
                    class="search-input"
                    placeholder="Buscar especie, zona o pescador…"
                />
                <button
                    v-if="query"
                    class="clear-btn"
                    aria-label="Limpiar"
                    @click="query = ''"
                >
                    <Icon name="x" :size="16" color="var(--text-faint)" />
                </button>
                <span v-else class="filter-btn"
                    ><Icon
                        name="sliders-horizontal"
                        :size="16"
                        color="var(--teal)"
                /></span>
            </div>
            <div class="chips">
                <div ref="chipWrap" class="chip-wrap">
                    <div class="chip" :class="{ active: selectedSpeciesId !== null }">
                        <button class="chip-label" @click="speciesOpen = !speciesOpen">
                            {{ selectedSpeciesName ?? 'Especie' }}
                        </button>
                        <button
                            v-if="selectedSpeciesId !== null"
                            class="chip-btn"
                            aria-label="Quitar filtro de especie"
                            @click.stop="selectSpecies(null)"
                        >
                            <Icon name="x" :size="13" />
                        </button>
                        <button
                            v-else
                            class="chip-btn"
                            aria-label="Abrir filtro de especie"
                            @click="speciesOpen = !speciesOpen"
                        >
                            <Icon name="chevron-down" :size="13" />
                        </button>
                    </div>

                    <div v-if="speciesOpen" class="dropdown">
                        <button
                            class="dd-item"
                            :class="{ sel: selectedSpeciesId === null }"
                            @click="selectSpecies(null)"
                        >
                            Todas las especies
                        </button>
                        <button
                            v-for="s in speciesOptions"
                            :key="s.id"
                            class="dd-item"
                            :class="{ sel: selectedSpeciesId === s.id }"
                            @click="selectSpecies(s.id)"
                        >
                            <span
                                class="dd-dot"
                                :style="{ background: rarityColor[s.rarity] ?? '#64748b' }"
                            />
                            {{ s.name }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- peek card -->
        <Link v-if="peek" :href="`/catches/${peek.id}`" class="peek">
            <div class="thumb">
                <FishPhoto :rarity="peek.species.rarity" :stripe="7" />
            </div>
            <div class="peek-body">
                <div class="peek-title">
                    <span class="name">{{ peek.species.name }}</span>
                    <RarityBadge
                        :rarity="peek.species.rarity"
                        variant="outline"
                    />
                </div>
                <div class="peek-sub">{{ timeAgo(peek.caught_at) }}</div>
            </div>
            <Icon name="chevron-right" :size="20" color="var(--text-faint)" />
        </Link>
        <div v-else class="peek empty">
            <Icon name="map-pin" :size="20" color="var(--text-faint)" />
            <span>No hay capturas en esta zona. Mueve o aleja el mapa para ver más.</span>
        </div>

        <!-- viewport loading indicator -->
        <div v-if="loadingData" class="loading-pill">
            <span class="spinner" />
            Cargando capturas…
        </div>

        <!-- locate button -->
        <button
            class="locate-btn"
            :class="{ active: locating }"
            aria-label="Centrar en mi ubicación"
            @click="locateUser(true)"
        >
            <Icon
                name="locate-fixed"
                :size="21"
                :color="locating ? 'var(--text-faint)' : 'var(--teal)'"
            />
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
.search-input {
    flex: 1;
    min-width: 0;
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: inherit;
    font-size: 14.5px;
    padding: 0;
}
.search-input::placeholder {
    color: var(--text-muted);
}
.clear-btn {
    margin-left: auto;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: none;
    border: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
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
    gap: 4px;
    padding: 7px 12px;
    background: var(--glass-bg);
    border: 1px solid var(--border-strong);
    color: var(--text-soft);
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
}
.chip.active {
    background: var(--glass-bg);
    border-color: rgba(45, 212, 191, 0.5);
    color: var(--teal-chip-text);
}
.chip-label,
.chip-btn {
    display: flex;
    align-items: center;
    background: none;
    border: 0;
    padding: 0;
    color: inherit;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    cursor: pointer;
}
.chip-btn {
    margin-left: 1px;
}
.chip.active .chip-btn {
    color: var(--teal);
}
.chip-wrap {
    position: relative;
}
.dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 15;
    min-width: 190px;
    max-height: 260px;
    overflow-y: auto;
    background: var(--glass-strong);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.45);
    padding: 6px;
}
.dd-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    text-align: left;
    background: none;
    border: 0;
    color: var(--text-soft);
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 10px;
    border-radius: 9px;
    cursor: pointer;
}
.dd-item:hover {
    background: var(--glass-bg);
}
.dd-item.sel {
    color: var(--teal);
    background: rgba(45, 212, 191, 0.12);
}
.dd-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex: none;
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
.loading-pill {
    position: absolute;
    top: 118px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 13;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    background: var(--glass-strong);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 999px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
    color: var(--text-soft);
    font-size: 12px;
    font-weight: 600;
}
.loading-pill .spinner {
    width: 13px;
    height: 13px;
    border-radius: 50%;
    border: 2px solid var(--glass-border);
    border-top-color: var(--teal);
    animation: map-spin 0.7s linear infinite;
}
@keyframes map-spin {
    to {
        transform: rotate(360deg);
    }
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

<!-- Global: Leaflet popups render outside the component's scoped DOM. -->
<style>
.map-pop-wrap .leaflet-popup-content-wrapper {
    background: var(--glass-strong, #0d2331);
    color: var(--text, #eaf6ff);
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.12));
    border-radius: 16px;
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.45);
    padding: 0;
    overflow: hidden;
}
.map-pop-wrap .leaflet-popup-content {
    margin: 0;
    width: 210px !important;
}
.map-pop-wrap .leaflet-popup-tip {
    background: var(--glass-strong, #0d2331);
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.12));
}
.map-pop-wrap a.leaflet-popup-close-button {
    color: var(--text-faint, #7d97a8);
    padding: 6px 7px 0 0;
}

.map-pop {
    display: flex;
    flex-direction: column;
    font-family: inherit;
}
.map-pop .mp-media {
    height: 96px;
    width: 100%;
    background-size: cover;
    background-position: center;
}
.map-pop .mp-body {
    padding: 10px 12px 12px;
}
.map-pop .mp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.map-pop .mp-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--text, #eaf6ff);
}
.map-pop .mp-rarity {
    flex: none;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.03em;
    border: 1px solid;
    border-radius: 999px;
    padding: 1px 7px;
}
.map-pop .mp-weight {
    font-weight: 600;
    font-size: 12.5px;
    color: var(--teal, #2dd4bf);
    margin-top: 3px;
}
.map-pop .mp-user {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 8px;
}
.map-pop .mp-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    flex: none;
    background-size: cover;
    background-position: center;
}
.map-pop .mp-uname {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-soft, #c4d6e2);
}
.map-pop .mp-time {
    font-size: 11px;
    color: var(--text-faint, #7d97a8);
}
.map-pop a.mp-link {
    display: inline-block;
    margin-top: 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--accent-pdx, #f5a524);
    text-decoration: none;
}
</style>
