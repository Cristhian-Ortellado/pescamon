<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from './Icon.vue';

const page = usePage();

const tabs = [
    { href: '/dashboard', label: 'Mapa', icon: 'map' },
    { href: '/feed', label: 'Feed', icon: 'newspaper' },
    { href: '/profile', label: 'Perfil', icon: 'user' },
];

const current = computed(() => page.url.split('?')[0]);
function isActive(href: string) {
    return current.value === href || current.value.startsWith(href + '/');
}
</script>

<template>
    <nav class="bottom-nav">
        <Link
            v-for="tab in tabs"
            :key="tab.href"
            :href="tab.href"
            class="tab"
            :class="{ active: isActive(tab.href) }"
        >
            <Icon :name="tab.icon" :size="23" />
            <span>{{ tab.label }}</span>
        </Link>
    </nav>
</template>

<style scoped>
.bottom-nav {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: calc(72px + env(safe-area-inset-bottom));
    background: var(--nav-bg);
    backdrop-filter: blur(10px);
    border-top: 1px solid var(--nav-border);
    display: flex;
    justify-content: space-around;
    padding: 12px 22px env(safe-area-inset-bottom);
    z-index: 40;
}
.tab {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    background: none;
    border: 0;
    color: var(--text-faint);
    font-size: 10.5px;
    font-weight: 600;
    font-family: inherit;
}
.tab.active {
    color: var(--teal);
}

/* The bottom nav is a mobile affordance only — desktop uses the sidebar. */
@media (min-width: 1024px) {
    .bottom-nav {
        display: none;
    }
}
</style>
