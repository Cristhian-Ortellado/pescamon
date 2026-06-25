<script setup lang="ts">
import Icon from './Icon.vue';

defineProps<{
    title: string;
    description?: string;
    xp?: number;
}>();

const emit = defineEmits<{ close: [] }>();

const sparkles = [
    { left: '22%', top: '24%', size: 8, color: '#fff', dur: '1.8s', delay: '0s' },
    { left: '74%', top: '28%', size: 7, color: 'var(--teal)', dur: '2.2s', delay: '.4s' },
    { left: '30%', top: '42%', size: 6, color: 'var(--accent-pdx)', dur: '2s', delay: '.7s' },
    { left: '80%', top: '46%', size: 8, color: '#a335ee', dur: '1.7s', delay: '.2s' },
];
</script>

<template>
    <div class="overlay pdx-app">
        <div class="bg" />
        <div
            v-for="(s, i) in sparkles"
            :key="i"
            class="sparkle"
            :style="{
                left: s.left,
                top: s.top,
                width: s.size + 'px',
                height: s.size + 'px',
                background: s.color,
                animationDuration: s.dur,
                animationDelay: s.delay,
            }"
        />

        <div class="stack">
            <div class="eyebrow">★ Logro desbloqueado ★</div>
            <div class="badge">
                <Icon name="gem" :size="66" color="#9af0e2" />
                <div class="shine" />
            </div>
            <div class="title">{{ title }}</div>
            <div v-if="description" class="desc">{{ description }}</div>
            <div v-if="xp" class="xp-pill"><Icon name="zap" :size="18" /> +{{ xp }} XP</div>
        </div>

        <div class="cta-bar">
            <button class="primary" @click="emit('close')">¡Genial!</button>
        </div>
    </div>
</template>

<style scoped>
.overlay {
    position: fixed;
    inset: 0;
    z-index: 80;
    overflow: hidden;
}
.bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(120% 80% at 50% 32%, #15324a, #07182a 70%);
}
.sparkle {
    position: absolute;
    border-radius: 50%;
    animation-name: pdx-twinkle;
    animation-timing-function: ease;
    animation-iteration-count: infinite;
}
.stack {
    position: absolute;
    left: 0;
    right: 0;
    top: 24%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 24px;
}
.eyebrow {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.18em;
    color: var(--teal);
    text-transform: uppercase;
    margin-bottom: 26px;
    animation: pdx-float 3s ease infinite;
}
.badge {
    position: relative;
    width: 158px;
    height: 158px;
    border-radius: 34px;
    overflow: hidden;
    background: radial-gradient(circle at 50% 32%, #2a6f66, #0e3a35);
    border: 3px solid var(--teal);
    box-shadow: 0 0 44px rgba(45, 212, 191, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pdx-pop 1s ease both;
}
.shine {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 55%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: pdx-shine 2.8s ease infinite;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 25px;
    margin-top: 28px;
    color: var(--text);
    text-align: center;
}
.desc {
    font-size: 14.5px;
    color: var(--icon-muted);
    margin-top: 8px;
    text-align: center;
    max-width: 280px;
    line-height: 1.45;
}
.xp-pill {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 22px;
    background: rgba(245, 165, 36, 0.16);
    border: 1px solid rgba(245, 165, 36, 0.5);
    border-radius: 999px;
    padding: 8px 16px;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    color: var(--accent-pdx);
}
.cta-bar {
    position: absolute;
    left: 24px;
    right: 24px;
    bottom: calc(40px + env(safe-area-inset-bottom));
    max-width: 360px;
    margin: 0 auto;
    display: flex;
    gap: 12px;
}
.primary {
    flex: 1;
    text-align: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    padding: 14px;
    border-radius: 16px;
    border: 0;
    background: var(--teal);
    color: var(--teal-ink);
    box-shadow: 0 12px 28px rgba(45, 212, 191, 0.4);
}
</style>
