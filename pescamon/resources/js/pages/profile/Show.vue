<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import XpBar from '@/components/XpBar.vue';
import PokedexGrid from '@/components/PokedexGrid.vue';
import AchievementBadge from '@/components/AchievementBadge.vue';

interface PokedexEntry {
    id: number;
    name: string;
    slug: string;
    rarity: string;
    image_path: string | null;
    caught: boolean;
}

interface RecentCatch {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    weight_kg: string;
    thumbnail_path: string | null;
    caught_at: string;
    is_personal_best: boolean;
}

interface AchievementItem {
    id: number;
    code: string;
    name: string;
    description: string;
    icon_path: string | null;
    pivot: { unlocked_at: string };
}

const props = defineProps<{
    profileUser: {
        id: number;
        name: string;
        level: number;
        xp: number;
        catches_count: number;
        followers_count: number;
        following_count: number;
        is_own_profile: boolean;
        is_following: boolean;
    };
    pokedex: PokedexEntry[];
    recentCatches: RecentCatch[];
    achievements: AchievementItem[];
    levelProgress: number;
    xpToNextLevel: number;
}>();

const activeTab = ref<'pokedex' | 'catches' | 'achievements'>('pokedex');

const caughtCount = props.pokedex.filter((s) => s.caught).length;

function toggleFollow() {
    router.post(`/profile/${props.profileUser.id}/follow`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="profileUser.is_own_profile ? 'Mi Perfil' : `Perfil de ${profileUser.name}`" />

    <div class="mx-auto max-w-4xl px-4 py-8">
        <!-- Profile header -->
        <div class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-4xl dark:bg-blue-900">
                🎣
            </div>

            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ profileUser.name }}</h1>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-sm font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                        Nv. {{ profileUser.level }}
                    </span>
                </div>

                <div class="mt-2 w-64">
                    <XpBar :xp="profileUser.xp" :level="profileUser.level" :progress="levelProgress" />
                    <p class="mt-0.5 text-xs text-gray-400">{{ xpToNextLevel }} XP para el siguiente nivel</p>
                </div>

                <div class="mt-2 flex gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span><strong class="text-gray-900 dark:text-white">{{ profileUser.catches_count }}</strong> capturas</span>
                    <span><strong class="text-gray-900 dark:text-white">{{ caughtCount }}</strong> / {{ pokedex.length }} especies</span>
                    <span><strong class="text-gray-900 dark:text-white">{{ profileUser.followers_count }}</strong> seguidores</span>
                </div>
            </div>

            <div v-if="!profileUser.is_own_profile">
                <button
                    @click="toggleFollow"
                    :class="['rounded-lg px-4 py-2 text-sm font-semibold transition', profileUser.is_following ? 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200' : 'bg-blue-600 text-white hover:bg-blue-700']"
                >
                    {{ profileUser.is_following ? 'Siguiendo ✓' : 'Seguir' }}
                </button>
            </div>
            <div v-else>
                <Link href="/catches/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    + Nueva captura
                </Link>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 flex gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-700">
            <button
                v-for="tab in [{ key: 'pokedex', label: '🐟 PescaDex' }, { key: 'catches', label: '🎣 Capturas' }, { key: 'achievements', label: '🏅 Logros' }]"
                :key="tab.key"
                @click="activeTab = tab.key as typeof activeTab"
                :class="['flex-1 rounded-lg py-2 text-sm font-medium transition', activeTab === tab.key ? 'bg-white text-gray-900 shadow dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400']"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Pokédex tab -->
        <div v-if="activeTab === 'pokedex'">
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                {{ caughtCount }} / {{ pokedex.length }} especies capturadas
            </p>
            <PokedexGrid :entries="pokedex" />
        </div>

        <!-- Catches tab -->
        <div v-if="activeTab === 'catches'">
            <div v-if="recentCatches.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                <Link
                    v-for="c in recentCatches"
                    :key="c.id"
                    :href="`/catches/${c.id}`"
                    class="group relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700"
                >
                    <img
                        v-if="c.thumbnail_path"
                        :src="`/storage/${c.thumbnail_path}`"
                        :alt="c.species.name"
                        class="h-32 w-full object-cover transition group-hover:scale-105"
                    />
                    <div v-else class="flex h-32 items-center justify-center text-3xl">🐟</div>
                    <div class="p-2">
                        <p class="truncate text-xs font-semibold text-gray-800 dark:text-gray-200">{{ c.species.name }}</p>
                        <p class="text-xs text-gray-500">{{ c.weight_kg }} kg</p>
                    </div>
                    <div v-if="c.is_personal_best" class="absolute right-1 top-1 rounded-full bg-amber-400 px-1.5 py-0.5 text-xs">🏆</div>
                </Link>
            </div>
            <p v-else class="text-sm text-gray-400">Sin capturas verificadas aún.</p>
        </div>

        <!-- Achievements tab -->
        <div v-if="activeTab === 'achievements'">
            <div v-if="achievements.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <AchievementBadge v-for="a in achievements" :key="a.id" :achievement="a" />
            </div>
            <p v-else class="text-sm text-gray-400">Sin logros desbloqueados aún. ¡Empieza a pescar!</p>
        </div>
    </div>
</template>
