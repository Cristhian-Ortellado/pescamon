<script setup lang="ts">
// The Inertia `useForm` object is a reactive store passed in by reference so
// this shared component can bind and update its fields — the standard Inertia
// pattern, hence vue/no-mutating-props is intentionally disabled here.
/* eslint-disable vue/no-mutating-props */
import type { InertiaForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';

export interface ProductFormData {
    name: string;
    price: string | number;
    description: string;
    is_active: boolean;
    image: File | null;
}

const props = defineProps<{
    form: InertiaForm<ProductFormData>;
    submitLabel: string;
    currentImage?: string | null;
}>();

const emit = defineEmits<{ (e: 'submit'): void }>();

const preview = ref<string | null>(props.currentImage ?? null);

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    props.form.image = file;
    preview.value = URL.createObjectURL(file);
}
</script>

<template>
    <!-- eslint-disable vue/no-mutating-props -->
    <form class="form" @submit.prevent="emit('submit')">
        <!-- Image -->
        <label class="field">
            <span class="label">Imagen</span>
            <div class="image-picker" :class="{ 'has-image': preview }">
                <img v-if="preview" :src="preview" alt="" class="image-preview" />
                <div v-else class="image-placeholder">
                    <Icon name="upload" :size="26" color="var(--text-faint)" />
                    <span>Subir imagen</span>
                </div>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="file-input" @change="onImageChange" />
                <span v-if="preview" class="change-hint">Cambiar</span>
            </div>
        </label>
        <p v-if="form.errors.image" class="error">{{ form.errors.image }}</p>

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

        <button type="submit" class="submit" :disabled="form.processing">
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
.image-picker {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 14px;
    border: 1px dashed var(--border-strong);
    background: var(--surface-alt);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.image-picker.has-image {
    border-style: solid;
}
.image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--text-faint);
    font-size: 13px;
    font-weight: 600;
}
.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.change-hint {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: var(--glass-strong);
    color: var(--text);
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 999px;
    backdrop-filter: blur(6px);
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
