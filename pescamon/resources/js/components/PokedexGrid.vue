<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface PokedexEntry {
    id: number;
    name: string;
    slug: string;
    rarity: string;
    image_path: string | null;
    caught: boolean;
}

const props = defineProps<{
    entries: PokedexEntry[];
}>();

const filter = ref<'all' | 'caught' | 'uncaught'>('all');

const rarityBorder: Record<string, string> = {
    common: 'border-gray-300',
    uncommon: 'border-emerald-400',
    rare: 'border-blue-400',
    legendary: 'border-amber-400',
};

const filtered = computed(() => {
    if (filter.value === 'caught') return props.entries.filter((e) => e.caught);
    if (filter.value === 'uncaught') return props.entries.filter((e) => !e.caught);
    return props.entries;
});
</script>

<template>
    <div>
        <!-- Filter pills -->
        <div class="mb-4 flex gap-2">
            <button
                v-for="f in [{ key: 'all', label: 'Todas' }, { key: 'caught', label: '✓ Capturadas' }, { key: 'uncaught', label: '🔒 Sin capturar' }]"
                :key="f.key"
                @click="filter = f.key as typeof filter"
                :class="['rounded-full px-3 py-1 text-xs font-medium transition', filter === f.key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400']"
            >
                {{ f.label }}
            </button>
        </div>

        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6">
            <Link
                v-for="entry in filtered"
                :key="entry.id"
                :href="`/species/${entry.slug}`"
                :class="['group relative overflow-hidden rounded-xl border-2 bg-white transition hover:shadow-md dark:bg-gray-800', rarityBorder[entry.rarity], !entry.caught ? 'opacity-60' : '']"
            >
                <!-- Image or placeholder -->
                <div class="flex h-20 items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700">
                    <img
                        v-if="entry.image_path"
                        :src="`/storage/${entry.image_path}`"
                        :alt="entry.name"
                        :class="['h-full w-full object-cover', !entry.caught ? 'grayscale' : '']"
                    />
                    <span v-else class="text-3xl opacity-30">🐟</span>
                </div>

                <div class="p-1.5">
                    <p class="truncate text-center text-xs font-medium text-gray-800 dark:text-gray-200">
                        {{ entry.caught ? entry.name : '???' }}
                    </p>
                </div>

                <!-- Caught checkmark -->
                <div v-if="entry.caught" class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-white text-xs">
                    ✓
                </div>
            </Link>
        </div>
    </div>
</template>
