<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useAppearance } from '@/composables/useAppearance';
import Icon from '@/components/pescadex/Icon.vue';

interface Preferences {
    location_privacy: 'exact' | 'approximate' | 'hidden';
    unit_preference: 'metric' | 'imperial';
}

const props = defineProps<{ preferences: Preferences }>();

const { resolvedAppearance, updateAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

const privacy = ref<Preferences['location_privacy']>(props.preferences.location_privacy);
const units = ref<Preferences['unit_preference']>(props.preferences.unit_preference);

const privacyOptions = [
    { id: 'exact', icon: 'map-pin', title: 'Exacta', desc: 'Muestra el punto real de captura' },
    { id: 'approximate', icon: 'locate-fixed', title: 'Aproximada', desc: 'Redondea a ~1 km · protege tus spots', badge: 'RECOMENDADA' },
    { id: 'hidden', icon: 'eye-off', title: 'Oculta', desc: 'No comparte ubicación de tus capturas' },
] as const;

// Client-only preferences (no backend column yet).
const notifNearby = ref(true);
const notifRanking = ref(false);

function persist() {
    router.patch('/settings/preferences', {
        location_privacy: privacy.value,
        unit_preference: units.value,
    }, { preserveScroll: true });
}
function setPrivacy(id: Preferences['location_privacy']) {
    privacy.value = id;
    persist();
}
function setUnits(u: Preferences['unit_preference']) {
    units.value = u;
    persist();
}
function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Ajustes" />

    <div class="head">
        <div class="top-gap" />
        <div class="app-bar">
            <button class="ic" @click="router.visit('/profile')"><Icon name="arrow-left" :size="23" color="var(--icon-muted)" /></button>
            <span class="title">Ajustes</span>
        </div>
    </div>

    <!-- appearance -->
    <div class="group-label plain">Apariencia</div>
    <div class="card">
        <div class="row">
            <span class="row-label">Tema</span>
            <div class="segmented">
                <button :class="{ on: isDark }" @click="updateAppearance('dark')">Oscuro</button>
                <button :class="{ on: !isDark }" @click="updateAppearance('light')">Claro</button>
            </div>
        </div>
    </div>

    <!-- location privacy -->
    <div class="group-label"><Icon name="shield" :size="14" /> Privacidad de ubicación</div>
    <div class="card">
        <button
            v-for="(opt, i) in privacyOptions"
            :key="opt.id"
            class="row option"
            :class="{ active: privacy === opt.id, divider: i < privacyOptions.length - 1 }"
            @click="setPrivacy(opt.id)"
        >
            <Icon :name="opt.icon" :size="19" :color="privacy === opt.id ? 'var(--teal)' : 'var(--text-faint)'" />
            <div class="row-text">
                <div class="row-title">
                    {{ opt.title }}
                    <span v-if="opt.badge" class="badge">{{ opt.badge }}</span>
                </div>
                <div class="row-desc">{{ opt.desc }}</div>
            </div>
            <span class="radio" :class="{ on: privacy === opt.id }">
                <span v-if="privacy === opt.id" class="radio-dot" />
            </span>
        </button>
    </div>

    <!-- units -->
    <div class="group-label plain">Unidades</div>
    <div class="card">
        <div class="row">
            <span class="row-label">Sistema</span>
            <div class="segmented">
                <button :class="{ on: units === 'metric' }" @click="setUnits('metric')">kg / cm</button>
                <button :class="{ on: units === 'imperial' }" @click="setUnits('imperial')">lb / in</button>
            </div>
        </div>
    </div>

    <!-- notifications -->
    <div class="group-label plain">Notificaciones</div>
    <div class="card">
        <div class="row divider">
            <span class="row-label">Capturas cercanas</span>
            <button class="toggle" :class="{ on: notifNearby }" @click="notifNearby = !notifNearby"><span class="knob" /></button>
        </div>
        <div class="row">
            <span class="row-label">Cambios de ranking</span>
            <button class="toggle" :class="{ on: notifRanking }" @click="notifRanking = !notifRanking"><span class="knob" /></button>
        </div>
    </div>

    <!-- account -->
    <div class="group-label plain">Cuenta</div>
    <div class="card">
        <Link href="/settings/profile" class="row divider">
            <Icon name="user" :size="18" color="var(--icon-muted)" />
            <span class="row-label">Editar perfil</span>
            <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
        </Link>
        <Link href="/settings/security" class="row divider">
            <Icon name="shield" :size="18" color="var(--icon-muted)" />
            <span class="row-label">Seguridad</span>
            <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
        </Link>
        <button class="row logout" @click="logout">
            <Icon name="arrow-left" :size="18" color="#e2737a" />
            <span class="row-label danger">Cerrar sesión</span>
        </button>
    </div>

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 20px;
}
.top-gap {
    height: 16px;
}
.app-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 4px 0 8px;
}
.ic {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
.group-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-faint);
    padding: 14px 20px 10px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.group-label.plain {
    padding-top: 18px;
}
.card {
    margin: 0 20px;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 16px;
    overflow: hidden;
}
.row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 15px;
    width: 100%;
    background: none;
    border: 0;
    color: var(--text);
    font-family: inherit;
    text-align: left;
}
.row.divider {
    border-bottom: 1px solid var(--divider);
}
.option.active {
    background: rgba(45, 212, 191, 0.08);
}
.row-text {
    flex: 1;
}
.row-title {
    font-size: 14px;
    font-weight: 600;
}
.badge {
    font-size: 10px;
    font-weight: 700;
    color: var(--teal);
    border: 1px solid #1f6f6a;
    border-radius: 5px;
    padding: 1px 5px;
    margin-left: 4px;
}
.row-desc {
    font-size: 11.5px;
    color: var(--text-muted);
}
.row-label {
    flex: 1;
    font-size: 14px;
    font-weight: 600;
}
.row-label.danger {
    color: #e2737a;
}
.radio {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #2c4a5e;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.radio.on {
    border-color: var(--teal);
}
.radio-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: var(--teal);
}
.segmented {
    display: flex;
    background: var(--track-bg);
    border: 1px solid var(--track-border);
    border-radius: 999px;
    padding: 3px;
}
.segmented button {
    padding: 5px 14px;
    border-radius: 999px;
    border: 0;
    background: none;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--icon-muted);
    font-family: inherit;
}
.segmented button.on {
    background: var(--teal);
    color: var(--teal-ink);
    font-weight: 700;
}
.toggle {
    width: 46px;
    height: 27px;
    border-radius: 999px;
    background: var(--track-border);
    border: 0;
    position: relative;
    flex: none;
}
.toggle.on {
    background: var(--teal);
}
.knob {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 21px;
    height: 21px;
    border-radius: 50%;
    background: var(--text-faint);
    transition: transform 0.15s ease, background 0.15s ease;
}
.toggle.on .knob {
    transform: translateX(19px);
    background: #fff;
}
.logout {
    cursor: pointer;
}
.bottom-spacer {
    height: 40px;
}
</style>
