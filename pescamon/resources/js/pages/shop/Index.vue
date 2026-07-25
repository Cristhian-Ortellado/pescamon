<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import { formatPrice, storageUrl } from '@/lib/pescadex';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number | string;
    image_path: string | null;
}

defineProps<{ products: Product[] }>();
</script>

<template>
    <Head title="Tienda" />

    <div class="shop">
        <header class="shop-head">
            <div class="head-icon"><Icon name="store" :size="22" color="var(--accent-pdx)" /></div>
            <div>
                <h1 class="shop-title">Tienda</h1>
                <p class="shop-sub">Equipo y accesorios de pesca</p>
            </div>
        </header>

        <div v-if="products.length" class="grid">
            <Link
                v-for="p in products"
                :key="p.id"
                :href="`/tienda/${p.slug}`"
                class="card"
            >
                <div class="media" :class="{ empty: !p.image_path }">
                    <img
                        v-if="p.image_path"
                        :src="storageUrl(p.image_path) ?? ''"
                        :alt="p.name"
                        class="media-img"
                    />
                    <Icon v-else name="package" :size="34" color="var(--text-faint)" />
                </div>
                <div class="body">
                    <div class="name">{{ p.name }}</div>
                    <div class="desc">{{ p.description }}</div>
                    <div class="foot">
                        <span class="price">{{ formatPrice(p.price) }}</span>
                        <span class="cta">Ver <Icon name="chevron-right" :size="15" /></span>
                    </div>
                </div>
            </Link>
        </div>

        <div v-else class="empty-state">
            <div class="emblem"><Icon name="store" :size="40" color="#2c5266" /></div>
            <div class="empty-title">Aún no hay productos</div>
            <div class="empty-text">Vuelve pronto: la tienda se está preparando.</div>
        </div>

        <div class="bottom-spacer" />
    </div>
</template>

<style scoped>
.shop {
    min-height: 100%;
}
.shop-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 20px 14px;
    background: var(--banner);
}
.head-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(245, 165, 36, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.shop-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    color: var(--text);
    line-height: 1.1;
}
.shop-sub {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}
.grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px 16px 0;
}
.card {
    display: flex;
    flex-direction: column;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 18px;
    overflow: hidden;
    color: var(--text);
    transition: transform 0.12s ease, border-color 0.12s ease;
}
.card:active {
    transform: scale(0.99);
}
.card:hover {
    border-color: rgba(245, 165, 36, 0.4);
}
.media {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
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
        var(--bg-frame) 12px,
        var(--surface-alt) 12px,
        var(--surface-alt) 24px
    );
}
.media-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.body {
    padding: 13px 15px 15px;
}
.name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    color: var(--text);
}
.desc {
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.45;
    margin-top: 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 12px;
}
.price {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 17px;
    color: var(--accent-pdx);
}
.cta {
    display: flex;
    align-items: center;
    gap: 2px;
    font-size: 13px;
    font-weight: 600;
    color: var(--teal);
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 70px 34px 0;
}
.emblem {
    width: 108px;
    height: 108px;
    border-radius: 50%;
    background: radial-gradient(circle at 50% 40%, #103a4e, #0b2636);
    border: 2px dashed #2a4c62;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 22px;
}
.empty-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 20px;
    color: var(--text);
}
.empty-text {
    font-size: 14px;
    color: var(--text-muted);
    margin-top: 8px;
}
.bottom-spacer {
    height: 40px;
}
</style>
