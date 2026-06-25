<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import CatchPhoto from '@/components/pescadex/CatchPhoto.vue';
import Avatar from '@/components/pescadex/Avatar.vue';
import { timeAgo } from '@/lib/pescadex';

interface Post {
    id: number;
    author: { id: number; name: string; level: number };
    species: { id: number; name: string; slug: string; rarity: string };
    weight_kg: number;
    location_name: string | null;
    caught_at: string;
    image_path: string | null;
    likes_count: number;
    comments_count: number;
    is_liked: boolean;
    is_personal_best: boolean;
    is_own: boolean;
    is_following: boolean;
}

defineProps<{ posts: Post[]; unreadCount: number }>();

function openCatch(id: number) {
    router.visit(`/catches/${id}`);
}
function toggleLike(id: number) {
    router.post(`/catches/${id}/like`, {}, { preserveScroll: true });
}
function follow(id: number) {
    router.post(`/users/${id}/follow`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Feed" />

    <div class="head-pad">
        <div class="top-gap" />
        <div class="head-row">
            <span class="screen-title">Feed</span>
            <div class="head-actions">
                <Link href="/leaderboard"><Icon name="user-plus" :size="21" color="var(--icon-muted)" /></Link>
                <Link href="/notifications" class="icon-btn">
                    <Icon name="bell" :size="21" color="var(--icon-muted)" />
                    <span v-if="unreadCount" class="badge-dot" />
                </Link>
            </div>
        </div>
    </div>

    <div v-if="posts.length === 0" class="empty">
        <Icon name="newspaper" :size="40" color="var(--text-locked)" />
        <p>Aún no hay capturas en tu feed.</p>
        <Link href="/catches/create" class="empty-cta">Registrar una captura</Link>
    </div>

    <article
        v-for="(post, i) in posts"
        :key="post.id"
        class="post"
        @click="openCatch(post.id)"
    >
        <div class="post-head">
            <Link
                :href="post.is_own ? '/profile' : `/profile/${post.author.id}`"
                class="author-tap"
                @click.stop
            >
                <Avatar :size="38" />
                <div class="author">
                    <div class="name">{{ post.author.name }}</div>
                    <div class="meta">
                        <template v-if="post.location_name">{{ post.location_name }} · </template>{{ timeAgo(post.caught_at) }}
                    </div>
                </div>
            </Link>
            <button
                v-if="!post.is_own && !post.is_following"
                class="follow"
                @click.stop="follow(post.author.id)"
            >
                Seguir
            </button>
            <Icon v-else name="more-horizontal" :size="20" color="var(--text-faint)" />
        </div>

        <div class="media" :style="{ height: i === 0 ? '240px' : '170px' }">
            <CatchPhoto :path="post.image_path" :rarity="post.species.rarity" :stripe="10" :alt="post.species.name" />
            <div class="species-tag">
                <span class="st-name">{{ post.species.name }}</span>
                <RarityBadge :rarity="post.species.rarity" variant="short" />
            </div>
            <div class="weight-tag">{{ post.weight_kg }} kg</div>
            <div v-if="post.is_personal_best" class="record-tag">🏆 Récord</div>
        </div>

        <div class="actions">
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

<style scoped>
.head-pad {
    padding: 0 18px;
}
.top-gap {
    height: 16px;
}
.head-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0 10px;
}
.screen-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    color: var(--text);
}
.head-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}
.icon-btn {
    position: relative;
    display: flex;
}
.badge-dot {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent-pdx);
    border: 1.5px solid var(--bg-frame);
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
.empty-cta {
    margin-top: 6px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 999px;
}
.post {
    border-top: 1px solid var(--hairline);
    padding: 13px 18px;
    cursor: pointer;
}
.post-head {
    display: flex;
    align-items: center;
    gap: 10px;
}
.author-tap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
.author {
    flex: 1;
    min-width: 0;
}
.author .name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}
.author .meta {
    font-size: 11.5px;
    color: var(--text-muted);
}
.follow {
    font-size: 12px;
    color: var(--teal);
    font-weight: 600;
    border: 1px solid #1f6f6a;
    border-radius: 999px;
    padding: 4px 11px;
    background: none;
}
.media {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    margin-top: 11px;
}
.species-tag {
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
.st-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 12.5px;
    color: #fff;
}
.weight-tag {
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
.record-tag {
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
.actions {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-top: 11px;
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
.bottom-spacer {
    height: 40px;
}
</style>
