<script setup lang="ts">
// Circular user avatar. Shows the uploaded profile picture when `src` is
// provided, otherwise falls back to the striped placeholder. Optional level
// chip and teal/accent ring variants mirror the official design.
withDefaults(
    defineProps<{
        size?: number;
        level?: number | null;
        ring?: 'teal' | 'accent' | 'none';
        src?: string | null;
        alt?: string;
    }>(),
    { size: 38, level: null, ring: 'none', src: null, alt: '' },
);
</script>

<template>
    <div
        class="avatar"
        :class="['ring-' + ring, { 'has-image': src }]"
        :style="{ width: size + 'px', height: size + 'px' }"
    >
        <img v-if="src" :src="src" :alt="alt" class="avatar-img" />
        <span v-if="level != null" class="level-tag">Nv {{ level }}</span>
    </div>
</template>

<style scoped>
.avatar {
    position: relative;
    border-radius: 50%;
    flex: none;
    background: repeating-linear-gradient(
        135deg,
        var(--avatar-a),
        var(--avatar-a) 5px,
        var(--avatar-b) 5px,
        var(--avatar-b) 10px
    );
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
.ring-teal {
    border: 2px solid var(--teal);
}
.ring-accent {
    border: 2.5px solid var(--accent-pdx);
}
.level-tag {
    position: absolute;
    bottom: -6px;
    right: -6px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 999px;
    border: 2px solid var(--bg-frame);
    white-space: nowrap;
}
</style>
