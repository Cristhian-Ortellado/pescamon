<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <template v-if="canManageTwoFactor">
        <div class="group-label plain">Autenticación en dos pasos</div>
        <div class="card">
            <div class="card-body">
                <template v-if="!twoFactorEnabled">
                    <p class="section-desc">
                        Al activar la autenticación en dos pasos, se te pedirá
                        un PIN seguro al iniciar sesión, generado por una app
                        TOTP en tu teléfono.
                    </p>

                    <button
                        v-if="hasSetupData"
                        type="button"
                        class="action-btn"
                        @click="showSetupModal = true"
                    >
                        <Icon name="shield" :size="17" /> Continuar
                        configuración
                    </button>
                    <Form
                        v-else
                        v-bind="enable.form()"
                        @success="showSetupModal = true"
                        #default="{ processing }"
                    >
                        <button
                            type="submit"
                            class="action-btn"
                            :disabled="processing"
                        >
                            <Icon name="shield" :size="17" /> Activar 2FA
                        </button>
                    </Form>
                </template>

                <template v-else>
                    <p class="section-desc">
                        Se te pedirá un PIN seguro y aleatorio al iniciar
                        sesión, generado por la app TOTP de tu teléfono.
                    </p>

                    <Form v-bind="disable.form()" #default="{ processing }">
                        <button
                            type="submit"
                            class="danger-btn"
                            :disabled="processing"
                        >
                            Desactivar 2FA
                        </button>
                    </Form>
                </template>
            </div>
        </div>

        <TwoFactorRecoveryCodes v-if="twoFactorEnabled" />

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
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
.card-body {
    padding: 16px 15px 18px;
}
.section-desc {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 14px;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    padding: 11px 20px;
    border: 0;
    border-radius: 12px;
    box-shadow: 0 10px 24px rgba(245, 165, 36, 0.32);
}
.action-btn:disabled {
    opacity: 0.6;
}
.danger-btn {
    background: none;
    border: 1px solid #a34751;
    color: #e2737a;
    font-family: inherit;
    font-weight: 600;
    font-size: 13.5px;
    padding: 10px 18px;
    border-radius: 11px;
}
.danger-btn:disabled {
    opacity: 0.6;
}
</style>
