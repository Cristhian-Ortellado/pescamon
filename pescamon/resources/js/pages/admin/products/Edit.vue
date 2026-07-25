<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import { goBack } from '@/lib/navigation';
import ProductForm from './ProductForm.vue';
import type { ProductFormData } from './ProductForm.vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number | string;
    images: { id: number; url: string }[];
    is_active: boolean;
}

const props = defineProps<{ product: Product }>();

const form = useForm<ProductFormData>({
    name: props.product.name,
    price: props.product.price,
    description: props.product.description,
    is_active: props.product.is_active,
    images: [],
    removed_image_ids: [],
});

function submit() {
    // Spoof PUT so the multipart request reaches the resource update route
    // (browsers can't send files over a real PUT).
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/admin/products/${props.product.id}`, { forceFormData: true });
}
</script>

<template>
    <Head :title="`Editar: ${product.name}`" />

    <div class="screen">
        <header class="head">
            <button class="ic" aria-label="Volver" @click="goBack('/admin/products')">
                <Icon name="arrow-left" :size="23" />
            </button>
            <h1 class="title">Editar producto</h1>
        </header>

        <ProductForm
            :form="form"
            :existing-images="product.images"
            submit-label="Guardar cambios"
            @submit="submit"
        />
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
