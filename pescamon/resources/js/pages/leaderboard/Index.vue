<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

interface UserRow {
    id: number;
    name: string;
    level: number;
    xp: number;
    verified_catches_count?: number;
    species_count?: number;
}

interface BigCatch {
    id: number;
    weight_kg: string;
    caught_at: string;
    thumbnail_path: string | null;
    user: { id: number; name: string };
    species: { id: number; name: string; slug: string };
}

defineProps<{
    topByXp: UserRow[];
    topByCatches: UserRow[];
    topBySpecies: UserRow[];
    biggestCatch: BigCatch[];
}>();

const activeTab = ref<'xp' | 'catches' | 'species' | 'biggest'>('xp');

const medals = ['🥇', '🥈', '🥉'];
</script>

<template>
    <Head title="Ranking · PescaDex" />

    <div class="mx-auto max-w-3xl px-4 py-8">
        <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">🏆 Ranking de Pescadores</h1>

        <!-- Tab nav -->
        <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-gray-700">
            <button
                v-for="tab in [
                    { key: 'xp', label: '⚡ XP' },
                    { key: 'catches', label: '🎣 Capturas' },
                    { key: 'species', label: '🐟 Especies' },
                    { key: 'biggest', label: '🏋️ Mayor captura' },
                ]"
                :key="tab.key"
                @click="activeTab = tab.key as typeof activeTab"
                :class="['flex-1 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition', activeTab === tab.key ? 'bg-white text-gray-900 shadow dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400']"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- XP Ranking -->
        <div v-if="activeTab === 'xp'">
            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-800">
                <div
                    v-for="(user, i) in topByXp"
                    :key="user.id"
                    :class="['flex items-center gap-4 px-4 py-3', i < topByXp.length - 1 ? 'border-b border-gray-100 dark:border-gray-700' : '']"
                >
                    <span class="w-6 text-center text-lg">{{ medals[i] ?? `#${i + 1}` }}</span>
                    <div class="flex-1">
                        <Link :href="`/profile/${user.id}`" class="font-semibold text-gray-900 hover:underline dark:text-white">{{ user.name }}</Link>
                        <p class="text-xs text-gray-500">Nivel {{ user.level }}</p>
                    </div>
                    <span class="font-bold text-blue-600">{{ user.xp.toLocaleString('es-ES') }} XP</span>
                </div>
            </div>
        </div>

        <!-- Catches Ranking -->
        <div v-if="activeTab === 'catches'">
            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-800">
                <div
                    v-for="(user, i) in topByCatches"
                    :key="user.id"
                    :class="['flex items-center gap-4 px-4 py-3', i < topByCatches.length - 1 ? 'border-b border-gray-100 dark:border-gray-700' : '']"
                >
                    <span class="w-6 text-center text-lg">{{ medals[i] ?? `#${i + 1}` }}</span>
                    <div class="flex-1">
                        <Link :href="`/profile/${user.id}`" class="font-semibold text-gray-900 hover:underline dark:text-white">{{ user.name }}</Link>
                        <p class="text-xs text-gray-500">Nivel {{ user.level }}</p>
                    </div>
                    <span class="font-bold text-emerald-600">{{ user.verified_catches_count }} capturas</span>
                </div>
            </div>
        </div>

        <!-- Species Ranking -->
        <div v-if="activeTab === 'species'">
            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-800">
                <div
                    v-for="(user, i) in topBySpecies"
                    :key="user.id"
                    :class="['flex items-center gap-4 px-4 py-3', i < topBySpecies.length - 1 ? 'border-b border-gray-100 dark:border-gray-700' : '']"
                >
                    <span class="w-6 text-center text-lg">{{ medals[i] ?? `#${i + 1}` }}</span>
                    <div class="flex-1">
                        <Link :href="`/profile/${user.id}`" class="font-semibold text-gray-900 hover:underline dark:text-white">{{ user.name }}</Link>
                        <p class="text-xs text-gray-500">Nivel {{ user.level }}</p>
                    </div>
                    <span class="font-bold text-purple-600">{{ user.species_count }} especies</span>
                </div>
            </div>
        </div>

        <!-- Biggest Catches -->
        <div v-if="activeTab === 'biggest'">
            <div class="space-y-3">
                <div
                    v-for="(c, i) in biggestCatch"
                    :key="c.id"
                    class="flex items-center gap-4 rounded-xl bg-white p-4 shadow dark:bg-gray-800"
                >
                    <span class="w-6 text-center text-lg">{{ medals[i] ?? `#${i + 1}` }}</span>
                    <img
                        v-if="c.thumbnail_path"
                        :src="`/storage/${c.thumbnail_path}`"
                        class="h-12 w-12 rounded-lg object-cover"
                    />
                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-2xl">🐟</div>
                    <div class="flex-1">
                        <Link :href="`/catches/${c.id}`" class="font-semibold text-gray-900 hover:underline dark:text-white">
                            {{ c.species.name }}
                        </Link>
                        <p class="text-xs text-gray-500">
                            por
                            <Link :href="`/profile/${c.user.id}`" class="hover:underline">{{ c.user.name }}</Link>
                            · {{ new Date(c.caught_at).toLocaleDateString('es-ES') }}
                        </p>
                    </div>
                    <span class="font-bold text-amber-600 text-lg">{{ c.weight_kg }} kg</span>
                </div>
            </div>
        </div>
    </div>
</template>
