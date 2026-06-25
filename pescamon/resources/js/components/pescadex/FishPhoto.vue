<script setup lang="ts">
import { computed } from 'vue';
import { rarityMeta } from '@/lib/pescadex';

// Diagonal-stripe placeholder standing in for catch photography. The tint
// shifts with rarity so legendary catches read warm, freshwater greens cool.
const props = withDefaults(
    defineProps<{ rarity?: string; stripe?: number; radius?: number }>(),
    { rarity: 'rare', stripe: 9 },
);

const background = computed(() => {
    const [a, b] = rarityMeta(props.rarity).photo;
    const s = props.stripe;
    return `repeating-linear-gradient(135deg, ${a}, ${a} ${s}px, ${b} ${s}px, ${b} ${s * 2}px)`;
});
</script>

<template>
    <div
        class="fish-photo"
        :style="{ background, borderRadius: radius ? radius + 'px' : undefined }"
    >
        <slot />
    </div>
</template>

<style scoped>
.fish-photo {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>
