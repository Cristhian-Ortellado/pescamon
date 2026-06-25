<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LevelUpOverlay from './LevelUpOverlay.vue';
import AchievementUnlockedOverlay from './AchievementUnlockedOverlay.vue';

interface RewardAchievement {
    name: string;
    description?: string;
    xp?: number;
}
interface Reward {
    id: string;
    leveled_up: boolean;
    level: number;
    xp_earned: number;
    achievements: RewardAchievement[];
}

const page = usePage();
const reward = computed<Reward | null>(() => (page.props.reward as Reward) ?? null);

// Build an ordered queue of celebration steps: level-up first, then each
// newly unlocked achievement. Driven by the shared `reward` prop, which the
// backend exposes from the latest unread catch-reward notification.
type Step = { type: 'level' } | { type: 'achievement'; data: RewardAchievement };
const steps = ref<Step[]>([]);
const index = ref(0);
const dismissedId = ref<string | null>(null);

watch(
    reward,
    (r) => {
        if (!r || r.id === dismissedId.value) return;
        const next: Step[] = [];
        if (r.leveled_up) next.push({ type: 'level' });
        for (const a of r.achievements ?? []) next.push({ type: 'achievement', data: a });
        steps.value = next;
        index.value = 0;
    },
    { immediate: true },
);

const current = computed(() => steps.value[index.value] ?? null);

function advance() {
    if (index.value < steps.value.length - 1) {
        index.value += 1;
        return;
    }
    // Finished — mark the reward consumed so it won't replay, and clear it
    // server-side by marking notifications read.
    if (reward.value) dismissedId.value = reward.value.id;
    steps.value = [];
    router.post('/notifications/read', {}, { preserveScroll: true, preserveState: true, only: ['reward'] });
}
</script>

<template>
    <LevelUpOverlay
        v-if="current?.type === 'level' && reward"
        :level="reward.level"
        :xp="reward.xp_earned"
        @close="advance"
    />
    <AchievementUnlockedOverlay
        v-else-if="current?.type === 'achievement'"
        :title="current.data.name"
        :description="current.data.description"
        :xp="current.data.xp"
        @close="advance"
    />
</template>
