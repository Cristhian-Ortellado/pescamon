<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import { goBack } from '@/lib/navigation';
import { formatPrice } from '@/lib/pescadex';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number | string;
    image_url: string | null;
}

const props = defineProps<{ product: Product; whatsapp: string }>();

// Pre-filled WhatsApp order message → wa.me deep link.
const whatsappUrl = computed(() => {
    const text = `¡Hola! Me interesa este producto de la tienda:\n\n*${props.product.name}*\nPrecio: ${formatPrice(props.product.price)}\n\n¿Está disponible?`;

    return `https://wa.me/${props.whatsapp}?text=${encodeURIComponent(text)}`;
});
</script>

<template>
    <Head :title="product.name" />

    <div class="detail">
        <div class="media" :class="{ empty: !product.image_url }">
            <img
                v-if="product.image_url"
                :src="product.image_url"
                :alt="product.name"
                class="media-img"
            />
            <Icon v-else name="package" :size="56" color="var(--text-faint)" />

            <button class="back" aria-label="Volver" @click="goBack('/tienda')">
                <Icon name="arrow-left" :size="22" />
            </button>
        </div>

        <div class="body">
            <h1 class="name">{{ product.name }}</h1>
            <div class="price">{{ formatPrice(product.price) }}</div>

            <div class="section-label">Descripción</div>
            <p class="desc">{{ product.description }}</p>
        </div>

        <div class="order-bar">
            <a :href="whatsappUrl" target="_blank" rel="noopener" class="wa-btn">
                <Icon name="message-circle" :size="20" />
                Pedir por WhatsApp
            </a>
        </div>
    </div>
</template>

<style scoped>
.detail {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
.media {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    background: var(--bg-frame);
    overflow: hidden;
}
.media.empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: repeating-linear-gradient(
        135deg,
        var(--bg-frame),
        var(--bg-frame) 14px,
        var(--surface-alt) 14px,
        var(--surface-alt) 28px
    );
}
.media-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.back {
    position: absolute;
    top: 14px;
    left: 14px;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--glass-strong);
    backdrop-filter: blur(8px);
    color: var(--text);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
    cursor: pointer;
}
.body {
    padding: 18px 20px 0;
}
.name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 24px;
    color: var(--text);
    line-height: 1.2;
}
.price {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 22px;
    color: var(--accent-pdx);
    margin-top: 8px;
}
.section-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin-top: 22px;
}
.desc {
    font-size: 14.5px;
    color: var(--text-soft);
    line-height: 1.6;
    margin-top: 8px;
    white-space: pre-line;
}
.order-bar {
    position: sticky;
    bottom: 0;
    margin-top: auto;
    padding: 16px 20px calc(16px + env(safe-area-inset-bottom));
    background: linear-gradient(to top, var(--bg-frame) 68%, transparent);
}
.wa-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    border-radius: 15px;
    background: #25d366;
    color: #05331a;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 16px;
    box-shadow: 0 12px 28px rgba(37, 211, 102, 0.4);
}

/* Clear the mobile bottom nav so the button isn't hidden behind it. */
@media (max-width: 1023px) {
    .order-bar {
        padding-bottom: calc(84px + env(safe-area-inset-bottom));
    }
}
</style>
