<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BottomNav from '@/components/pescadex/BottomNav.vue';
import CelebrationHost from '@/components/pescadex/CelebrationHost.vue';
import Icon from '@/components/pescadex/Icon.vue';
import { Toaster } from '@/components/ui/sonner';
import { useAppearance } from '@/composables/useAppearance';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    level: number;
}

const page = usePage();
const user = computed(() => (page.props.auth as { user: AuthUser }).user);
const handle = computed(
    () => '@' + (user.value?.email?.split('@')[0] ?? 'pescador'),
);

const { resolvedAppearance, updateAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');
function toggleTheme() {
    updateAppearance(isDark.value ? 'light' : 'dark');
}

const nav = [
    { href: '/dashboard', label: 'Mapa', icon: 'map' },
    { href: '/feed', label: 'Feed', icon: 'newspaper' },
    { href: '/leaderboard', label: 'Rankings', icon: 'trophy' },
    { href: '/profile', label: 'Perfil', icon: 'user' },
];

const current = computed(() => page.url.split('?')[0]);
function isActive(href: string) {
    return current.value === href || current.value.startsWith(href + '/');
}
</script>

<template>
    <div class="studio pdx-app">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <div class="brand-mark">
                    <Icon name="fish" :size="20" color="var(--accent-ink)" />
                </div>
                <span class="brand-name">PescaDex</span>
            </Link>

            <nav class="nav">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="nav-item"
                    :class="{ active: isActive(item.href) }"
                >
                    <Icon :name="item.icon" :size="20" />
                    <span>{{ item.label }}</span>
                </Link>
                <Link
                    href="/achievements"
                    class="nav-item"
                    :class="{ active: isActive('/achievements') }"
                >
                    <Icon name="award" :size="20" />
                    <span>Logros</span>
                </Link>
                <Link
                    href="/notifications"
                    class="nav-item"
                    :class="{ active: isActive('/notifications') }"
                >
                    <Icon name="bell" :size="20" />
                    <span>Notificaciones</span>
                </Link>
            </nav>

            <Link href="/catches/create" class="register">
                <Icon name="plus" :size="19" /> Registrar captura
            </Link>

            <div class="spacer" />

            <button class="theme-toggle" @click="toggleTheme">
                <Icon :name="isDark ? 'sun' : 'moon'" :size="18" />
                <span>{{ isDark ? 'Modo claro' : 'Modo oscuro' }}</span>
            </button>

            <Link href="/profile" class="user-chip">
                <div class="chip-avatar">
                    <span class="chip-level">{{ user?.level ?? 1 }}</span>
                </div>
                <div class="chip-text">
                    <div class="chip-name">{{ user?.name }}</div>
                    <div class="chip-handle">{{ handle }}</div>
                </div>
            </Link>
        </aside>

        <main class="stage">
            <div class="panel pdx-scroll">
                <slot />
            </div>
        </main>

        <BottomNav />
        <CelebrationHost />
        <Toaster />
    </div>
</template>

<style scoped>
.studio {
    display: flex;
    min-height: 100vh;
    min-height: 100dvh;
    background: var(--studio-bg);
}
.sidebar {
    width: 248px;
    flex: none;
    display: flex;
    flex-direction: column;
    padding: 26px 16px 22px;
    border-right: 1px solid var(--sidebar-border-pdx);
    position: sticky;
    top: 0;
    height: 100vh;
}
.brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0 8px;
    margin-bottom: 28px;
}
.brand-mark {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--accent-pdx);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.brand-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
.nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 11px 12px;
    border: 0;
    background: none;
    border-radius: 11px;
    color: var(--sidebar-item);
    font-size: 14.5px;
    font-weight: 600;
    font-family: inherit;
    text-align: left;
}
.nav-item:hover {
    background: var(--sidebar-item-hover-bg);
    color: var(--text);
}
.nav-item.active {
    background: rgba(45, 212, 191, 0.14);
    color: var(--teal);
}
.register {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 18px;
    padding: 12px;
    border: 0;
    border-radius: 13px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14.5px;
    box-shadow: 0 10px 24px rgba(245, 165, 36, 0.32);
}
.spacer {
    flex: 1;
}
.theme-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 12px;
    border: 1px solid var(--sidebar-chip-border);
    border-radius: 12px;
    background: none;
    color: var(--sidebar-item);
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 600;
    text-align: left;
}
.theme-toggle:hover {
    background: var(--sidebar-item-hover-bg);
    color: var(--text);
}
.user-chip {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 10px;
    border: 1px solid var(--sidebar-chip-border);
    border-radius: 14px;
    background: var(--sidebar-chip-bg);
    color: var(--text);
    font-family: inherit;
    text-align: left;
}
.user-chip:hover {
    background: var(--sidebar-item-hover-bg);
}
.chip-avatar {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: repeating-linear-gradient(
        135deg,
        var(--avatar-a),
        var(--avatar-a) 5px,
        var(--avatar-b) 5px,
        var(--avatar-b) 10px
    );
    border: 2px solid var(--teal);
    flex: none;
}
.chip-level {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 10px;
    min-width: 17px;
    text-align: center;
    padding: 1px 4px;
    border-radius: 999px;
    border: 2px solid var(--sidebar-chip-bg);
}
.chip-text {
    min-width: 0;
}
.chip-name {
    font-size: 13.5px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chip-handle {
    font-size: 11.5px;
    color: var(--text-muted);
}
.stage {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 28px;
}
.panel {
    position: relative;
    width: 100%;
    max-width: 440px;
    height: min(940px, calc(100dvh - 56px));
    overflow-y: auto;
    background: var(--bg-frame);
    border: 1px solid var(--nav-border);
    border-radius: 22px;
    box-shadow: 0 24px 60px rgba(5, 18, 28, 0.28);
}

/* On phones: drop the desktop chrome, panel fills the viewport, bottom nav shows. */
@media (max-width: 1023px) {
    .studio {
        background: var(--bg-frame);
    }
    .sidebar {
        display: none;
    }
    .stage {
        padding: 0;
    }
    .panel {
        max-width: 100%;
        height: 100dvh;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
}
</style>
