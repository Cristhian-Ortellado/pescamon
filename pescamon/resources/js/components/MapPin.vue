<script setup lang="ts">
defineProps<{
    rarity?: string;
    size?: number;
}>();

const rarityColor: Record<string, string> = {
    common: '#6B7280',
    uncommon: '#10B981',
    rare: '#3B82F6',
    legendary: '#F59E0B',
};
</script>

<template>
    <div
        :style="{
            width: `${size ?? 14}px`,
            height: `${size ?? 14}px`,
            borderRadius: '50%',
            background: rarityColor[rarity ?? 'common'] ?? '#6B7280',
            border: '2px solid white',
            boxShadow: '0 1px 3px rgba(0,0,0,.4)',
        }"
    />
</template>
