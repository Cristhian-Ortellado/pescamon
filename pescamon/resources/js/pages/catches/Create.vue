<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';

interface Species {
    id: number;
    name: string;
    scientific_name: string;
    slug: string;
    rarity: string;
    average_weight_kg: number | null;
}

const props = defineProps<{ species: Species[] }>();

const form = useForm({
    species_id: '' as number | '',
    weight_kg: '',
    length_cm: '',
    caught_at: new Date().toISOString().slice(0, 16),
    method: 'spinning',
    water_body_type: 'river',
    bait_used: '',
    location_name: '',
    latitude: '',
    longitude: '',
    notes: '',
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);
const gpsState = ref<'idle' | 'detecting' | 'done'>('idle');

const methods = [
    { value: 'spinning', label: 'Spinning' },
    { value: 'fly', label: 'Mosca' },
    { value: 'bait_casting', label: 'Bait Casting' },
    { value: 'trolling', label: 'Trolling' },
    { value: 'other', label: 'Otro' },
];
const waters = [
    { value: 'river', label: 'Río' },
    { value: 'lake', label: 'Lago' },
    { value: 'sea', label: 'Mar' },
    { value: 'reservoir', label: 'Embalse' },
    { value: 'other', label: 'Otro' },
];

const selectedSpecies = computed(() => props.species.find((s) => s.id === Number(form.species_id)));

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];

    if (!file) {
return;
}

    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
}

function detectGps() {
    if (!navigator.geolocation) {
return;
}

    gpsState.value = 'detecting';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.latitude = pos.coords.latitude.toFixed(7);
            form.longitude = pos.coords.longitude.toFixed(7);
            gpsState.value = 'done';
        },
        () => {
            gpsState.value = 'idle';
        },
    );
}

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/dashboard');
    }
}

function save() {
    form.post('/catches', { forceFormData: true });
}
</script>

<template>
    <Head title="Nueva captura" />

    <div class="appbar-pad">
        <div class="top-gap" />
        <div class="appbar">
            <button class="icon-btn" @click="goBack()"><Icon name="x" :size="23" color="var(--icon-muted)" /></button>
            <span class="title">Nueva captura</span>
        </div>
    </div>

    <div class="body">
        <!-- photo -->
        <label class="photo">
            <img v-if="imagePreview" :src="imagePreview" class="photo-img" alt="captura" />
            <template v-else>
                <Icon name="camera" :size="26" color="var(--text-faint)" />
                <span class="photo-label">Toca para añadir foto</span>
            </template>
            <input type="file" accept="image/jpeg,image/png" class="file-input" @change="onImageChange" />
        </label>
        <p v-if="form.errors.image" class="err">{{ form.errors.image }}</p>

        <!-- record banner -->
        <div v-if="selectedSpecies" class="record-banner">
            <Icon name="fish" :size="20" color="var(--accent-pdx)" />
            <div class="rb-text">
                <b>{{ selectedSpecies.name }}</b>
                <span v-if="selectedSpecies.average_weight_kg"> · promedio {{ selectedSpecies.average_weight_kg }} kg</span>
            </div>
            <RarityBadge :rarity="selectedSpecies.rarity" variant="outline" />
        </div>

        <!-- especie -->
        <div class="field">
            <div class="label">Especie</div>
            <div class="select-wrap">
                <Icon name="fish" :size="20" color="var(--teal)" />
                <select v-model="form.species_id" class="native-select">
                    <option value="" disabled>Selecciona una especie</option>
                    <option v-for="s in species" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <Icon name="chevron-down" :size="18" color="var(--text-faint)" />
            </div>
            <p v-if="form.errors.species_id" class="err">{{ form.errors.species_id }}</p>
        </div>

        <!-- peso / longitud -->
        <div class="two">
            <div class="field">
                <div class="label">Peso</div>
                <label class="num">
                    <input v-model="form.weight_kg" type="number" step="0.1" inputmode="decimal" placeholder="0.0" />
                    <span class="unit">kg</span>
                </label>
                <p v-if="form.errors.weight_kg" class="err">{{ form.errors.weight_kg }}</p>
            </div>
            <div class="field">
                <div class="label">Longitud</div>
                <label class="num">
                    <input v-model="form.length_cm" type="number" inputmode="decimal" placeholder="—" />
                    <span class="unit">cm</span>
                </label>
            </div>
        </div>

        <!-- fecha -->
        <div class="field">
            <div class="label">Fecha y hora</div>
            <input v-model="form.caught_at" type="datetime-local" class="text-input" />
            <p v-if="form.errors.caught_at" class="err">{{ form.errors.caught_at }}</p>
        </div>

        <!-- ubicación -->
        <div class="field">
            <div class="label">Ubicación</div>
            <input v-model="form.location_name" type="text" class="text-input" placeholder="Ej. Embalse del Atazar" />
            <button type="button" class="gps" :class="{ on: gpsState === 'done' }" @click="detectGps">
                <Icon name="locate-fixed" :size="14" :color="gpsState === 'done' ? 'var(--teal)' : 'var(--text-faint)'" />
                <span v-if="gpsState === 'idle'">Detectar con GPS</span>
                <span v-else-if="gpsState === 'detecting'">Detectando…</span>
                <span v-else>Ubicación detectada ✓</span>
            </button>
        </div>

        <!-- método -->
        <div class="field">
            <div class="label">Método</div>
            <div class="chips">
                <button
                    v-for="m in methods"
                    :key="m.value"
                    type="button"
                    class="chip"
                    :class="{ active: form.method === m.value }"
                    @click="form.method = m.value"
                >
                    {{ m.label }}
                </button>
            </div>
        </div>

        <!-- agua -->
        <div class="field">
            <div class="label">Tipo de agua</div>
            <div class="chips">
                <button
                    v-for="w in waters"
                    :key="w.value"
                    type="button"
                    class="chip"
                    :class="{ active: form.water_body_type === w.value }"
                    @click="form.water_body_type = w.value"
                >
                    {{ w.label }}
                </button>
            </div>
        </div>

        <!-- cebo / notas -->
        <div class="field">
            <div class="label">Cebo (opcional)</div>
            <input v-model="form.bait_used" type="text" class="text-input" placeholder="Ej. vinilo, mosca seca…" />
        </div>
        <div class="field">
            <div class="label">Notas (opcional)</div>
            <textarea v-model="form.notes" class="text-input" rows="2" placeholder="Condiciones, anécdota…" />
        </div>
    </div>

    <div class="save-bar">
        <button class="save" :disabled="form.processing" @click="save">
            <Icon name="check" :size="20" /> {{ form.processing ? 'Guardando…' : 'Guardar captura' }}
        </button>
    </div>
