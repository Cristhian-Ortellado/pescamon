<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    species: {
        id: number;
        name: string;
        scientific_name?: string;
        slug: string;
        rarity: string;
        image_reference_path: string | null;
        caught?: boolean;
    };
}>();

const rarityStyles: Record<string, string> = {
    common: 'border-gray-200 bg-gray-50',
    uncommon: 'border-emerald-200 bg-emerald-50',
    rare: 'border-blue-200 bg-blue-50',
    legendary: 'border-amber-300 bg-amber-50',
};

const rarityLabel: Record<string, string> = {
    common: 'Común',
    uncommon: 'Poco común',
    rare: 'Rara',
    legendary: '⭐ Legendaria',
};
</script>

<template>
    <Link
        :href="`/species/${species.slug}`"
        :class="['block overflow-hidden rounded-xl border-2 transition hover:shadow-lg', rarityStyles[species.rarity]]"
    >
        <div class="flex h-32 items-center justify-center overflow-hidden bg-white">
            <img
                v-if="species.image_reference_path"
                :src="`/storage/${species.image_reference_path}`"
                :alt="species.name"
                class="h-full w-full object-cover"
            />
            <span v-else class="text-5xl opacity-20">🐟</span>
        </div>
        <div class="p-3">
            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ species.name }}</p>
            <p v-if="species.scientific_name" class="text-xs italic text-gray-500">{{ species.scientific_name }}</p>
            <p class="mt-1 text-xs font-medium text-gray-600">{{ rarityLabel[species.rarity] ?? species.rarity }}</p>
        </div>
    </Link>
</template>
