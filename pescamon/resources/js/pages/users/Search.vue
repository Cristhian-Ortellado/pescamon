<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Avatar from '@/components/pescadex/Avatar.vue';
import Icon from '@/components/pescadex/Icon.vue';

interface Result {
    id: number;
    name: string;
    level: number;
    avatar: string | null;
    is_following: boolean;
}

const props = defineProps<{ query: string; results: Result[] }>();

const term = ref(props.query);
let debounce: ReturnType<typeof setTimeout> | undefined;

// Debounced live search: fetch only the search props and keep the input focused.
watch(term, (value) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(
            '/users/search',
            { q: value },
            {
                only: ['query', 'results'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
});

function toggleFollow(id: number) {
    router.post(`/users/${id}/follow`, {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['query', 'results'],
    });
}
</script>

<template>
    <Head title="Buscar amigos" />

    <div class="head">
        <div class="top-gap" />
        <div class="app-bar">
            <button class="ic" @click="router.visit('/feed')">
                <Icon name="arrow-left" :size="23" color="var(--icon-muted)" />
            </button>
            <span class="title">Buscar amigos</span>
        </div>
        <div class="search-wrap">
            <Icon name="search" :size="18" color="var(--text-faint)" />
            <input
                v-model="term"
                type="text"
                class="search-input"
                placeholder="Buscá por nombre…"
                autofocus
            />
            <button v-if="term" class="clear" @click="term = ''">
                <Icon name="x" :size="16" color="var(--text-faint)" />
            </button>
        </div>
    </div>

    <div v-if="!query" class="hint">
        <Icon name="users" :size="38" color="var(--text-locked)" />
        <p>Escribí el nombre de un pescador para encontrarlo y seguirlo.</p>
    </div>

    <div v-else-if="results.length === 0" class="hint">
        <Icon name="search" :size="38" color="var(--text-locked)" />
        <p>No encontramos pescadores con «{{ query }}».</p>
    </div>

    <div v-else class="list">
        <div v-for="u in results" :key="u.id" class="row">
            <Link :href="`/profile/${u.id}`" class="who">
                <Avatar :size="44" ring="teal" :src="u.avatar" :alt="u.name" />
                <div class="info">
                    <div class="name">{{ u.name }}</div>
                    <div class="lvl">Nivel {{ u.level }}</div>
                </div>
            </Link>
            <button
                class="follow"
                :class="{ following: u.is_following }"
                @click="toggleFollow(u.id)"
            >
                <Icon
                    :name="u.is_following ? 'check' : 'user-plus'"
                    :size="16"
                    :color="u.is_following ? 'var(--text-soft)' : 'var(--accent-ink)'"
                />
                {{ u.is_following ? 'Siguiendo' : 'Seguir' }}
            </button>
        </div>
    </div>

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 20px;
}
.top-gap {
    height: 16px;
}
.app-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 4px 0 12px;
}
.ic {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
.search-wrap {
    display: flex;
    align-items: center;
    gap: 11px;
    background: var(--surface);
    border: 1px solid var(--field-border);
    border-radius: 13px;
    padding: 4px 14px;
}
.search-input {
    flex: 1;
    width: 100%;
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: inherit;
    font-size: 14.5px;
    padding: 12px 0;
}
.search-input::placeholder {
    color: var(--text-faint);
}
.clear {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
}
.hint {
    text-align: center;
    color: var(--text-muted);
    padding: 60px 34px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.list {
    padding: 8px 18px;
}
.row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 2px;
    border-bottom: 1px solid var(--hairline);
}
.who {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
    color: var(--text);
}
.info {
    min-width: 0;
}
.name {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.lvl {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 1px;
}
.follow {
    flex: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    padding: 8px 14px;
    border: 0;
    border-radius: 999px;
}
.follow.following {
    background: var(--track-bg);
    color: var(--text-soft);
    border: 1px solid var(--track-border);
}
.bottom-spacer {
    height: 40px;
}
</style>