</template>

<style scoped>
.appbar-pad {
    padding: 0 18px;
}
.top-gap {
    height: 16px;
}
.appbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 0 10px;
}
.icon-btn {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 19px;
    color: var(--text);
}
.body {
    padding: 4px 18px;
}
.photo {
    position: relative;
    height: 160px;
    border-radius: 18px;
    overflow: hidden;
    border: 1px dashed var(--track-border);
    background: var(--surface-locked);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}
.photo-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.photo-label {
    font-size: 12.5px;
    color: var(--text-faint);
}
.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.record-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    background: linear-gradient(90deg, rgba(245, 165, 36, 0.2), rgba(245, 165, 36, 0.06));
    border: 1px solid rgba(245, 165, 36, 0.5);
    border-radius: 14px;
    padding: 11px 13px;
}
.rb-text {
    flex: 1;
    font-size: 12.5px;
    color: var(--text);
}
.rb-text b {
    font-family: var(--font-display);
    color: var(--accent-pdx);
}
.rb-text span {
    color: #cdb98a;
}
.field {
    margin-top: 16px;
}
.field .label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin-bottom: 7px;
}
.select-wrap {
    display: flex;
    align-items: center;
    gap: 11px;
    background: var(--surface-alt);
    border: 1px solid var(--field-border);
    border-radius: 13px;
    padding: 4px 14px;
}
.native-select {
    flex: 1;
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    padding: 11px 0;
    appearance: none;
}
.native-select option {
    color: #15303d;
}
.two {
    display: flex;
    gap: 11px;
}
.two .field {
    flex: 1;
}
.num {
    display: flex;
    align-items: baseline;
    gap: 5px;
    background: var(--surface-alt);
    border: 1px solid var(--field-border);
    border-radius: 13px;
    padding: 13px 14px;
}
.num input {
    width: 100%;
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 18px;
}
.num input::-webkit-outer-spin-button,
.num input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.unit {
    color: var(--text-faint);
    font-size: 13px;
}
.text-input {
    width: 100%;
    background: var(--surface-alt);
    border: 1px solid var(--field-border);
    border-radius: 13px;
    padding: 13px 14px;
    color: var(--text);
    font-family: inherit;
    font-size: 14px;
    outline: none;
    resize: vertical;
}
.gps {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-faint);
    background: none;
    border: 0;
}
.gps.on {
    color: var(--teal);
}
.chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.chip {
    padding: 9px 15px;
    border-radius: 999px;
    border: 1px solid var(--border-strong);
    color: var(--icon-muted);
    font-size: 13px;
    font-weight: 600;
    background: none;
    font-family: inherit;
}
.chip.active {
    background: rgba(45, 212, 191, 0.16);
    border-color: rgba(45, 212, 191, 0.5);
    color: var(--teal-chip-text);
}
.err {
    color: #f08a90;
    font-size: 11.5px;
    margin-top: 5px;
}
.save-bar {
    position: sticky;
    bottom: 0;
    padding: 14px 18px calc(20px + env(safe-area-inset-bottom));
    background: linear-gradient(180deg, transparent, var(--bg-frame) 28%);
}
.save {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    padding: 15px;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.45);
}
.save:disabled {
    opacity: 0.6;
}
</style>
