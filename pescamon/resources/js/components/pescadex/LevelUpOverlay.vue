<script setup lang="ts">
import Icon from './Icon.vue';
import { levelTitle } from '@/lib/pescadex';
import { computed } from 'vue';

const props = defineProps<{
    level: number;
    xp?: number;
}>();

const emit = defineEmits<{ close: [] }>();

const title = computed(() => levelTitle(props.level));

const confetti = [
    { left: '12%', w: 9, h: 13, color: 'var(--accent-pdx)', round: false, dur: '2.6s', delay: '0s' },
    { left: '26%', w: 8, h: 8, color: 'var(--teal)', round: true, dur: '3.1s', delay: '.3s' },
    { left: '42%', w: 10, h: 10, color: '#a335ee', round: false, dur: '2.9s', delay: '.6s' },
    { left: '60%', w: 9, h: 13, color: '#5eead4', round: false, dur: '3.3s', delay: '.15s' },
    { left: '74%', w: 8, h: 8, color: 'var(--accent-pdx)', round: true, dur: '2.7s', delay: '.5s' },
    { left: '88%', w: 10, h: 10, color: '#ff8000', round: false, dur: '3s', delay: '.8s' },
];
</script>

<template>
    <div class="overlay pdx-app">
        <div class="bg" />
        <div
            v-for="(c, i) in confetti"
            :key="i"
            class="confetti"
            :style="{
                left: c.left,
                width: c.w + 'px',
                height: c.h + 'px',
                background: c.color,
                borderRadius: c.round ? '50%' : '2px',
                animationDuration: c.dur,
                animationDelay: c.delay,
            }"
        />

        <div class="stack">
            <div class="badge-wrap">
                <div class="halo" />
                <div class="medal">
                    <span class="medal-cap">NIVEL</span>
                    <span class="medal-num">{{ level }}</span>
                </div>
            </div>
            <div class="headline">¡Subiste de nivel!</div>
            <div class="subline">Ahora eres <b>{{ title }}</b></div>

            <div v-if="xp" class="xp-pill"><Icon name="zap" :size="18" /> +{{ xp }} XP</div>
        </div>

        <div class="cta-bar">
            <button class="continue" @click="emit('close')">Continuar</button>
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
    background: radial-gradient(120% 80% at 50% 30%, #123a52, #07182a 70%);
}
.confetti {
    position: absolute;
    top: 80px;
    animation-name: pdx-confetti;
    animation-timing-function: linear;
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
}
.badge-wrap {
    position: relative;
    width: 180px;
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.halo {
    position: absolute;
    inset: -20px;
    background: conic-gradient(from 0deg, transparent, rgba(245, 165, 36, 0.45), transparent 40%);
    border-radius: 50%;
    animation: pdx-spin 6s linear infinite;
}
.medal {
    position: relative;
    width: 148px;
    height: 148px;
    border-radius: 50%;
    background: radial-gradient(circle at 50% 35%, #ffce7a, var(--accent-pdx) 55%, #b9730f);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 40px rgba(245, 165, 36, 0.6), inset 0 -8px 18px rgba(120, 70, 0, 0.5);
    border: 4px solid #ffe7b0;
    animation: pdx-levelpop 1s ease both;
}
.medal-cap {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 13px;
    color: #5a3500;
    letter-spacing: 0.1em;
    margin-bottom: -6px;
}
.medal-num {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 58px;
    color: #3a2200;
    line-height: 1;
}
.headline {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 27px;
    margin-top: 26px;
    color: var(--text);
}
.subline {
    font-size: 14.5px;
    color: var(--icon-muted);
    margin-top: 6px;
}
.subline b {
    color: var(--text-strong);
    font-weight: 600;
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
}
.continue {
    width: 100%;
    text-align: center;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    padding: 15px;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.45);
}
</style>
