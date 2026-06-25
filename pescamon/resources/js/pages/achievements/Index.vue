<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import XpBar from '@/components/pescadex/XpBar.vue';

interface Achievement {
    id: number;
    code: string;
    name: string;
    description: string;
    xp_reward: number;
    icon: string;
    tone: 'teal' | 'accent' | 'rara';
    unlocked: boolean;
    progress: { current: number; total: number } | null;
}

const props = defineProps<{
    achievements: Achievement[];
    unlockedCount: number;
    totalCount: number;
    next: Achievement | null;
}>();

const overallProgress = computed(() => (props.totalCount ? props.unlockedCount / props.totalCount : 0));
const nextProgress = computed(() =>
    props.next?.progress ? props.next.progress.current / props.next.progress.total : 0,
);
const unlocked = computed(() => props.achievements.filter((a) => a.unlocked));
const locked = computed(() => props.achievements.filter((a) => !a.unlocked && a.id !== props.next?.id));

const toneStyles: Record<string, { bg: string; border: string; icon: string }> = {
    teal: { bg: 'radial-gradient(circle at 50% 35%, #1a5e57, #0e3a35)', border: 'var(--teal)', icon: 'var(--teal)' },
    accent: { bg: 'radial-gradient(circle at 50% 35%, #5a3a12, #3a2410)', border: 'var(--accent-pdx)', icon: 'var(--accent-pdx)' },
    rara: { bg: 'radial-gradient(circle at 50% 35%, #5a1e6e, #3a1248)', border: '#a335ee', icon: '#c98bf0' },
};
const toneGlow: Record<string, string> = {
    teal: '0 0 14px rgba(45,212,191,.25)',
    accent: '0 0 14px rgba(245,165,36,.25)',
    rara: '0 0 14px rgba(163,53,238,.25)',
};
</script>

<template>
    <Head title="Logros" />

    <div class="head">
        <div class="top-gap" />
        <div class="title-row">
            <span class="screen-title">Logros</span>
            <span class="count">{{ unlockedCount }} / {{ totalCount }}</span>
        </div>
        <XpBar :progress="overallProgress" :height="8" />
    </div>

    <div v-if="next" class="featured">
        <div class="f-row">
            <div class="f-icon"><Icon :name="next.icon" :size="22" color="var(--accent-pdx)" /></div>
            <div class="f-text">
                <div class="f-title">{{ next.name }}</div>
                <div class="f-desc">{{ next.description }}</div>
            </div>
            <span class="f-progress">{{ next.progress?.current }}/{{ next.progress?.total }}</span>
        </div>
        <div class="f-track"><div class="f-fill" :style="{ width: nextProgress * 100 + '%' }" /></div>
    </div>

    <div class="group-label">Desbloqueados</div>
    <div class="grid">
        <div v-for="a in unlocked" :key="a.id" class="badge-cell">
            <div
                class="badge"
                :style="{ background: toneStyles[a.tone].bg, borderColor: toneStyles[a.tone].border, boxShadow: toneGlow[a.tone] }"
            >
                <Icon :name="a.icon" :size="27" :color="toneStyles[a.tone].icon" />
            </div>
            <div class="badge-label">{{ a.name }}</div>
        </div>
        <p v-if="unlocked.length === 0" class="muted-note">Aún no has desbloqueado logros. ¡Registra una captura!</p>
    </div>

    <div class="group-label">Bloqueados</div>
    <div class="grid">
        <div v-for="a in locked" :key="a.id" class="badge-cell locked">
            <div class="badge locked-badge"><Icon name="lock" :size="22" color="var(--text-locked)" /></div>
            <div class="badge-label locked-label">{{ a.name }}</div>
        </div>
    </div>

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 20px;
}
.top-gap {
    height: 16px;
}
.title-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 4px 0;
}
.screen-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    color: var(--text);
}
.count {
    font-size: 13px;
    color: var(--text-muted);
}
.featured {
    margin: 16px 20px 4px;
    background: linear-gradient(90deg, rgba(245, 165, 36, 0.16), rgba(245, 165, 36, 0.04));
    border: 1px solid rgba(245, 165, 36, 0.4);
    border-radius: 16px;
    padding: 13px 14px;
}
.f-row {
    display: flex;
    align-items: center;
    gap: 11px;
}
.f-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(245, 165, 36, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.f-text {
    flex: 1;
}
.f-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14.5px;
    color: var(--text);
}
.f-desc {
    font-size: 11.5px;
    color: #cdb98a;
}
.f-progress {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--accent-pdx);
}
.f-track {
    height: 6px;
    border-radius: 999px;
    background: #2a2110;
    overflow: hidden;
    margin-top: 10px;
}
.f-fill {
    height: 100%;
    background: var(--accent-pdx);
}
.group-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-faint);
    padding: 14px 20px 10px;
}
.grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px 10px;
    padding: 0 20px;
}
.badge-cell {
    text-align: center;
}
.badge-cell.locked {
    opacity: 0.55;
}
.badge {
    width: 62px;
    height: 62px;
    border-radius: 18px;
    margin: 0 auto 7px;
    border: 1.5px solid;
    display: flex;
    align-items: center;
    justify-content: center;
}
.locked-badge {
    background: #0b2433;
    border: 1.5px dashed #2c4a5e;
}
.badge-label {
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1.15;
    color: var(--text);
}
.locked-label {
    color: var(--text-faint);
}
.muted-note {
    grid-column: 1 / -1;
    color: var(--text-muted);
    font-size: 13px;
    text-align: center;
    padding: 8px 0;
}
.bottom-spacer {
    height: 40px;
}
</style>
