<script setup lang="ts">
import { computed } from 'vue';
import { storageUrl } from '@/lib/pescadex';
import FishPhoto from './FishPhoto.vue';

// Shows a real catch/species photo when available, otherwise falls back to the
// rarity-tinted stripe placeholder used throughout the official design.
const props = withDefaults(
    defineProps<{
        path?: string | null;
        rarity?: string;
        stripe?: number;
        alt?: string;
        // 'cover' fills and crops the frame; 'contain' shows the whole image.
        fit?: 'cover' | 'contain';
    }>(),
    { rarity: 'rare', stripe: 9, alt: '', fit: 'cover' },
);

const src = computed(() => storageUrl(props.path));
</script>

<template>
    <img v-if="src" :src="src" :alt="alt" class="catch-img" :class="`fit-${fit}`" />
    <FishPhoto v-else :rarity="rarity" :stripe="stripe">
        <slot />
    </FishPhoto>
</template>

<style scoped>
.catch-img {
    width: 100%;
    height: 100%;
    display: block;
}
.catch-img.fit-cover {
    object-fit: cover;
}
.catch-img.fit-contain {
    object-fit: contain;
}
</style>
