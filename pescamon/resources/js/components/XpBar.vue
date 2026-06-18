<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    xp: number;
    level: number;
    progress?: number;
}>();

// Formula: level^2 * 100
function xpForLevel(n: number) {
    return n ** 2 * 100;
}

const progressPercent = computed(() => {
    if (props.progress !== undefined) return props.progress;
    const current = xpForLevel(props.level);
    const next = xpForLevel(props.level + 1);
    return Math.min(100, ((props.xp - current) / (next - current)) * 100);
});
</script>

<template>
    <div class="w-full">
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>Nv. {{ level }}</span>
            <span>{{ Math.round(progressPercent) }}%</span>
        </div>
        <div class="mt-0.5 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-600">
            <div
                class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 transition-all duration-500"
                :style="{ width: `${progressPercent}%` }"
            />
        </div>
    </div>
</template>
