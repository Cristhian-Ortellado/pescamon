<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import { goBack } from '@/lib/navigation';
import ProductForm from './ProductForm.vue';
import type {ProductFormData} from './ProductForm.vue';

const form = useForm<ProductFormData>({
    name: '',
    price: '',
    description: '',
    is_active: true,
    image: null,
});

function submit() {
    form.post('/admin/products', { forceFormData: true });
}
</script>

<template>
    <Head title="Nuevo producto" />

    <div class="screen">
        <header class="head">
            <button class="ic" aria-label="Volver" @click="goBack('/admin/products')">
                <Icon name="arrow-left" :size="23" />
            </button>
            <h1 class="title">Nuevo producto</h1>
        </header>

        <ProductForm :form="form" submit-label="Crear producto" @submit="submit" />
    </div>
</template>

<style scoped>
.head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px 8px;
    background: var(--banner);
}
.ic {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
    color: var(--text);
    cursor: pointer;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 20px;
    color: var(--text);
}
</style>
