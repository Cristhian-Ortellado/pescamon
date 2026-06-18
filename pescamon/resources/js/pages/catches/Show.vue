<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

interface Comment {
    id: number;
    body: string;
    user: { id: number; name: string };
    created_at: string;
}

interface CatchData {
    id: number;
    species: { id: number; name: string; slug: string; rarity: string };
    user: { id: number; name: string; level: number };
    weight_kg: string;
    length_cm: string | null;
    image_path: string;
    thumbnail_path: string | null;
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
    can_edit: boolean;
    comments: Comment[];
}

const props = defineProps<{
    catch: CatchData;
    levelProgress: number;
}>();

const commentForm = useForm({ body: '' });
const isLiked = ref(props.catch.is_liked);
const likesCount = ref(props.catch.likes_count);

const rarityBadge: Record<string, string> = {
    common: 'bg-gray-100 text-gray-600',
    uncommon: 'bg-emerald-100 text-emerald-700',
    rare: 'bg-blue-100 text-blue-700',
    legendary: 'bg-amber-100 text-amber-700',
};

const methodLabel: Record<string, string> = {
    spinning: 'Spinning',
    fly: 'Mosca',
    bait_casting: 'Bait Casting',
    trolling: 'Trolling',
    other: 'Otro',
};

const waterLabel: Record<string, string> = {
    river: 'Río',
    lake: 'Lago',
    sea: 'Mar',
    reservoir: 'Embalse',
    other: 'Otro',
};

function toggleLike() {
    router.post(`/catches/${props.catch.id}/like`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isLiked.value = !isLiked.value;
            likesCount.value += isLiked.value ? 1 : -1;
        },
    });
}

function submitComment() {
    commentForm.post(`/catches/${props.catch.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}
</script>

<template>
    <Head :title="`Captura de ${catch.species.name}`" />

    <div class="mx-auto max-w-3xl px-4 py-8">
        <!-- Back link -->
        <Link href="/dashboard" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600">
            ← Volver al mapa
        </Link>

        <div class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-800">
            <!-- Image -->
            <div class="relative">
                <img
                    :src="`/storage/${catch.image_path}`"
                    :alt="catch.species.name"
                    class="h-80 w-full object-cover"
                />
                <div v-if="catch.status === 'pending'" class="absolute left-3 top-3 rounded-full bg-yellow-400 px-2 py-0.5 text-xs font-semibold text-yellow-900">
                    ⏳ Pendiente de verificación
                </div>
                <div v-if="catch.is_personal_best" class="absolute right-3 top-3 rounded-full bg-amber-400 px-2 py-0.5 text-xs font-semibold text-amber-900">
                    🏆 Récord personal
                </div>
            </div>

            <div class="p-6">
                <!-- Header -->
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ catch.species.name }}
                            </h1>
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold capitalize', rarityBadge[catch.species.rarity]]">
                                {{ catch.species.rarity }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Por <Link :href="`/profile/${catch.user.id}`" class="font-medium hover:underline">{{ catch.user.name }}</Link>
                            · Nv. {{ catch.user.level }}
                        </p>
                    </div>

                    <!-- Like button -->
                    <button
                        @click="toggleLike"
                        :class="['flex items-center gap-1 rounded-full px-3 py-1.5 text-sm transition', isLiked ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500 hover:bg-red-50']"
                    >
                        {{ isLiked ? '❤️' : '🤍' }} {{ likesCount }}
                    </button>
                </div>

                <!-- Stats grid -->
                <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-3 text-center dark:bg-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Peso</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ catch.weight_kg }} kg</p>
                    </div>
                    <div v-if="catch.length_cm" class="rounded-xl bg-gray-50 p-3 text-center dark:bg-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Longitud</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ catch.length_cm }} cm</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 text-center dark:bg-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Técnica</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ methodLabel[catch.method] ?? catch.method }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 text-center dark:bg-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Agua</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ waterLabel[catch.water_body_type] ?? catch.water_body_type }}</p>
                    </div>
                </div>

                <!-- Meta info -->
                <div class="mb-6 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <p v-if="catch.bait_used">🪱 Cebo: <span class="font-medium text-gray-900 dark:text-white">{{ catch.bait_used }}</span></p>
                    <p v-if="catch.location_name">📍 {{ catch.location_name }}</p>
                    <p>📅 {{ new Date(catch.caught_at).toLocaleString('es-ES') }}</p>
                </div>

                <p v-if="catch.notes" class="mb-6 rounded-xl bg-blue-50 p-3 text-sm text-gray-700 dark:bg-blue-900/20 dark:text-gray-300">
                    {{ catch.notes }}
                </p>

                <!-- Species link -->
                <Link :href="`/species/${catch.species.slug}`" class="mb-6 inline-block rounded-lg border border-blue-200 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-400">
                    Ver especie en PescaDex →
                </Link>

                <!-- Comments -->
                <div>
                    <h3 class="mb-3 font-semibold text-gray-900 dark:text-white">Comentarios ({{ catch.comments.length }})</h3>

                    <div class="mb-4 space-y-3">
                        <div v-for="comment in catch.comments" :key="comment.id" class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ comment.user.name }}</p>
                            <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">{{ comment.body }}</p>
                        </div>
                        <p v-if="catch.comments.length === 0" class="text-sm text-gray-400">Sin comentarios aún.</p>
                    </div>

                    <form @submit.prevent="submitComment" class="flex gap-2">
                        <input
                            v-model="commentForm.body"
                            type="text"
                            placeholder="Escribe un comentario..."
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <button
                            type="submit"
                            :disabled="commentForm.processing || !commentForm.body.trim()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
