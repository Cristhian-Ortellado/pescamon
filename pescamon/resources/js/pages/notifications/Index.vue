<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';

interface Segment {
    text: string;
    bold?: boolean;
}
interface NotificationItem {
    id: string;
    icon: string;
    tone: 'accent' | 'teal' | 'rara' | 'amber' | 'neutral';
    segments: Segment[];
    time: string;
    unread: boolean;
    period: 'today' | 'week';
    catch_id: number | null;
}

const props = defineProps<{ notifications: NotificationItem[] }>();

const groups = computed(() => [
    { label: 'Hoy', items: props.notifications.filter((n) => n.period === 'today') },
    { label: 'Anteriores', items: props.notifications.filter((n) => n.period === 'week') },
]);

const tone: Record<string, { bg: string; color: string }> = {
    accent: { bg: 'rgba(245,165,36,.18)', color: 'var(--accent-pdx)' },
    teal: { bg: 'rgba(45,212,191,.16)', color: 'var(--teal)' },
    rara: { bg: 'rgba(163,53,238,.16)', color: '#c98bf0' },
    amber: { bg: 'rgba(245,165,36,.14)', color: 'var(--accent-pdx)' },
    neutral: { bg: '#13344a', color: 'var(--icon-muted)' },
};

function markAllRead() {
    router.post('/notifications/read', {}, { preserveScroll: true });
}
function open(n: NotificationItem) {
    if (n.catch_id) router.visit(`/catches/${n.catch_id}`);
}
</script>

<template>
    <Head title="Notificaciones" />

    <div class="head">
        <div class="top-gap" />
        <div class="head-row">
            <span class="screen-title">Notificaciones</span>
            <button class="mark" @click="markAllRead">Marcar leídas</button>
        </div>
    </div>

    <p v-if="notifications.length === 0" class="empty">No tienes notificaciones todavía.</p>

    <template v-for="section in groups" :key="section.label">
        <div v-if="section.items.length" class="group-label">{{ section.label }}</div>
        <div
            v-for="n in section.items"
            :key="n.id"
            class="item"
            :class="{ unread: n.unread }"
            @click="open(n)"
        >
            <div class="icon" :style="{ background: tone[n.tone].bg }">
                <Icon :name="n.icon" :size="20" :color="tone[n.tone].color" />
            </div>
            <div class="body">
                <div class="text">
                    <template v-for="(seg, i) in n.segments" :key="i">
                        <b v-if="seg.bold">{{ seg.text }}</b><span v-else>{{ seg.text }}</span>
                    </template>
                </div>
                <div class="time">{{ n.time }}</div>
            </div>
            <span v-if="n.unread" class="dot" />
        </div>
    </template>

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 20px;
}
.top-gap {
    height: 16px;
}
.head-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
}
.screen-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    color: var(--text);
}
.mark {
    font-size: 12.5px;
    color: var(--teal);
    font-weight: 600;
    background: none;
    border: 0;
    font-family: inherit;
}
.empty {
    color: var(--text-muted);
    text-align: center;
    padding: 50px 20px;
}
.group-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-faint);
    padding: 14px 20px 8px;
}
.item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 20px;
    cursor: pointer;
}
.item.unread {
    background: rgba(45, 212, 191, 0.06);
}
.icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.body {
    flex: 1;
}
.text {
    font-size: 13.5px;
    line-height: 1.4;
    color: var(--text);
}
.text b {
    font-weight: 700;
}
.time {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 3px;
}
.dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--teal);
    margin-top: 5px;
    flex: none;
}
.bottom-spacer {
    height: 40px;
}
</style>
