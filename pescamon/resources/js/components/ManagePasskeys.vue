<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import PasskeyItem from '@/components/PasskeyItem.vue';
import PasskeyRegister from '@/components/PasskeyRegister.vue';
import Icon from '@/components/pescadex/Icon.vue';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

withDefaults(defineProps<Props>(), {
    canManagePasskeys: false,
    passkeys: () => [],
});

const handleDelete = (id: number, onError: () => void) => {
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onError,
    });
};

const handleRegisterSuccess = () => {
    router.reload();
};
</script>

<template>
    <template v-if="canManagePasskeys">
        <div class="group-label plain">Passkeys</div>
        <div class="card">
            <template v-if="passkeys.length">
                <PasskeyItem
                    v-for="passkey in passkeys"
                    :key="passkey.id"
                    :passkey="passkey"
                    @remove="handleDelete"
                />
            </template>

            <div v-else class="empty">
                <div class="empty-icon">
                    <Icon
                        name="key-round"
                        :size="26"
                        color="var(--text-faint)"
                    />
                </div>
                <p class="empty-title">Aún no tienes passkeys</p>
                <p class="empty-desc">
                    Añade una passkey para iniciar sesión sin contraseña
                </p>
            </div>
        </div>

        <div class="register-wrap">
            <PasskeyRegister @success="handleRegisterSuccess" />
        </div>
    </template>
</template>

<style scoped>
.group-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-faint);
    padding: 14px 20px 10px;
}
.group-label.plain {
    padding-top: 18px;
}
.card {
    margin: 0 20px;
    background: var(--surface-alt);
    border: 1px solid var(--border-pdx);
    border-radius: 16px;
    overflow: hidden;
}
.empty {
    text-align: center;
    padding: 32px 24px;
}
.empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: var(--surface-locked);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}
.empty-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14.5px;
    color: var(--text);
}
.empty-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    margin-top: 4px;
}
.register-wrap {
    margin: 12px 20px 0;
}
</style>
