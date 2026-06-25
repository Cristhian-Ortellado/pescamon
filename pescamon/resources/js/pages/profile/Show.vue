<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import StatCard from '@/components/pescadex/StatCard.vue';
import SpeciesCard from '@/components/pescadex/SpeciesCard.vue';
import XpBar from '@/components/pescadex/XpBar.vue';

interface ProfileUser {
    id: number;
    name: string;
    level: number;
    xp: number;
    catches_count: number;
    followers_count: number;
    following_count: number;
    is_own_profile: boolean;
    is_following: boolean;
}
interface PokedexEntry {
    id: number;
    name: string;
    slug: string;
    rarity: string;
    image_path: string | null;
    caught: boolean;
}

const props = defineProps<{
    profileUser: ProfileUser;
    pokedex: PokedexEntry[];
    achievements: Array<{ id: number; name: string }>;
    stats: { species_caught: number; species_total: number; record_kg: number };
    levelProgress: number;
    xpForNextLevel: number;
}>();

const handle = computed(() => '@' + props.profileUser.name.toLowerCase().replace(/\s+/g, ''));
const isEmpty = computed(() => props.profileUser.is_own_profile && props.stats.species_caught === 0);

const tab = ref<'all' | 'caught'>('all');
const shown = computed(() => (tab.value === 'caught' ? props.pokedex.filter((s) => s.caught) : props.pokedex));
const featured = computed(() => props.pokedex.filter((s) => s.caught).slice(0, 6));

