<script setup lang="ts">
import { computed } from 'vue';
import { rarityMeta } from '@/lib/pescadex';
import CatchPhoto from './CatchPhoto.vue';
import RarityBadge from './RarityBadge.vue';

const props = defineProps<{
    name: string;
    rarity: string;
    caught: boolean;
    imagePath?: string | null;
    record?: string | number | null;
}>();

const borderColor = computed(() => rarityMeta(props.rarity).color);
const glow = computed(() => {
    const key = rarityMeta(props.rarity);
    if (key.label === 'Legendaria') return '0 0 14px rgba(255,128,0,.4)';
    if (key.label === 'Rara') return '0 0 0 1px rgba(163,53,238,.25)';
    return 'none';
});
</script>

<template>
    <button
        v-if="caught"
        class="card unlocked"
        :style="{ borderColor, boxShadow: glow }"
    >
        <div class="media">
            <CatchPhoto :path="imagePath" :rarity="rarity" :stripe="7" :alt="name" />
            <RarityBadge :rarity="rarity" variant="short" class="badge" />
        </div>
        <div class="body">
            <div class="name">{{ name }}</div>
            <div class="record">{{ record != null ? record + ' kg' : 'capturada' }}</div>
        </div>
    </button>

    <div v-else class="card locked">
        <div class="media locked-media">
            <span class="qmark">?</span>
        </div>
        <div class="body">
            <div class="name locked-name">?????</div>
            <div class="record locked-name">bloqueada</div>
        </div>
    </div>
</template>

<style scoped>
.card {
    display: block;
    width: 100%;
    text-align: left;
    border-radius: 14px;
    overflow: hidden;
    background: var(--surface);
    border: 2px solid var(--border-strong);
    padding: 0;
    font-family: inherit;
    color: var(--text);
    cursor: pointer;
}
.card.locked {
    background: var(--surface-locked);
    border: 2px dashed var(--border-strong);
    opacity: 0.85;
    cursor: default;
}
.media {
    position: relative;
    height: 62px;
}
.locked-media {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-locked);
}
.qmark {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 26px;
    color: var(--text-locked);
}
.badge {
    position: absolute;
    top: 4px;
    left: 4px;
}
.body {
    padding: 6px 7px;
}
.name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 11.5px;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.record {
    font-size: 9.5px;
    color: var(--text-muted);
}
.locked-name {
    color: var(--text-locked);
}
</style>
