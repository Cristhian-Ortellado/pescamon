<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{ progress: number; height?: number }>(),
    { height: 9 },
);
// Accepts either a 0–1 fraction or a 0–100 percentage.
const width = computed(() => {
    const p = props.progress > 1 ? props.progress / 100 : props.progress;
    return `${Math.round(Math.min(1, Math.max(0, p)) * 100)}%`;
});
</script>

<template>
    <div class="track" :style="{ height: height + 'px' }">
        <div class="fill" :style="{ width }" />
    </div>
</template>

<style scoped>
.track {
    border-radius: 999px;
    background: var(--track-bg);
    overflow: hidden;
    border: 1px solid var(--track-border);
}
.fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--teal), var(--teal-bright));
}
</style>
