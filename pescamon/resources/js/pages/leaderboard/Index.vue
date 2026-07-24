<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';

interface Entry {
    rank: number;
    id: number;
    name: string;
    avatar: string | null;
    level: number;
    species_count: number;
    is_current_user: boolean;
}

interface Species {
    id: number;
    name: string;
    scientific_name: string;
    slug: string;
    rarity: string;
    average_weight_kg: number | null;
}

interface RankedSpecies {
    rank: number,
    id: number,
    name: string,
    avatar: string | null,
    species_id: number,
    species_name: string,
    total_xp: number,
    is_current_user: boolean,
}

const props = defineProps<{ ranking: Entry[]; species: Species[], rankedSpecies: RankedSpecies[], me: Entry | null; scope: 'global' | 'friends' | 'species' }>();

const tabs = [
    { key: 'global', label: 'Global' },
    { key: 'species', label: 'Por especie' },
    { key: 'friends', label: 'Amigos' },
] as const;

function selectTab(key: (typeof tabs)[number]['key']) {
    if (key === props.scope) return;
    router.get('/leaderboard', { scope: key }, { preserveScroll: true, preserveState: true, replace: true });
}

const showFriendsHint = computed(() => props.scope === 'friends' && props.ranking.length <= 1);
const showSearchBySpeciesBar = computed(() => props.scope === 'species');
const specie_id = ref(props.species[0]?.id ?? null);

const podium = computed(() => ({
    first: props.ranking.find((r) => r.rank === 1),
    second: props.ranking.find((r) => r.rank === 2),
    third: props.ranking.find((r) => r.rank === 3),
}));

const podiumBySpecies = computed(() => ({
    first: props.rankedSpecies.find((r) => r.rank === 1 && r.species_id === specie_id.value),
    second: props.rankedSpecies.find((r) => r.rank === 2 && r.species_id === specie_id.value),
    third: props.rankedSpecies.find((r) => r.rank === 3 && r.species_id === specie_id.value),
}));

const listRows = computed(() => props.ranking.filter((r) => r.rank > 3 && !r.is_current_user));
const meInList = computed(() => props.me && props.me.rank > 3);

const listRowsBySpecies = computed(() => props.rankedSpecies.filter((r) => r.rank > 3 && !r.is_current_user && r.species_id === specie_id.value));
const meInListBySpecies = computed(() => props.rankedSpecies.some((r) => r.is_current_user && r.species_id === specie_id.value && r.rank > 3));
const meBySpecies = computed(() => props.rankedSpecies.find((r) => r.is_current_user && r.species_id === specie_id.value && r.rank > 3));

function profileHref(e: {id: number, is_current_user: boolean}) {
    return e.is_current_user ? '/profile' : `/profile/${e.id}`;
}

</script>

