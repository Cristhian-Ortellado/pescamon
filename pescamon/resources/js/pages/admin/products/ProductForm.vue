<script setup lang="ts">
// The Inertia `useForm` object is a reactive store passed in by reference so
// this shared component can bind and update its fields — the standard Inertia
// pattern, hence vue/no-mutating-props is intentionally disabled here.
/* eslint-disable vue/no-mutating-props */
import type { InertiaForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';

export interface ExistingImage {
    id: number;
    url: string;
}

export interface ProductFormData {
    name: string;
    price: string | number;
    description: string;
    is_active: boolean;
    images: File[];
    removed_image_ids: number[];
}

const props = withDefaults(
    defineProps<{
        form: InertiaForm<ProductFormData>;
        submitLabel: string;
        existingImages?: ExistingImage[];
    }>(),
    { existingImages: () => [] },
);

const emit = defineEmits<{ (e: 'submit'): void }>();

// Newly picked files with their object-URL previews, kept in sync with
// form.images (the actual upload payload).
const picked = ref<{ file: File; url: string }[]>([]);

// Existing images not marked for removal.
const visibleExisting = computed(() =>
    props.existingImages.filter((img) => !props.form.removed_image_ids.includes(img.id)),
);

const hasAnyImage = computed(() => visibleExisting.value.length + picked.value.length > 0);

function onFilesChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);

    files.forEach((file) => picked.value.push({ file, url: URL.createObjectURL(file) }));
    props.form.images = picked.value.map((p) => p.file);

    // Allow re-selecting the same file later.
    input.value = '';
}

function removePicked(index: number) {
    URL.revokeObjectURL(picked.value[index].url);
    picked.value.splice(index, 1);
    props.form.images = picked.value.map((p) => p.file);
}

function removeExisting(id: number) {
    if (!props.form.removed_image_ids.includes(id)) {
        props.form.removed_image_ids.push(id);
    }
}

onBeforeUnmount(() => picked.value.forEach((p) => URL.revokeObjectURL(p.url)));
</script>

<template>
    <!-- eslint-disable vue/no-mutating-props -->
    <form class="form" @submit.prevent="emit('submit')">
        <!-- Images -->
        <div class="field">
            <span class="label">Imágenes</span>
            <div class="thumbs">
                <div v-for="img in visibleExisting" :key="`e${img.id}`" class="thumb">
                    <img :src="img.url" alt="" class="thumb-img" />
                    <button type="button" class="remove" aria-label="Quitar" @click="removeExisting(img.id)">
                        <Icon name="x" :size="14" />
                    </button>
                </div>
                <div v-for="(p, i) in picked" :key="`n${i}`" class="thumb">
                    <img :src="p.url" alt="" class="thumb-img" />
                    <span class="new-tag">Nueva</span>
                    <button type="button" class="remove" aria-label="Quitar" @click="removePicked(i)">
                        <Icon name="x" :size="14" />
                    </button>
                </div>

                <label class="add-tile">
                    <Icon name="plus" :size="22" color="var(--text-faint)" />
                    <span>Agregar</span>
                    <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        class="file-input"
                        @change="onFilesChange"
                    />
                </label>
            </div>
            <p class="hint">Deslizá en las cards para ver todas. La primera imagen es la portada.</p>
        </div>
        <p v-if="form.errors.images" class="error">{{ form.errors.images }}</p>

        <!-- Name -->
        <label class="field">
            <span class="label">Nombre</span>
            <input v-model="form.name" type="text" class="input" placeholder="Ej: Caña Spinning Pro 2.10m" />
        </label>
        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>

        <!-- Price -->
        <label class="field">
            <span class="label">Precio (₲)</span>
            <input v-model="form.price" type="number" min="0" step="1000" class="input" placeholder="850000" />
        </label>
        <p v-if="form.errors.price" class="error">{{ form.errors.price }}</p>

        <!-- Description -->
        <label class="field">
            <span class="label">Descripción</span>
            <textarea v-model="form.description" rows="5" class="input textarea" placeholder="Detalles del producto…" />
        </label>
        <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>

        <!-- Active toggle -->
        <button type="button" class="toggle-row" @click="form.is_active = !form.is_active">
            <div class="toggle-text">
                <span class="label">Visible en la tienda</span>
                <span class="toggle-sub">Los productos ocultos no aparecen para los usuarios.</span>
            </div>
            <span class="switch" :class="{ on: form.is_active }"><span class="knob" /></span>
        </button>

        <button type="submit" class="submit" :disabled="form.processing || !hasAnyImage">
            {{ form.processing ? 'Guardando…' : submitLabel }}
        </button>
    </form>
</template>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    padding: 16px 20px 40px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin-top: 16px;
}
.field:first-child {
    margin-top: 0;
}
.label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-soft);
}
.input {
    width: 100%;
    background: var(--surface-alt);
    border: 1px solid var(--border-strong);
    border-radius: 12px;
    padding: 12px 14px;
    color: var(--text);
    font-family: inherit;
    font-size: 15px;
    outline: none;
}
.input:focus {
    border-color: var(--teal);
}
.textarea {
    resize: vertical;
    line-height: 1.5;
}
.thumbs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.thumb {
    position: relative;
    aspect-ratio: 1 / 1;
    border-radius: 12px;
    overflow: hidden;
    background: var(--surface-alt);
    border: 1px solid var(--border-strong);
}
.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.remove {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 0;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.new-tag {
    position: absolute;
    bottom: 5px;
    left: 5px;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--accent-ink);
    background: var(--accent-pdx);
    padding: 1px 6px;
    border-radius: 999px;
}
.add-tile {
    position: relative;
    aspect-ratio: 1 / 1;
    border-radius: 12px;
    border: 1px dashed var(--border-strong);
    background: var(--surface-alt);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: var(--text-faint);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.hint {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 2px;
}
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-top: 20px;
    padding: 14px;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 14px;
    text-align: left;
    cursor: pointer;
}
.toggle-text {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.toggle-sub {
    font-size: 11.5px;
    color: var(--text-muted);
    font-weight: 400;
}
.switch {
    flex: none;
    width: 46px;
    height: 27px;
    border-radius: 999px;
    background: var(--border-strong);
    padding: 3px;
    transition: background 0.15s ease;
}
.switch.on {
    background: var(--teal);
}
.knob {
    display: block;
    width: 21px;
    height: 21px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.15s ease;
}
.switch.on .knob {
    transform: translateX(19px);
}
.submit {
    margin-top: 26px;
    padding: 15px;
    border: 0;
    border-radius: 14px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
}
.submit:disabled {
    opacity: 0.6;
}
.error {
    color: #f87171;
    font-size: 12.5px;
    margin-top: 6px;
}
</style>
