<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import {
    canShareImage,
    copyCatchLink,
    shareCatchImage,
    shareCatchToWhatsApp,
    type ShareableCatch,
} from '@/lib/share';

const props = defineProps<{ post: ShareableCatch }>();

const open = ref(false);
const feedback = ref('');
const root = ref<HTMLElement | null>(null);
let feedbackTimer: ReturnType<typeof setTimeout> | undefined;

function toggle() {
    open.value = !open.value;
}
function close() {
    open.value = false;
}

function flash(message: string) {
    feedback.value = message;
    clearTimeout(feedbackTimer);
    feedbackTimer = setTimeout(() => (feedback.value = ''), 2200);
}

async function shareToInstagram() {
    close();

    const result = await shareCatchImage(props.post);

    if (result === 'unsupported') {
        flash('Abrí desde el celular para publicar en tu Story.');
    } else if (result === 'error') {
        flash('No se pudo compartir la captura.');
    }
}

async function shareToWhatsApp() {
    close();

    // Prefer the native sheet so the photo itself travels to WhatsApp; fall
    // back to a wa.me link when file sharing is not available (e.g. desktop).
    if (canShareImage()) {
        const result = await shareCatchImage(props.post);

        if (result === 'shared' || result === 'cancelled') {
            return;
        }
    }

    shareCatchToWhatsApp(props.post);
}

async function copyLink() {
    close();

    flash((await copyCatchLink(props.post)) ? 'Enlace copiado' : 'No se pudo copiar el enlace.');
}

function onDocClick(e: MouseEvent) {
    if (open.value && root.value && !root.value.contains(e.target as Node)) {
        close();
    }
}

onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
    clearTimeout(feedbackTimer);
});
</script>

<template>
    <div ref="root" class="share-wrap" @click.stop>
        <button class="action share" :class="{ active: open }" @click="toggle">
            <Icon name="share-2" :size="20" />
            <span>Compartir</span>
        </button>

        <div v-if="open" class="menu">
            <button class="menu-item" @click="shareToInstagram">
                <Icon name="image" :size="18" color="var(--accent-pdx)" />
                <span>Instagram Story</span>
            </button>
            <button class="menu-item" @click="shareToWhatsApp">
                <Icon name="message-circle" :size="18" color="var(--teal)" />
                <span>WhatsApp</span>
            </button>
            <button class="menu-item" @click="copyLink">
                <Icon name="anchor" :size="18" color="var(--text-muted)" />
                <span>Copiar enlace</span>
            </button>
        </div>

        <span v-if="feedback" class="feedback">{{ feedback }}</span>
    </div>
</template>

<style scoped>
.share-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.action.share {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-soft);
    background: none;
    border: 0;
    font-family: inherit;
    cursor: pointer;
}
.action.share span {
    font-size: 13px;
    font-weight: 600;
}
.action.share.active {
    color: var(--text);
}
.menu {
    position: absolute;
    bottom: calc(100% + 8px);
    right: 0;
    z-index: 20;
    min-width: 176px;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.4);
}
.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 10px;
    background: none;
    border: 0;
    border-radius: 10px;
    color: var(--text);
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    text-align: left;
}
.menu-item:hover {
    background: rgba(255, 255, 255, 0.05);
}
.feedback {
    position: absolute;
    bottom: calc(100% + 8px);
    right: 0;
    white-space: nowrap;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 10px;
    padding: 7px 11px;
    font-size: 12px;
    color: var(--text);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
    z-index: 20;
}
</style>
