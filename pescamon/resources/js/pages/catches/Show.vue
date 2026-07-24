<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import CatchPhoto from '@/components/pescadex/CatchPhoto.vue';
import Avatar from '@/components/pescadex/Avatar.vue';
import { goBack } from '@/lib/navigation';
import { formatDateEs, methodLabel, waterLabel } from '@/lib/pescadex';

interface Comment {
    id: number;
    body: string;
    user: { id: number; name: string; avatar: string | null };
    created_at: string;
}
interface CatchData {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    user: { id: number; name: string; level: number; avatar: string | null };
    weight_kg: string;
    length_cm: string | null;
    image_path: string;
    caught_at: string;
    method: string;
    water_body_type: string;
    bait_used: string | null;
    location_name: string | null;
    notes: string | null;
    status: string;
    is_personal_best: boolean;
    likes_count: number;
    is_liked: boolean;
    comments: Comment[];
}

const props = defineProps<{ catch: CatchData }>();
const item = computed(() => props.catch);

const isLiked = ref(item.value.is_liked);
const likesCount = ref(item.value.likes_count);

const commentForm = useForm({ body: '' });

function toggleLike() {
    router.post(`/catches/${item.value.id}/like`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isLiked.value = !isLiked.value;
            likesCount.value += isLiked.value ? 1 : -1;
        },
    });
}
function submitComment() {
    commentForm.post(`/catches/${item.value.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}
</script>

<template>
    <Head :title="`Captura de ${item.species.name}`" />

    <div class="screen">
        <!-- hero -->
        <div class="hero">
            <CatchPhoto :path="item.image_path" :rarity="item.species.rarity" :stripe="11" :alt="item.species.name" />
            <div class="hero-fade" />
            <div class="hero-actions">
                <button class="round" @click="goBack('/feed')"><Icon name="arrow-left" :size="20" /></button>
                <Link :href="`/species/${item.species.slug}`" class="round"><Icon name="fish" :size="18" /></Link>
            </div>
            <div class="species-tag">
                <span class="st-name">{{ item.species.name }}</span>
                <RarityBadge :rarity="item.species.rarity" variant="short" />
            </div>
            <div v-if="item.status === 'pending'" class="pending">⏳ Pendiente de verificación</div>
        </div>

        <div class="content">
            <Link :href="`/profile/${item.user.id}`" class="author">
                <Avatar :size="40" ring="teal" :src="item.user.avatar" :alt="item.user.name" />
                <div>
                    <div class="name">{{ item.user.name }}</div>
                    <div class="when">{{ formatDateEs(item.caught_at, true) }}</div>
                </div>
            </Link>

            <div class="stat-row">
                <div class="stat"><div class="v accent">{{ item.weight_kg }} kg</div><div class="l">Peso</div></div>
                <div class="stat"><div class="v">{{ item.length_cm ?? '—' }}<span v-if="item.length_cm"> cm</span></div><div class="l">Longitud</div></div>
                <div class="stat"><div class="v small">{{ methodLabel(item.method) }}</div><div class="l">Método</div></div>
            </div>

            <div v-if="item.is_personal_best" class="xp-banner">
                <Icon name="trophy" :size="22" color="var(--accent-pdx)" />
                <div class="xp-text"><b>¡Récord personal!</b><span> tu mejor {{ item.species.name }}.</span></div>
            </div>

            <div class="meta">
                <p v-if="item.bait_used">🪱 Cebo: <span>{{ item.bait_used }}</span></p>
                <p>🌊 {{ waterLabel(item.water_body_type) }}</p>
                <p v-if="item.location_name">📍 {{ item.location_name }}</p>
            </div>

            <p v-if="item.notes" class="notes">{{ item.notes }}</p>

            <div class="likes">
                <button class="like" :class="{ on: isLiked }" @click="toggleLike">
                    <Icon name="heart" :size="22" :color="isLiked ? 'var(--accent-pdx)' : 'currentColor'" :fill="isLiked ? 'var(--accent-pdx)' : 'none'" />
                    <span>{{ likesCount }} me gusta</span>
                </button>
                <div class="like muted"><Icon name="message-circle" :size="22" /><span>{{ item.comments.length }}</span></div>
            </div>

            <!-- comments -->
            <div class="comments">
                <Link
                    v-for="c in item.comments"
                    :key="c.id"
                    :href="`/profile/${c.user.id}`"
                    class="comment"
                >
                    <Avatar :size="34" :src="c.user.avatar" :alt="c.user.name" />
                    <div class="c-content">
                        <div class="c-name">{{ c.user.name }}</div>
                        <div class="c-body">{{ c.body }}</div>
                    </div>
                </Link>
                <p v-if="item.comments.length === 0" class="no-comments">Sé el primero en comentar.</p>
            </div>
        </div>

        <form class="comment-bar" @submit.prevent="submitComment">
            <input v-model="commentForm.body" type="text" placeholder="Añade un comentario…" class="comment-input" />
            <button type="submit" class="send" :disabled="commentForm.processing || !commentForm.body.trim()">
                <Icon name="arrow-up" :size="18" color="var(--accent-ink)" />
            </button>
        </form>
    </div>
</template>

<style scoped>
.screen {
    min-height: 100%;
}
.hero {
    position: relative;
    height: 320px;
}
.hero-fade {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, var(--hero-scrim-top) 0%, transparent 28%, transparent 60%, var(--bg-frame) 100%);
    pointer-events: none;
}
.hero-actions {
    position: absolute;
    top: 16px;
    left: 16px;
    right: 16px;
    display: flex;
    justify-content: space-between;
}
.round {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(6, 22, 32, 0.7);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #2a4c62;
    color: #fff;
}
.species-tag {
    position: absolute;
    left: 16px;
    bottom: 18px;
    display: flex;
    gap: 6px;
    align-items: center;
    background: rgba(6, 22, 32, 0.78);
    border: 1px solid #2a4c62;
    border-radius: 999px;
    padding: 6px 11px;
}
.st-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    color: #fff;
}
.pending {
    position: absolute;
    right: 16px;
    bottom: 18px;
    background: rgba(245, 165, 36, 0.92);
    color: var(--accent-ink);
    font-size: 11px;
    font-weight: 700;
    padding: 4px 9px;
    border-radius: 999px;
}
.content {
    padding: 14px 20px 0;
}
.author {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text);
}
.author .name {
    font-weight: 600;
    font-size: 14.5px;
}
.author .when {
    font-size: 11.5px;
    color: var(--text-muted);
}
.stat-row {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.stat {
    flex: 1;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 14px;
    padding: 11px 12px;
}
.stat .v {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 17px;
    color: var(--text);
}
.stat .v.small {
    font-size: 14px;
}
.stat .v.accent {
    color: var(--accent-pdx);
}
.stat .l {
    font-size: 10.5px;
    color: var(--text-muted);
    margin-top: 2px;
}
.xp-banner {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-top: 13px;
    background: linear-gradient(90deg, rgba(245, 165, 36, 0.18), rgba(245, 165, 36, 0.05));
    border: 1px solid rgba(245, 165, 36, 0.45);
    border-radius: 14px;
    padding: 12px 14px;
}
.xp-text {
    font-size: 13px;
    color: var(--text);
}
.xp-text b {
    font-family: var(--font-display);
    color: var(--accent-pdx);
}
.xp-text span {
    color: #cdb98a;
}
.meta {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 13px;
    color: var(--text-muted);
}
.meta span {
    color: var(--text);
    font-weight: 600;
}
.notes {
    margin-top: 12px;
    background: rgba(45, 212, 191, 0.08);
    border-radius: 12px;
    padding: 12px;
    font-size: 13.5px;
    color: var(--text-soft);
}
.likes {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-top: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--hairline);
}
.like {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text);
    background: none;
    border: 0;
    font-family: inherit;
}
.like span {
    font-size: 13.5px;
    font-weight: 600;
}
.like.on {
    color: var(--accent-pdx);
}
.like.muted {
    color: var(--text-soft);
}
.comments {
    padding: 14px 0 90px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.comment {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: var(--surface-alt);
    border-radius: 12px;
    padding: 10px 12px;
}
.c-content {
    flex: 1;
    min-width: 0;
}
.c-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-strong);
}
.c-body {
    font-size: 13.5px;
    color: var(--text-soft);
    margin-top: 2px;
}
.no-comments {
    font-size: 13px;
    color: var(--text-faint);
}
.comment-bar {
    position: sticky;
    bottom: 0;
    display: flex;
    gap: 9px;
    padding: 12px 18px calc(14px + env(safe-area-inset-bottom));
    background: linear-gradient(180deg, transparent, var(--bg-frame) 30%);
}
.comment-input {
    flex: 1;
    background: var(--surface-alt);
    border: 1px solid var(--field-border);
    border-radius: 999px;
    padding: 11px 15px;
    color: var(--text);
    font-family: inherit;
    font-size: 13.5px;
    outline: none;
}
.send {
    width: 42px;
    border-radius: 999px;
    background: var(--accent-pdx);
    border: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.send:disabled {
    opacity: 0.5;
}
</style>