<template>
    <Head title="Rankings" />

    <div class="head">
        <div class="top-gap" />
        <div class="screen-title">Rankings</div>
        <div class="tabs">
            <span
                v-for="t in tabs"
                :key="t.key"
                class="tab"
                :class="{ active: t.key === scope}"
                @click="selectTab(t.key)"
            >{{ t.label }}</span>
        </div>
    </div>

    <div v-if="showFriendsHint" class="empty">
        <Icon name="user-plus" :size="34" color="var(--text-locked)" />
        <p>Todavía no sigues a otros pescadores.</p>
    </div>

    <div v-else-if="showSearchBySpeciesBar">
        <!-- podium -->
        <div class="podium">
            <component :is="podiumBySpecies.second ? Link : 'div'" :href="podiumBySpecies.second ? profileHref(podiumBySpecies.second) : undefined" class="col" :class="{ tappable: podiumBySpecies.second }">
                <div class="pavatar silver" :class="{ 'has-image': podiumBySpecies.second?.avatar }">
                    <img v-if="podiumBySpecies.second?.avatar" :src="podiumBySpecies.second.avatar" :alt="podiumBySpecies.second.name" class="pav-img" />
                </div>
                <div class="pname">{{ podiumBySpecies.second?.name ?? '—' }}</div>
                <div class="bar second">2</div>
            </component>
            <component :is="podiumBySpecies.first ? Link : 'div'" :href="podiumBySpecies.first ? profileHref(podiumBySpecies.first) : undefined" class="col" :class="{ tappable: podiumBySpecies.first }">
                <div class="pavatar gold" :class="{ 'has-image': podiumBySpecies.first?.avatar }">
                    <img v-if="podiumBySpecies.first?.avatar" :src="podiumBySpecies.first.avatar" :alt="podiumBySpecies.first.name" class="pav-img" />
                    <Icon name="crown" :size="20" color="var(--accent-pdx)" class="crown" />
                </div>
                <div class="pname">{{ podiumBySpecies.first?.name ?? '—' }}</div>
                <div class="bar first">1</div>
            </component>
            <component :is="podiumBySpecies.third ? Link : 'div'" :href="podiumBySpecies.third ? profileHref(podiumBySpecies.third) : undefined" class="col" :class="{ tappable: podiumBySpecies.third }">
                <div class="pavatar bronze" :class="{ 'has-image': podiumBySpecies.third?.avatar }">
                    <img v-if="podiumBySpecies.third?.avatar" :src="podiumBySpecies.third.avatar" :alt="podiumBySpecies.third.name" class="pav-img" />
                </div>
                <div class="pname">{{ podiumBySpecies.third?.name ?? '—' }}</div>
                <div class="bar third">3</div>
            </component>
        </div>

        <!-- species filter -->
        <div class="species-chip-wrap">
            <div class="species-chip">
                <Icon name="fish" :size="16" color="var(--teal)" />
                <select v-model="specie_id" class="species-select">
                    <option v-for="s in props.species" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <Icon name="chevron-down" :size="14" color="var(--text-faint)" />
            </div>
        </div>

        <!-- list -->
        <div class="list">
            <Link v-for="r in listRowsBySpecies" :key="r.id" :href="profileHref(r)" class="row tappable">
                <span class="rank">{{ r.rank }}</span>
                <div class="ravatar" :class="{ 'has-image': r.avatar }">
                    <img v-if="r.avatar" :src="r.avatar" :alt="r.name" class="rav-img" />
                </div>
                <span class="rname">{{ r.name }}</span>
                <span class="rscore">{{ r.total_xp }} esp.</span>
            </Link>

            <Link v-if="meInListBySpecies" href="/profile" class="row me">
                <span class="rank me-rank">{{ meBySpecies?.rank }}</span>
                <div class="ravatar me-avatar" :class="{ 'has-image': meBySpecies?.avatar }">
                    <img v-if="meBySpecies?.avatar" :src="meBySpecies.avatar" :alt="meBySpecies.name" class="rav-img" />
                </div>
                <span class="rname strong">Tú · {{ meBySpecies?.name }}</span>
                <span class="rscore me-score">{{ meBySpecies?.total_xp }} esp.</span>
            </Link>
        </div>

    </div>


    <template v-else>
        <!-- podium -->
        <div class="podium">
            <component :is="podium.second ? Link : 'div'" :href="podium.second ? profileHref(podium.second) : undefined" class="col" :class="{ tappable: podium.second }">
                <div class="pavatar silver" :class="{ 'has-image': podium.second?.avatar }">
                    <img v-if="podium.second?.avatar" :src="podium.second.avatar" :alt="podium.second.name" class="pav-img" />
                </div>
                <div class="pname">{{ podium.second?.name ?? '—' }}</div>
                <div class="bar second">2</div>
            </component>
            <component :is="podium.first ? Link : 'div'" :href="podium.first ? profileHref(podium.first) : undefined" class="col" :class="{ tappable: podium.first }">
                <div class="pavatar gold" :class="{ 'has-image': podium.first?.avatar }">
                    <img v-if="podium.first?.avatar" :src="podium.first.avatar" :alt="podium.first.name" class="pav-img" />
                    <Icon name="crown" :size="20" color="var(--accent-pdx)" class="crown" />
                </div>
                <div class="pname">{{ podium.first?.name ?? '—' }}</div>
                <div class="bar first">1</div>
            </component>
            <component :is="podium.third ? Link : 'div'" :href="podium.third ? profileHref(podium.third) : undefined" class="col" :class="{ tappable: podium.third }">
                <div class="pavatar bronze" :class="{ 'has-image': podium.third?.avatar }">
                    <img v-if="podium.third?.avatar" :src="podium.third.avatar" :alt="podium.third.name" class="pav-img" />
                </div>
                <div class="pname">{{ podium.third?.name ?? '—' }}</div>
                <div class="bar third">3</div>
            </component>
        </div>

        <!-- list -->
        <div class="list">
            <Link v-for="r in listRows" :key="r.id" :href="profileHref(r)" class="row tappable">
                <span class="rank">{{ r.rank }}</span>
                <div class="ravatar" :class="{ 'has-image': r.avatar }">
                    <img v-if="r.avatar" :src="r.avatar" :alt="r.name" class="rav-img" />
                </div>
                <span class="rname">{{ r.name }}</span>
                <span class="rscore">{{ r.species_count }} esp.</span>
            </Link>

            <Link v-if="me && meInList" href="/profile" class="row me">
                <span class="rank me-rank">{{ me.rank }}</span>
                <div class="ravatar me-avatar" :class="{ 'has-image': me.avatar }">
                    <img v-if="me.avatar" :src="me.avatar" :alt="me.name" class="rav-img" />
                </div>
                <span class="rname strong">Tú · {{ me.name }}</span>
                <span class="rscore me-score">{{ me.species_count }} esp.</span>
            </Link>
        </div>
    </template>

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
    cursor: pointer;
}
.tab.active {
    background: rgba(45, 212, 191, 0.18);
    border-color: rgba(45, 212, 191, 0.5);
    color: var(--teal-chip-text);
}
.tab.disabled {
    opacity: 0.5;
    cursor: default;
}
.empty {
    text-align: center;
    color: var(--text-muted);
    padding: 60px 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
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
.tappable {
    cursor: pointer;
}
.tappable:active {
    opacity: 0.7;
}
.pavatar {
    position: relative;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    margin: 0 auto 7px;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 5px, var(--avatar-b) 5px, var(--avatar-b) 10px);
}
.pavatar.has-image {
    background: none;
}
.pav-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
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
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 5px, var(--avatar-b) 5px, var(--avatar-b) 10px);
    flex: none;
}
.ravatar.has-image {
    background: none;
}
.rav-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
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
.species-chip-wrap {
    display: flex;
    justify-content: flex-end;
    padding: 0 18px 4px;
}
.species-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--surface-alt);
    border: 1px solid var(--field-border);
    border-radius: 999px;
    padding: 6px 14px;
}
.species-select {
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    appearance: none;
}
.species-select option {
    color: #15303d;
}
</style>
