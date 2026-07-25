<script setup lang="ts">
import { ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';

interface GalleryImage {
    id?: number | string;
    url: string;
}

const props = withDefaults(
    defineProps<{
        images: GalleryImage[];
        ratio?: string;
        alt?: string;
    }>(),
    { ratio: '16 / 10', alt: '' },
);

const active = ref(0);
const track = ref<HTMLElement | null>(null);

// Derive the current slide from the scroll offset so the dots stay in sync
// as the user swipes.
function onScroll() {
    const el = track.value;

    if (!el) {
        return;
    }

    active.value = Math.round(el.scrollLeft / el.clientWidth);
}
</script>

<template>
    <div class="carousel" :style="{ aspectRatio: props.ratio }">
        <div
            v-if="images.length"
            ref="track"
            class="track"
            @scroll.passive="onScroll"
        >
            <div v-for="(img, i) in images" :key="img.id ?? i" class="slide">
                <img :src="img.url" :alt="alt" class="img" loading="lazy" />
            </div>
        </div>
        <div v-else class="placeholder">
            <Icon name="package" :size="34" color="var(--text-faint)" />
        </div>

        <div v-if="images.length > 1" class="dots">
            <span
                v-for="(img, i) in images"
                :key="img.id ?? i"
                class="dot"
                :class="{ on: i === active }"
            />
        </div>
    </div>
</template>

<style scoped>
.carousel {
    position: relative;
    width: 100%;
    overflow: hidden;
    background: var(--bg-frame);
}
.track {
    position: absolute;
    inset: 0;
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.track::-webkit-scrollbar {
    display: none;
}
.slide {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    scroll-snap-align: center;
}
.img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: repeating-linear-gradient(
        135deg,
        var(--bg-frame),
        var(--bg-frame) 12px,
        var(--surface-alt) 12px,
        var(--surface-alt) 24px
    );
}
.dots {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 8px;
    display: flex;
    justify-content: center;
    gap: 5px;
    pointer-events: none;
}
.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    transition: all 0.15s ease;
}
.dot.on {
    width: 18px;
    border-radius: 999px;
    background: #fff;
}
</style>
