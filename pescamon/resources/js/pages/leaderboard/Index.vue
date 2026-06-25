<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';

interface Entry {
    rank: number;
    id: number;
    name: string;
    level: number;
    species_count: number;
    is_current_user: boolean;
}

const props = defineProps<{ ranking: Entry[]; me: Entry | null }>();

const tabs = ['Global', 'Por especie', 'Amigos'];

const podium = computed(() => ({
    first: props.ranking.find((r) => r.rank === 1),
    second: props.ranking.find((r) => r.rank === 2),
    third: props.ranking.find((r) => r.rank === 3),
}));

const listRows = computed(() => props.ranking.filter((r) => r.rank > 3 && !r.is_current_user));
const meInList = computed(() => props.me && props.me.rank > 3);

function profileHref(e: Entry) {
    return e.is_current_user ? '/profile' : `/profile/${e.id}`;
}
</script>

<template>
    <Head title="Rankings" />

    <div class="head">
        <div class="top-gap" />
        <div class="screen-title">Rankings</div>
        <div class="tabs">
            <span v-for="(t, i) in tabs" :key="t" class="tab" :class="{ active: i === 0 }">{{ t }}</span>
        </div>
    </div>

    <!-- podium -->
    <div class="podium">
        <div class="col">
            <div class="pavatar silver" />
            <div class="pname">{{ podium.second?.name ?? '—' }}</div>
            <div class="bar second">2</div>
        </div>
        <div class="col">
            <div class="pavatar gold">
                <Icon name="crown" :size="20" color="var(--accent-pdx)" class="crown" />
            </div>
            <div class="pname">{{ podium.first?.name ?? '—' }}</div>
            <div class="bar first">1</div>
        </div>
        <div class="col">
            <div class="pavatar bronze" />
            <div class="pname">{{ podium.third?.name ?? '—' }}</div>
            <div class="bar third">3</div>
        </div>
    </div>

    <!-- list -->
    <div class="list">
        <Link v-for="r in listRows" :key="r.id" :href="profileHref(r)" class="row tappable">
            <span class="rank">{{ r.rank }}</span>
            <div class="ravatar" />
            <span class="rname">{{ r.name }}</span>
            <span class="rscore">{{ r.species_count }} esp.</span>
        </Link>

        <Link v-if="me && meInList" href="/profile" class="row me">
            <span class="rank me-rank">{{ me.rank }}</span>
            <div class="ravatar me-avatar" />
            <span class="rname strong">Tú · {{ me.name }}</span>
            <span class="rscore me-score">{{ me.species_count }} esp.</span>
        </Link>
    </div>

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 18px;
    background: var(--banner);
}
.top-gap {
    height: 16px;
}
.screen-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    padding: 4px 0 12px;
    color: var(--text);
}
.tabs {
    display: flex;
    gap: 8px;
    padding-bottom: 16px;
}
.tab {
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid var(--border-strong);
    color: var(--icon-muted);
    font-size: 12.5px;
    font-weight: 600;
}
.tab.active {
    background: rgba(45, 212, 191, 0.18);
    border-color: rgba(45, 212, 191, 0.5);
    color: var(--teal-chip-text);
}
.podium {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 10px;
    padding: 22px 18px 8px;
}
.col {
    text-align: center;
    flex: 1;
}
.pavatar {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    margin: 0 auto 7px;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 5px, var(--avatar-b) 5px, var(--avatar-b) 10px);
}
.pavatar.silver {
    border: 2px solid #cbd5e1;
}
.pavatar.bronze {
    border: 2px solid #cd7f32;
}
.pavatar.gold {
    position: relative;
    width: 66px;
    height: 66px;
    border: 2.5px solid var(--accent-pdx);
}
.crown {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
}
.pname {
    font-size: 11.5px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--text);
}
.bar {
    margin-top: 6px;
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 700;
}
.bar.first {
    height: 78px;
    padding-top: 7px;
    background: linear-gradient(180deg, var(--accent-pdx), #a86a10);
    color: #3a2700;
}
.bar.second {
    height: 54px;
    padding-top: 6px;
    background: linear-gradient(180deg, #2a4c62, #13344a);
    color: #cbd5e1;
}
.bar.third {
    height: 42px;
    padding-top: 5px;
    background: linear-gradient(180deg, #7a4a22, #13344a);
    color: #e8c39e;
}
.list {
    padding: 6px 18px;
}
.row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 4px;
    border-bottom: 1px solid var(--hairline);
    color: var(--text);
}
.rank {
    width: 20px;
    font-family: var(--font-display);
    font-weight: 600;
    color: var(--text-muted);
}
.ravatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 5px, var(--avatar-b) 5px, var(--avatar-b) 10px);
    flex: none;
}
.rname {
    flex: 1;
    font-size: 13.5px;
    font-weight: 600;
}
.rscore {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
}
.row.me {
    margin-top: 8px;
    padding: 12px;
    border-radius: 14px;
    border-bottom: 0;
    background: rgba(45, 212, 191, 0.12);
    border: 1px solid rgba(45, 212, 191, 0.45);
}
.me-rank {
    color: var(--teal);
}
.me-avatar {
    border: 1.5px solid var(--teal);
}
.rname.strong {
    font-weight: 700;
}
.me-score {
    color: var(--teal);
}
.bottom-spacer {
    height: 40px;
}
</style>
