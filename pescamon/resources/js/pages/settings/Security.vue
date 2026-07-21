<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import Icon from '@/components/pescadex/Icon.vue';

type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

function save() {
    form.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Seguridad" />

    <div class="head">
        <div class="top-gap" />
        <div class="app-bar">
            <button class="ic" @click="router.visit('/settings')">
                <Icon name="arrow-left" :size="23" color="var(--icon-muted)" />
            </button>
            <span class="title">Seguridad</span>
        </div>
    </div>

    <div class="group-label plain">Contraseña</div>
    <div class="card">
        <div class="card-body">
            <div class="field">
                <div class="label">Contraseña actual</div>
                <div class="input-wrap">
                    <Icon name="lock" :size="18" color="var(--text-faint)" />
                    <input
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        class="native-input"
                        name="current_password"
                        autocomplete="current-password"
                        placeholder="Contraseña actual"
                    />
                    <button
                        type="button"
                        class="eye-btn"
                        @click="showCurrent = !showCurrent"
                    >
                        <Icon
                            :name="showCurrent ? 'eye-off' : 'eye'"
                            :size="17"
                            color="var(--text-faint)"
                        />
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="err">
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div class="field">
                <div class="label">Nueva contraseña</div>
                <div class="input-wrap">
                    <Icon name="lock" :size="18" color="var(--text-faint)" />
                    <input
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        class="native-input"
                        name="password"
                        autocomplete="new-password"
                        :passwordrules="props.passwordRules"
                        placeholder="Nueva contraseña"
                    />
                    <button
                        type="button"
                        class="eye-btn"
                        @click="showNew = !showNew"
                    >
                        <Icon
                            :name="showNew ? 'eye-off' : 'eye'"
                            :size="17"
                            color="var(--text-faint)"
                        />
                    </button>
                </div>
                <p v-if="form.errors.password" class="err">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="field">
                <div class="label">Confirmar contraseña</div>
                <div class="input-wrap">
                    <Icon name="lock" :size="18" color="var(--text-faint)" />
                    <input
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        class="native-input"
                        name="password_confirmation"
                        autocomplete="new-password"
                        :passwordrules="props.passwordRules"
                        placeholder="Confirmar contraseña"
                    />
                    <button
                        type="button"
                        class="eye-btn"
                        @click="showConfirm = !showConfirm"
                    >
                        <Icon
                            :name="showConfirm ? 'eye-off' : 'eye'"
                            :size="17"
                            color="var(--text-faint)"
                        />
                    </button>
                </div>
                <p v-if="form.errors.password_confirmation" class="err">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <button
                class="save"
                data-test="update-password-button"
                :disabled="form.processing"
                @click="save"
            >
                <Icon name="check" :size="19" />
                {{ form.processing ? 'Guardando…' : 'Guardar contraseña' }}
            </button>
        </div>
    </div>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />

    <ManagePasskeys
        :canManagePasskeys="canManagePasskeys"
        :passkeys="passkeys"
    />

    <div class="bottom-spacer" />
</template>

<style scoped>
.head {
    padding: 0 20px;
}
.top-gap {
    height: 16px;
}
.app-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 4px 0 8px;
}
.ic {
    background: none;
    border: 0;
    padding: 0;
    display: flex;
}
.title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 21px;
    color: var(--text);
}
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
.field {
    margin-top: 16px;
}
.field:first-child {
    margin-top: 0;
}
.field .label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin-bottom: 7px;
}
.input-wrap {
    display: flex;
    align-items: center;
    gap: 11px;
    background: var(--surface);
    border: 1px solid var(--field-border);
    border-radius: 13px;
    padding: 4px 6px 4px 14px;
}
.native-input {
    flex: 1;
    width: 100%;
    background: none;
    border: 0;
    outline: none;
    color: var(--text);
    font-family: inherit;
    font-size: 14.5px;
    padding: 12px 0;
}
.native-input::placeholder {
    color: var(--text-faint);
}
.eye-btn {
    background: none;
    border: 0;
    padding: 8px;
    display: flex;
    flex: none;
}
.err {
    color: #f08a90;
    font-size: 11.5px;
    margin-top: 5px;
}
.save {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin-top: 20px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    padding: 13px;
    border: 0;
    border-radius: 13px;
    box-shadow: 0 10px 24px rgba(245, 165, 36, 0.35);
}
.save:disabled {
    opacity: 0.6;
}
.bottom-spacer {
    height: 40px;
}
</style>
