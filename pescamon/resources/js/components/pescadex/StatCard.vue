<script setup lang="ts">
import Icon from './Icon.vue';

withDefaults(
    defineProps<{
        value: string;
        suffix?: string;
        label: string;
        accent?: boolean;
        icon?: string;
    }>(),
    { accent: false },
);
</script>

<template>
    <div class="stat-card">
        <div class="value" :class="{ accent }">
            <Icon v-if="icon" :name="icon" :size="17" />
            <span>{{ value }}</span>
            <span v-if="suffix" class="suffix">{{ suffix }}</span>
        </div>
        <div class="label">{{ label }}</div>
    </div>
</template>

<style scoped>
.stat-card {
    flex: 1;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 15px;
    padding: 12px;
}
.value {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 19px;
}
.value.accent {
    color: var(--accent-pdx);
}
.suffix {
    color: var(--text-faint);
    font-size: 13px;
}
.label {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}
</style>
