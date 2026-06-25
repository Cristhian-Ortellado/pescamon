<script setup lang="ts">
import { computed } from 'vue';
import { rarityMeta } from '@/lib/pescadex';

const props = withDefaults(
    defineProps<{
        /** Backend rarity: common | uncommon | rare | legendary */
        rarity: string;
        variant?: 'solid' | 'outline' | 'short';
    }>(),
    { variant: 'solid' },
);

const meta = computed(() => rarityMeta(props.rarity));
const label = computed(() =>
    props.variant === 'short' ? meta.value.short : meta.value.label.toUpperCase(),
);
</script>

<template>
    <span
        v-if="variant === 'outline'"
        class="badge outline"
        :style="{ color: meta.color, borderColor: meta.color }"
        >{{ label }}</span
    >
    <span
        v-else
        class="badge solid"
        :style="{ background: meta.color, color: meta.ink }"
        >{{ label }}</span
    >
</template>

<style scoped>
.badge {
    display: inline-block;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.03em;
    border-radius: 6px;
    padding: 2px 5px;
    line-height: 1.2;
    white-space: nowrap;
}
.badge.outline {
    font-size: 10px;
    border: 1px solid;
    padding: 1px 5px;
    border-radius: 6px;
}
</style>
