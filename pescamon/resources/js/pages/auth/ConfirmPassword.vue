<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import Icon from '@/components/pescadex/Icon.vue';
import { Spinner } from '@/components/ui/spinner';
import PescadexLayout from '@/layouts/PescadexLayout.vue';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: PescadexLayout,
});

const showPassword = ref(false);

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Confirmar contraseña" />

    <div class="head">
        <div class="top-gap" />
        <div class="app-bar">
            <button class="ic" @click="goBack">
                <Icon name="arrow-left" :size="23" color="var(--icon-muted)" />
            </button>
            <span class="title">Confirmar contraseña</span>
        </div>
    </div>

    <p class="intro">
        Esta es un área segura de la aplicación. Confirma tu contraseña antes de
        continuar.
    </p>

    <div class="passkey-wrap">
        <PasskeyVerify
            :routes="{
                options: confirmOptions(),
                submit: confirmStore(),
            }"
            label="Confirmar con passkey"
            loading-label="Confirmando..."
            separator="O confirma con tu contraseña"
        />
    </div>

    <div class="group-label plain">Contraseña</div>
    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
    >
        <div class="card">
            <div class="card-body">
                <div class="field">
                    <div class="label">Contraseña</div>
                    <div class="input-wrap">
                        <Icon
                            name="lock"
                            :size="18"
                            color="var(--text-faint)"
                        />
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="native-input"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Tu contraseña"
                            required
                            autofocus
                        />
                        <button
                            type="button"
                            class="eye-btn"
                            @click="showPassword = !showPassword"
                        >
                            <Icon
                                :name="showPassword ? 'eye-off' : 'eye'"
                                :size="17"
                                color="var(--text-faint)"
                            />
                        </button>
                    </div>
                    <p v-if="errors.password" class="err">
                        {{ errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="save"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    <Icon v-else name="check" :size="19" />
                    {{ processing ? 'Confirmando…' : 'Confirmar contraseña' }}
                </button>
            </div>
        </div>
    </Form>

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
.intro {
    padding: 0 20px;
    margin-top: 6px;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
}
.passkey-wrap {
    margin: 18px 20px 0;
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
