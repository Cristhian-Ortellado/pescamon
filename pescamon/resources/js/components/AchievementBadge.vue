<script setup lang="ts">
defineProps<{
    achievement: {
        id: number;
        code: string;
        name: string;
        description: string;
        icon_path: string | null;
        pivot?: { unlocked_at: string };
    };
}>();
</script>

<template>
    <div class="flex flex-col items-center rounded-xl bg-white p-4 shadow dark:bg-gray-800">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-3xl dark:bg-amber-900/30">
            <img v-if="achievement.icon_path" :src="`/storage/${achievement.icon_path}`" :alt="achievement.name" class="h-10 w-10 object-contain" />
            <span v-else>🏅</span>
        </div>
        <p class="mt-2 text-center text-xs font-semibold text-gray-900 dark:text-white">{{ achievement.name }}</p>
        <p class="mt-0.5 text-center text-xs text-gray-500 dark:text-gray-400">{{ achievement.description }}</p>
        <p v-if="achievement.pivot?.unlocked_at" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
            {{ new Date(achievement.pivot.unlocked_at).toLocaleDateString('es-ES') }}
        </p>
    </div>
</template>
