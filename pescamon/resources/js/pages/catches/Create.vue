<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Species {
    id: number;
    name: string;
    scientific_name: string;
    slug: string;
    rarity: string;
    average_weight_kg: number | null;
}

const props = defineProps<{
    species: Species[];
}>();

const form = useForm({
    species_id: '',
    weight_kg: '',
    length_cm: '',
    caught_at: new Date().toISOString().slice(0, 16),
    method: 'other',
    water_body_type: 'other',
    bait_used: '',
    location_name: '',
    latitude: '',
    longitude: '',
    notes: '',
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);
const gpsDetecting = ref(false);

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
}

function detectGps() {
    gpsDetecting.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.latitude = pos.coords.latitude.toFixed(7);
            form.longitude = pos.coords.longitude.toFixed(7);
            gpsDetecting.value = false;
        },
        () => {
            gpsDetecting.value = false;
        },
    );
}

const selectedSpecies = computed(() => props.species.find((s) => s.id === Number(form.species_id)));

const rarityBadge: Record<string, string> = {
    common: 'bg-gray-100 text-gray-700',
    uncommon: 'bg-emerald-100 text-emerald-700',
    rare: 'bg-blue-100 text-blue-700',
    legendary: 'bg-amber-100 text-amber-700',
};

function submit() {
    form.post('/catches', {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Nueva Captura" />

    <div class="mx-auto max-w-2xl px-4 py-8">
        <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Registrar Captura</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Image upload -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto de la captura *</label>
                <div
                    class="flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-4 transition hover:border-blue-400 dark:border-gray-600"
                    @click="($refs.fileInput as HTMLInputElement).click()"
                >
                    <img v-if="imagePreview" :src="imagePreview" class="max-h-64 rounded-lg object-cover" />
                    <template v-else>
                        <span class="text-4xl">📷</span>
                        <p class="mt-2 text-sm text-gray-500">Haz clic para subir una foto (JPG/PNG, máx 10 MB)</p>
                    </template>
                </div>
                <input ref="fileInput" type="file" accept="image/jpeg,image/png" class="hidden" @change="onImageChange" />
                <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
            </div>

            <!-- Species selector -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Especie *</label>
                <select v-model="form.species_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">-- Selecciona una especie --</option>
                    <option v-for="s in species" :key="s.id" :value="s.id">{{ s.name }} ({{ s.scientific_name }})</option>
                </select>
                <div v-if="selectedSpecies" class="mt-1 flex items-center gap-2">
                    <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold capitalize', rarityBadge[selectedSpecies.rarity]]">
                        {{ selectedSpecies.rarity }}
                    </span>
                    <span v-if="selectedSpecies.average_weight_kg" class="text-xs text-gray-500">
                        Peso promedio: {{ selectedSpecies.average_weight_kg }} kg
                    </span>
                </div>
                <p v-if="form.errors.species_id" class="mt-1 text-sm text-red-600">{{ form.errors.species_id }}</p>
            </div>

            <!-- Weight & Length -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Peso (kg) *</label>
                    <input
                        v-model="form.weight_kg"
                        type="number"
                        step="0.001"
                        min="0"
                        placeholder="ej. 2.350"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                    <p v-if="form.errors.weight_kg" class="mt-1 text-sm text-red-600">{{ form.errors.weight_kg }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitud (cm)</label>
                    <input
                        v-model="form.length_cm"
                        type="number"
                        step="0.1"
                        min="0"
                        placeholder="ej. 45.5"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>

            <!-- Date & Method -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha y hora *</label>
                    <input
                        v-model="form.caught_at"
                        type="datetime-local"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                    <p v-if="form.errors.caught_at" class="mt-1 text-sm text-red-600">{{ form.errors.caught_at }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Técnica *</label>
                    <select v-model="form.method" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="spinning">Spinning</option>
                        <option value="fly">Mosca (Fly)</option>
                        <option value="bait_casting">Bait Casting</option>
                        <option value="trolling">Trolling</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
            </div>

            <!-- Water body & Bait -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de agua *</label>
                    <select v-model="form.water_body_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="river">Río</option>
                        <option value="lake">Lago</option>
                        <option value="sea">Mar</option>
                        <option value="reservoir">Embalse</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cebo / señuelo</label>
                    <input
                        v-model="form.bait_used"
                        type="text"
                        placeholder="ej. Gusano, Cuchara plateada..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación</label>
                <div class="flex gap-2">
                    <input
                        v-model="form.location_name"
                        type="text"
                        placeholder="Nombre del lugar (opcional)"
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                    <button
                        type="button"
                        @click="detectGps"
                        :disabled="gpsDetecting"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        {{ gpsDetecting ? '...' : '📍 GPS' }}
                    </button>
                </div>
                <div v-if="form.latitude && form.longitude" class="mt-1 text-xs text-emerald-600">
                    ✓ Coordenadas: {{ form.latitude }}, {{ form.longitude }}
                </div>
                <p class="mt-1 text-xs text-gray-400">
                    Si la foto tiene GPS en los metadatos EXIF, se extraerá automáticamente.
                </p>
            </div>

            <!-- Notes -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Condiciones del agua, hora del día, anécdotas..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                />
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                >
                    {{ form.processing ? 'Enviando...' : 'Registrar captura' }}
                </button>
                <p class="text-xs text-gray-500">La captura quedará pendiente de verificación.</p>
            </div>
        </form>
    </div>
</template>
