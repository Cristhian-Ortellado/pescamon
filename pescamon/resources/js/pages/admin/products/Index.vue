<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import { goBack } from '@/lib/navigation';
import { formatPrice } from '@/lib/pescadex';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number | string;
    cover: string | null;
    image_count: number;
    is_active: boolean;
}

defineProps<{ products: Product[] }>();

// Id pending delete confirmation (inline, avoids a native dialog).
const confirmingId = ref<number | null>(null);

function remove(id: number) {
    router.delete(`/admin/products/${id}`, {
        preserveScroll: true,
        onFinish: () => (confirmingId.value = null),
    });
}
</script>

<template>
    <Head title="Administrar tienda" />

    <div class="screen">
        <header class="head">
            <button class="ic" aria-label="Volver" @click="goBack('/profile')">
                <Icon name="arrow-left" :size="23" />
            </button>
            <div class="head-text">
                <h1 class="title">Administrar tienda</h1>
                <p class="sub">{{ products.length }} producto{{ products.length === 1 ? '' : 's' }}</p>
            </div>
            <Link href="/admin/products/create" class="new-btn">
                <Icon name="plus" :size="18" />
            </Link>
        </header>

        <div v-if="products.length" class="list">
            <div v-for="p in products" :key="p.id" class="row">
                <div class="thumb" :class="{ empty: !p.cover }">
                    <img v-if="p.cover" :src="p.cover" :alt="p.name" class="thumb-img" />
                    <Icon v-else name="package" :size="22" color="var(--text-faint)" />
                    <span v-if="p.image_count > 1" class="count">
                        <Icon name="image" :size="11" /> {{ p.image_count }}
                    </span>
                </div>
                <div class="info">
                    <div class="name-row">
                        <span class="name">{{ p.name }}</span>
                        <span v-if="!p.is_active" class="badge">Oculto</span>
                    </div>
                    <div class="price">{{ formatPrice(p.price) }}</div>
                </div>

                <template v-if="confirmingId === p.id">
                    <button class="act danger" aria-label="Confirmar" @click="remove(p.id)">
                        <Icon name="check" :size="18" />
                    </button>
                    <button class="act" aria-label="Cancelar" @click="confirmingId = null">
                        <Icon name="x" :size="18" />
                    </button>
                </template>
                <template v-else>
                    <Link :href="`/admin/products/${p.id}/edit`" class="act" aria-label="Editar">
                        <Icon name="pencil" :size="17" />
                    </Link>
                    <button class="act" aria-label="Eliminar" @click="confirmingId = p.id">
                        <Icon name="trash-2" :size="17" color="#f87171" />
                    </button>
                </template>
            </div>
        </div>

        <div v-else class="empty-state">
            <div class="emblem"><Icon name="package" :size="38" color="#2c5266" /></div>
            <div class="empty-title">Sin productos todavía</div>
            <Link href="/admin/products/create" class="cta">
                <Icon name="plus" :size="18" /> Crear el primero
            </Link>
        </div>

        <div class="bottom-spacer" />
    </div>
</template>

<style scoped>
.head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px 12px;
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
.head-text {
    flex: 1;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 20px;
    color: var(--text);
    line-height: 1.1;
}
.sub {
    font-size: 12.5px;
    color: var(--text-muted);
    margin-top: 2px;
}
.new-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 16px;
}
.row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 14px;
}
.thumb {
    position: relative;
    width: 52px;
    height: 52px;
    border-radius: 11px;
    overflow: hidden;
    flex: none;
    background: var(--bg-frame);
    display: flex;
    align-items: center;
    justify-content: center;
}
.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.count {
    position: absolute;
    bottom: 3px;
    right: 3px;
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 1px 5px 1px 4px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.62);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
}
.info {
    flex: 1;
    min-width: 0;
}
.name-row {
    display: flex;
    align-items: center;
    gap: 7px;
}
.name {
    font-weight: 600;
    font-size: 14.5px;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.badge {
    flex: none;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    border: 1px solid var(--border-strong);
    border-radius: 999px;
    padding: 1px 7px;
}
.price {
    font-size: 13px;
    font-weight: 600;
    color: var(--accent-pdx);
    margin-top: 3px;
}
.act {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    border: 1px solid var(--border-strong);
    background: var(--bg-frame);
    color: var(--text-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
    cursor: pointer;
}
.act.danger {
    background: rgba(248, 113, 113, 0.15);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 70px 34px 0;
}
.emblem {
    width: 104px;
    height: 104px;
    border-radius: 50%;
    background: radial-gradient(circle at 50% 40%, #103a4e, #0b2636);
    border: 2px dashed #2a4c62;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}
.empty-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 19px;
    color: var(--text);
    margin-bottom: 18px;
}
.cta {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    padding: 12px 20px;
    border-radius: 999px;
}
.bottom-spacer {
    height: 40px;
}
</style>
