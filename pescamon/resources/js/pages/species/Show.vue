<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CatchPhoto from '@/components/pescadex/CatchPhoto.vue';
import Icon from '@/components/pescadex/Icon.vue';
import RarityBadge from '@/components/pescadex/RarityBadge.vue';
import { goBack } from '@/lib/navigation';
import { formatDateEs } from '@/lib/pescadex';

interface UserCatch {
    id: number;
    weight_kg: string;
    length_cm: string | null;
    caught_at: string;
    image_path: string;
    thumbnail_path: string | null;
    location_name: string | null;
    is_personal_best: boolean;
}

const props = defineProps<{
    species: {
        id: number;
        name: string;
        scientific_name: string;
        slug: string;
        rarity: string;
        habitat_description: string | null;
        average_weight_kg: string | null;
    };
    userCaught: boolean;
    personalBest: UserCatch | null;
    userCatches: UserCatch[];
    stats: { total_catches: number; avg_weight_kg: number };
}>();

const heroImage = props.personalBest?.image_path ?? null;
</script>

<template>
    <Head :title="species.name" />

    <div class="hero">
        <CatchPhoto :path="heroImage" :rarity="species.rarity" :stripe="11" :alt="species.name" />
        <div class="hero-fade" />
        <div class="hero-actions">
            <button class="round" @click="goBack('/profile')"><Icon name="arrow-left" :size="20" /></button>
        </div>
        <RarityBadge :rarity="species.rarity" class="hero-badge" />
    </div>

    <div class="content">
        <div class="title">{{ species.name }}</div>
        <div class="sci">{{ species.scientific_name }}</div>

        <div class="stat-row">
            <div class="stat">
                <div class="v accent">{{ personalBest ? personalBest.weight_kg + ' kg' : '—' }}</div>
                <div class="l">Récord personal</div>
            </div>
            <div class="stat">
                <div class="v">{{ species.average_weight_kg ?? '—' }} kg</div>
                <div class="l">Promedio</div>
            </div>
            <div class="stat">
                <div class="v">{{ stats.total_catches }}</div>
                <div class="l">Capturas</div>
            </div>
        </div>

        <div v-if="species.habitat_description" class="habitat">
            <Icon name="waves" :size="18" color="var(--teal)" class="habitat-icon" />
            <span>{{ species.habitat_description }}</span>
        </div>

        <div v-if="!userCaught" class="locked-note">
            <Icon name="lock" :size="16" color="var(--text-faint)" />
            Aún no has capturado esta especie. ¡Sal a pescar!
        </div>

        <template v-if="userCatches.length">
            <div class="section-title hist-title">Tu historial</div>
            <Link
                v-for="c in userCatches"
                :key="c.id"
                :href="`/catches/${c.id}`"
                class="hist-row"
            >
                <div class="hist-thumb">
                    <CatchPhoto :path="c.thumbnail_path ?? c.image_path" :rarity="species.rarity" :stripe="6" />
                </div>
                <div class="hist-body">
                    <div class="hist-weight">
                        {{ c.weight_kg }} kg
                        <span v-if="c.is_personal_best" class="record-pill">RÉCORD</span>
                    </div>
                    <div class="hist-meta">
                        <template v-if="c.location_name">{{ c.location_name }} · </template>{{ formatDateEs(c.caught_at) }}
                    </div>
                </div>
                <Icon name="chevron-right" :size="18" color="var(--text-faint)" />
            </Link>
        </template>

        <div class="bottom-spacer" />
    </div>
</template>

<style scoped>
.hero {
    position: relative;
    height: 280px;
}
.hero-fade {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, var(--hero-scrim-top) 0%, transparent 30%, transparent 55%, var(--bg-frame) 100%);
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
.hero-badge {
    position: absolute;
    left: 16px;
    bottom: 18px;
    font-size: 10px;
    padding: 3px 8px;
}
.content {
    padding: 0 20px;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 25px;
    line-height: 1.05;
    color: var(--text);
}
.sci {
    font-style: italic;
    color: var(--text-muted);
    font-size: 13.5px;
    margin-top: 2px;
}
.stat-row {
    display: flex;
    gap: 10px;
    margin-top: 16px;
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
.stat .v.accent {
    color: var(--accent-pdx);
}
.stat .l {
    font-size: 10.5px;
    color: var(--text-muted);
    margin-top: 2px;
}
.habitat {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 14px;
    font-size: 13px;
    color: var(--text-soft);
    line-height: 1.45;
}
.habitat-icon {
    flex: none;
    margin-top: 1px;
}
.locked-note {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding: 12px;
    border-radius: 12px;
    background: var(--surface-locked);
    border: 1px dashed var(--border-strong);
    color: var(--text-muted);
    font-size: 13px;
}
.section-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    color: var(--text);
}
.hist-title {
    margin-top: 18px;
    margin-bottom: 9px;
}
.hist-row {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 9px 0;
    border-bottom: 1px solid var(--hairline);
    color: var(--text);
}
.hist-thumb {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    overflow: hidden;
    flex: none;
}
.hist-body {
    flex: 1;
}
.hist-weight {
    font-weight: 600;
    font-size: 13.5px;
}
.record-pill {
    font-size: 10px;
    font-weight: 700;
    color: var(--accent-pdx);
    border: 1px solid var(--accent-pdx);
    border-radius: 5px;
    padding: 0 4px;
    margin-left: 4px;
}
.hist-meta {
    font-size: 11.5px;
    color: var(--text-muted);
}
.bottom-spacer {
    height: 40px;
}
</style>