function openSpecies(entry: PokedexEntry) {
    if (entry.caught) router.visit(`/species/${entry.slug}`);
}
function toggleFollow() {
    router.post(`/users/${props.profileUser.id}/follow`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="profileUser.name" />

    <!-- ===== OWN PROFILE ===== -->
    <template v-if="profileUser.is_own_profile">
        <div class="banner">
            <div class="top-gap" />
            <div class="head-row">
                <div class="who">
                    <div class="avatar"><span class="level-tag">Nv {{ profileUser.level }}</span></div>
                    <div>
                        <div class="display-name">{{ profileUser.name }}</div>
                        <div class="handle">{{ handle }}</div>
                    </div>
                </div>
                <Link href="/settings" class="icon-btn">
                    <Icon name="settings" :size="21" color="var(--icon-muted)" />
                </Link>
            </div>
            <div class="xp-block">
                <div class="xp-row">
                    <span class="xp-level">Nivel {{ profileUser.level }}</span>
                    <span>{{ profileUser.xp.toLocaleString('es') }} / {{ xpForNextLevel.toLocaleString('es') }} XP</span>
                </div>
                <XpBar :progress="levelProgress" />
            </div>
        </div>

        <div v-if="isEmpty" class="empty">
            <div class="emblem">
                <Icon name="fish" :size="54" color="#2c5266" />
                <span class="plus"><Icon name="plus" :size="22" color="var(--accent-ink)" /></span>
            </div>
            <div class="empty-title">Tu Pokédex está vacía</div>
            <div class="empty-text">
                Registra tu primera captura para desbloquear tu primera carta de especie y empezar a ganar XP.
            </div>
            <Link href="/catches/create" class="cta">
                <Icon name="camera" :size="19" /> Registrar primera captura
            </Link>
            <Link href="/dashboard" class="ghost">
                <Icon name="map" :size="16" /> O explora el mapa de capturas
            </Link>
        </div>

        <template v-else>
            <div class="stats">
                <StatCard :value="String(stats.species_caught)" :suffix="'/' + stats.species_total" label="Especies" />
                <StatCard :value="String(stats.record_kg)" suffix="kg" label="Récord" />
                <StatCard :value="String(profileUser.followers_count)" label="Seguidores" accent icon="users" />
            </div>

            <Link href="/achievements" class="achievements-link">
                <div class="al-icon"><Icon name="award" :size="19" color="var(--accent-pdx)" /></div>
                <div class="al-text">
                    <div class="al-title">Logros e insignias</div>
                    <div class="al-sub">{{ achievements.length }} desbloqueados</div>
                </div>
                <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
            </Link>

            <div class="pdx-head">
                <div class="pdx-title">Mi Pokédex</div>
                <div class="pdx-tabs">
                    <span class="t" :class="{ active: tab === 'all' }" @click="tab = 'all'">Todas</span>
                    <span class="t" :class="{ active: tab === 'caught' }" @click="tab = 'caught'">Capturadas</span>
                </div>
            </div>

            <div class="grid">
                <SpeciesCard
                    v-for="s in shown"
                    :key="s.id"
                    :name="s.name"
                    :rarity="s.rarity"
                    :caught="s.caught"
                    :image-path="s.image_path"
                    @click="openSpecies(s)"
                />
            </div>
            <div class="bottom-spacer" />
        </template>
    </template>

    <!-- ===== PUBLIC PROFILE ===== -->
    <template v-else>
        <div class="pub-banner">
            <div class="top-gap" />
            <div class="top-bar">
                <button class="ic" @click="router.visit('/leaderboard')"><Icon name="arrow-left" :size="23" /></button>
                <Icon name="more-horizontal" :size="22" color="var(--icon-muted)" />
            </div>
            <div class="pub-who">
                <div class="pub-avatar"><span class="level-tag center">Nv {{ profileUser.level }}</span></div>
                <div class="pub-name">{{ profileUser.name }}</div>
                <div class="handle">{{ handle }}</div>
                <div class="actions">
                    <button class="follow" :class="{ following: profileUser.is_following }" @click="toggleFollow">
                        <Icon :name="profileUser.is_following ? 'check' : 'user-plus'" :size="17" />
                        {{ profileUser.is_following ? 'Siguiendo' : 'Seguir' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="stats">
            <StatCard :value="String(stats.species_caught)" :suffix="'/' + stats.species_total" label="Especies" />
            <StatCard :value="String(stats.record_kg)" suffix="kg" label="Récord" />
            <StatCard :value="String(profileUser.followers_count)" label="Seguidores" />
        </div>

        <div class="section-head">
            <div class="section-title">Colección destacada</div>
        </div>
        <div class="grid">
            <SpeciesCard
                v-for="s in featured"
                :key="s.id"
                :name="s.name"
                :rarity="s.rarity"
                :caught="true"
                :image-path="s.image_path"
                @click="openSpecies(s)"
            />
            <p v-if="featured.length === 0" class="muted-note">Aún no tiene capturas verificadas.</p>
        </div>
        <div class="bottom-spacer" />
    </template>
</template>

<style scoped>
.banner,
.pub-banner {
    position: relative;
    padding: 0 20px 18px;
    background: var(--banner);
}
.top-gap {
    height: 16px;
}
.head-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-top: 6px;
}
.who {
    display: flex;
    gap: 14px;
    align-items: center;
}
.avatar {
    position: relative;
    width: 66px;
    height: 66px;
    border-radius: 50%;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 6px, var(--avatar-b) 6px, var(--avatar-b) 12px);
    border: 2.5px solid var(--teal);
    flex: none;
}
.level-tag {
    position: absolute;
    bottom: -6px;
    right: -6px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 999px;
    border: 2px solid var(--bg-frame);
}
.level-tag.center {
    right: auto;
    left: 50%;
    transform: translateX(-50%);
}
.display-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
.handle {
    font-size: 13px;
    color: var(--text-muted);
}
.icon-btn {
    display: flex;
}
.xp-block {
    margin-top: 16px;
}
.xp-row {
    display: flex;
    justify-content: space-between;
    font-size: 11.5px;
    color: var(--icon-muted);
    margin-bottom: 6px;
}
.xp-level {
    font-weight: 600;
    color: var(--text-strong);
}
.empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 50px 34px 0;
}
.emblem {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle at 50% 40%, #103a4e, #0b2636);
    border: 2px dashed #2a4c62;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
}
.plus {
    position: absolute;
    bottom: 4px;
    right: 6px;
    background: var(--accent-pdx);
    border-radius: 50%;
    padding: 5px;
    display: flex;
}
.empty-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
.empty-text {
    font-size: 14px;
    color: var(--text-muted);
    margin-top: 10px;
    line-height: 1.5;
}
.cta {
    margin-top: 24px;
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15.5px;
    padding: 14px 22px;
    border-radius: 999px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.45);
}
.ghost {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 18px;
    font-size: 13px;
    color: var(--teal);
    font-weight: 600;
}
.stats {
    display: flex;
    gap: 10px;
    padding: 16px 20px 6px;
}
.achievements-link {
    display: flex;
    align-items: center;
    gap: 11px;
    margin: 12px 20px 0;
    padding: 11px 12px;
    border: 1px solid var(--border-pdx);
    border-radius: 14px;
    background: var(--surface-alt);
    color: var(--text);
}
.al-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(245, 165, 36, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
}
.al-text {
    flex: 1;
}
.al-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
}
.al-sub {
    font-size: 11.5px;
    color: var(--text-muted);
}
.pdx-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px 10px;
}
.pdx-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 17px;
    color: var(--text);
}
.pdx-tabs {
    display: flex;
    gap: 6px;
    font-size: 11.5px;
}
.pdx-tabs .t {
    padding: 5px 11px;
    border-radius: 999px;
    border: 1px solid var(--border-strong);
    color: var(--icon-muted);
    font-weight: 600;
    cursor: pointer;
}
.pdx-tabs .t.active {
    background: rgba(45, 212, 191, 0.16);
    border-color: rgba(45, 212, 191, 0.45);
    color: var(--teal-chip-text);
}
.grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    padding: 0 20px;
}
.muted-note {
    grid-column: 1 / -1;
    color: var(--text-muted);
    font-size: 13px;
    text-align: center;
    padding: 20px 0;
}
.bottom-spacer {
    height: 40px;
}

/* public */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 0 8px;
}
.ic {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
    color: var(--text);
}
.pub-who {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-top: 4px;
}
.pub-avatar {
    position: relative;
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: repeating-linear-gradient(135deg, var(--avatar-a), var(--avatar-a) 6px, var(--avatar-b) 6px, var(--avatar-b) 12px);
    border: 3px solid var(--accent-pdx);
}
.pub-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    margin-top: 14px;
    color: var(--text);
}
.actions {
    display: flex;
    gap: 11px;
    margin-top: 16px;
}
.follow {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--teal);
    color: var(--teal-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    padding: 10px 22px;
    border: 0;
    border-radius: 999px;
}
.follow.following {
    background: none;
    color: var(--teal);
    border: 1px solid var(--teal);
}
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px 10px;
}
.section-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 17px;
    color: var(--text);
}
</style>
