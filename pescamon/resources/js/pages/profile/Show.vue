<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CatchPhoto from '@/components/pescadex/CatchPhoto.vue';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import ShareCatchButton from '@/components/pescadex/ShareCatchButton.vue';
import SpeciesCard from '@/components/pescadex/SpeciesCard.vue';
import StatCard from '@/components/pescadex/StatCard.vue';
import XpBar from '@/components/pescadex/XpBar.vue';
import { goBack } from '@/lib/navigation';
import { timeAgo } from '@/lib/pescadex';

interface ProfileUser {
    id: number;
    name: string;
    avatar: string | null;
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
interface Post {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    weight_kg: number;
    location_name: string | null;
    caught_at: string;
    image_path: string | null;
    likes_count: number;
    comments_count: number;
    is_liked: boolean;
    is_personal_best: boolean;
}

const props = defineProps<{
    profileUser: ProfileUser;
    pokedex: PokedexEntry[];
    posts: Post[];
    achievements: Array<{ id: number; name: string }>;
    stats: { species_caught: number; species_total: number; record_kg: number };
    levelProgress: number;
    xpForNextLevel: number;
}>();

const page = usePage();
const isSuperadmin = computed(
    () => (page.props.auth as { user?: { is_superadmin?: boolean } })?.user?.is_superadmin ?? false,
);

const handle = computed(() => '@' + props.profileUser.name.toLowerCase().replace(/\s+/g, ''));
const isEmpty = computed(() => props.profileUser.is_own_profile && props.stats.species_caught === 0);

const tab = ref<'all' | 'caught'>('all');
const shown = computed(() => (tab.value === 'caught' ? props.pokedex.filter((s) => s.caught) : props.pokedex));
const caughtSpecies = computed(() => props.pokedex.filter((s) => s.caught));

function openSpecies(entry: PokedexEntry) {
    if (!entry.caught) {
        return;
    }

    // On another angler's profile, carry their id so the species page shows
    // THAT user's history instead of the viewer's.
    const url = props.profileUser.is_own_profile
        ? `/species/${entry.slug}`
        : `/species/${entry.slug}?user=${props.profileUser.id}`;
    router.visit(url);
}
function toggleFollow() {
    router.post(`/users/${props.profileUser.id}/follow`, {}, { preserveScroll: true });
}
function openCatch(id: number) {
    router.visit(`/catches/${id}`);
}
function toggleLike(id: number) {
    router.post(`/catches/${id}/like`, {}, { preserveScroll: true });
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
                    <div class="avatar" :class="{ 'has-image': profileUser.avatar }">
                        <img v-if="profileUser.avatar" :src="profileUser.avatar" :alt="profileUser.name" class="avatar-img" />
                        <span class="level-tag">Nv {{ profileUser.level }}</span>
                    </div>
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
            <div class="empty-title">Tu Pescadex está vacía</div>
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

            <Link href="/leaderboard" class="achievements-link">
                <div class="al-icon rank"><Icon name="trophy" :size="19" color="var(--teal)" /></div>
                <div class="al-text">
                    <div class="al-title">Rankings</div>
                    <div class="al-sub">Tabla de clasificación de pescadores</div>
                </div>
                <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
            </Link>

            <Link v-if="isSuperadmin" href="/admin/products" class="achievements-link">
                <div class="al-icon admin"><Icon name="package" :size="19" color="var(--accent-pdx)" /></div>
                <div class="al-text">
                    <div class="al-title">Administrar tienda</div>
                    <div class="al-sub">Gestionar productos de la tienda</div>
                </div>
                <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
            </Link>

            <div class="pdx-head">
                <div class="pdx-title">Mi Pescadex</div>
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

            <template v-if="posts.length">
                <div class="pdx-head">
                    <div class="pdx-title">Capturas recientes</div>
                </div>
                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="post"
                    @click="openCatch(post.id)"
                >
                    <div class="media">
                        <CatchPhoto :path="post.image_path" :rarity="post.species.rarity" :stripe="10" :alt="post.species.name" />
                        <div class="species-tag">
                            <span class="st-name">{{ post.species.name }}</span>
                            <RarityBadge :rarity="post.species.rarity" variant="short" />
                        </div>
                        <div class="weight-tag">{{ post.weight_kg }} kg</div>
                        <div v-if="post.is_personal_best" class="record-tag">🏆 Récord</div>
                    </div>

                    <div class="post-meta">
                        <template v-if="post.location_name">{{ post.location_name }} · </template>{{ timeAgo(post.caught_at) }}
                    </div>

                    <div class="actions-row">
                        <button class="action" :class="{ liked: post.is_liked }" @click.stop="toggleLike(post.id)">
                            <Icon name="heart" :size="21" :color="post.is_liked ? 'var(--accent-pdx)' : 'currentColor'" :fill="post.is_liked ? 'var(--accent-pdx)' : 'none'" />
                            <span>{{ post.likes_count }}</span>
                        </button>
                        <div class="action muted">
                            <Icon name="message-circle" :size="21" />
                            <span>{{ post.comments_count }}</span>
                        </div>
                        <div class="actions-spacer" />
                        <ShareCatchButton :post="post" />
                    </div>
                </article>
            </template>

            <div class="bottom-spacer" />

            <Link href="/catches/create" class="fab">
                <Icon name="plus" :size="24" color="var(--accent-ink)" />
            </Link>
        </template>
    </template>

    <!-- ===== PUBLIC PROFILE ===== -->
    <template v-else>
        <div class="pub-banner">
            <div class="top-gap" />
            <div class="top-bar">
                <button class="ic" @click="goBack('/feed')"><Icon name="arrow-left" :size="23" /></button>
                <Icon name="more-horizontal" :size="22" color="var(--icon-muted)" />
            </div>
            <div class="pub-who">
                <div class="pub-avatar" :class="{ 'has-image': profileUser.avatar }">
                    <img v-if="profileUser.avatar" :src="profileUser.avatar" :alt="profileUser.name" class="avatar-img" />
                    <span class="level-tag center">Nv {{ profileUser.level }}</span>
                </div>
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
            <div class="section-title">Especies capturadas</div>
        </div>
        <div class="grid">
            <SpeciesCard
                v-for="s in caughtSpecies"
                :key="s.id"
                :name="s.name"
                :rarity="s.rarity"
                :caught="true"
                :image-path="s.image_path"
                @click="openSpecies(s)"
            />
            <p v-if="caughtSpecies.length === 0" class="muted-note">Aún no tiene capturas verificadas.</p>
        </div>

        <div class="section-head">
            <div class="section-title">Capturas recientes</div>
        </div>
        <p v-if="posts.length === 0" class="muted-note solo">Todavía no ha publicado capturas.</p>
        <article
            v-for="post in posts"
            :key="post.id"
            class="post"
            @click="openCatch(post.id)"
        >
            <div class="media">
                <CatchPhoto :path="post.image_path" :rarity="post.species.rarity" :stripe="10" :alt="post.species.name" />
                <div class="species-tag">
                    <span class="st-name">{{ post.species.name }}</span>
                    <RarityBadge :rarity="post.species.rarity" variant="short" />
                </div>
                <div class="weight-tag">{{ post.weight_kg }} kg</div>
                <div v-if="post.is_personal_best" class="record-tag">🏆 Récord</div>
            </div>

            <div class="post-meta">
                <template v-if="post.location_name">{{ post.location_name }} · </template>{{ timeAgo(post.caught_at) }}
            </div>

            <div class="actions-row">
                <button class="action" :class="{ liked: post.is_liked }" @click.stop="toggleLike(post.id)">
                    <Icon name="heart" :size="21" :color="post.is_liked ? 'var(--accent-pdx)' : 'currentColor'" :fill="post.is_liked ? 'var(--accent-pdx)' : 'none'" />
                    <span>{{ post.likes_count }}</span>
                </button>
                <div class="action muted">
                    <Icon name="message-circle" :size="21" />
                    <span>{{ post.comments_count }}</span>
                </div>
            </div>
        </article>

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
.avatar.has-image {
    background: none;
}
.avatar-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
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
.al-icon.rank {
    background: rgba(45, 212, 191, 0.16);
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
.pub-avatar.has-image {
    background: none;
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
.muted-note.solo {
    padding: 8px 20px 20px;
}
.post {
    padding: 4px 20px 16px;
    cursor: pointer;
}
.post .media {
    position: relative;
    aspect-ratio: 4 / 3;
    border-radius: 16px;
    overflow: hidden;
    background: #06161c;
}
.post .species-tag {
    position: absolute;
    left: 10px;
    top: 10px;
    display: flex;
    gap: 6px;
    align-items: center;
    background: rgba(6, 22, 32, 0.78);
    border: 1px solid #2a4c62;
    border-radius: 999px;
    padding: 5px 10px;
}
.post .st-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 12.5px;
    color: #fff;
}
.post .weight-tag {
    position: absolute;
    right: 10px;
    bottom: 10px;
    background: rgba(6, 22, 32, 0.78);
    border: 1px solid #2a4c62;
    border-radius: 10px;
    padding: 6px 10px;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    color: #fff;
}
.post .record-tag {
    position: absolute;
    left: 10px;
    bottom: 10px;
    background: rgba(245, 165, 36, 0.92);
    color: var(--accent-ink);
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 11px;
    font-weight: 700;
}
.post-meta {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 9px;
}
.actions-row {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-top: 9px;
}
.actions-spacer {
    flex: 1;
}
.action {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text);
    background: none;
    border: 0;
    font-family: inherit;
}
.action span {
    font-size: 13px;
    font-weight: 600;
}
.action.muted {
    color: var(--text-soft);
}
.action.liked {
    color: var(--accent-pdx);
}
.fab {
    display: none;
}
@media (max-width: 1023px) {
    .fab {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        bottom: calc(84px + env(safe-area-inset-bottom));
        right: 20px;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--accent-pdx);
        box-shadow: 0 8px 20px rgba(245, 165, 36, 0.45);
        z-index: 30;
    }
}
</style>
