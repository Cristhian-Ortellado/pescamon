<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';

const page = usePage();
const isAuthed = computed(() => !!(page.props.auth as { user: unknown } | undefined)?.user);

const bullets = [
    { icon: 'layout-grid', text: 'Tu Pokédex personal de especies' },
    { icon: 'map-pin', text: 'Mapa social de capturas cercanas' },
    { icon: 'trophy', text: 'Niveles, logros y rankings' },
];
</script>

<template>
    <Head title="PescaDex" />

    <div class="page pdx-app">
        <div class="panel pdx-scroll">
            <div class="hero">
                <div class="hero-fade" />
            </div>

            <div class="content">
                <div class="logo">
                    <div class="logo-mark"><Icon name="fish" :size="20" color="var(--accent-ink)" /></div>
                    <span class="logo-name">PescaDex</span>
                </div>
                <div class="headline">Completa tu colección de peces</div>
                <div class="sub">Registra tus capturas, desbloquea especies y descubre dónde pican en el mapa.</div>

                <div class="bullets">
                    <div v-for="b in bullets" :key="b.text" class="bullet">
                        <Icon :name="b.icon" :size="19" color="var(--teal)" /> {{ b.text }}
                    </div>
                </div>

                <template v-if="isAuthed">
                    <Link href="/dashboard" class="cta">Entrar a la app</Link>
                </template>
                <template v-else>
                    <Link href="/register" class="cta">Crear cuenta</Link>
                    <div class="signin">
                        ¿Ya tienes cuenta? <Link href="/login" class="signin-link">Inicia sesión</Link>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page {
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--studio-bg);
    padding: 24px;
}
.panel {
    position: relative;
    width: 100%;
    max-width: 440px;
    height: min(880px, calc(100dvh - 48px));
    background: var(--bg-frame);
    border: 1px solid var(--nav-border);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(5, 18, 28, 0.28);
}
.hero {
    position: absolute;
    inset: 0 0 auto 0;
    height: 430px;
    background: repeating-linear-gradient(135deg, #0e3346, #0e3346 12px, #0c2c3d 12px, #0c2c3d 24px);
}
.hero-fade {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(7, 28, 42, 0.35) 0%, transparent 35%, var(--bg-frame) 96%);
}
.content {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 0 28px 40px;
    z-index: 3;
}
.logo {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 14px;
}
.logo-mark {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--accent-pdx);
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 24px;
    color: var(--text);
}
.headline {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 32px;
    line-height: 1.1;
    letter-spacing: -0.01em;
    color: var(--text);
}
.sub {
    font-size: 14.5px;
    color: var(--text-soft);
    margin-top: 12px;
    line-height: 1.5;
}
.bullets {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 24px;
}
.bullet {
    display: flex;
    align-items: center;
    gap: 11px;
    font-size: 13.5px;
    color: var(--text-strong);
}
.cta {
    display: block;
    text-align: center;
    width: 100%;
    margin-top: 28px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16.5px;
    padding: 15px;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.45);
}
.signin {
    text-align: center;
    margin-top: 18px;
    font-size: 13.5px;
    color: var(--icon-muted);
}
.signin-link {
    color: var(--teal);
    font-weight: 600;
}
</style>
