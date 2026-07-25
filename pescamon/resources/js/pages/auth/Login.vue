<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="page pdx-app">
        <div class="panel pdx-scroll">
            <div class="content">
                <div class="logo">
                    <div class="logo-mark">
                        <Icon
                            name="fish"
                            :size="20"
                            color="var(--accent-ink)"
                        />
                    </div>
                    <span class="logo-name">PescaDex</span>
                </div>
                <div class="headline">Inicia sesión</div>
                <div class="sub">
                    Ingresa tu correo y contraseña para continuar.
                </div>

                <p v-if="status" class="status-msg">{{ status }}</p>

                <Form
                    v-bind="store.form()"
                    novalidate
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                >
                    <div class="field">
                        <div class="label">Correo electrónico</div>
                        <div class="input-wrap">
                            <Icon
                                name="mail"
                                :size="18"
                                color="var(--text-faint)"
                            />
                            <input
                                id="email"
                                type="email"
                                class="native-input"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="tucorreo@ejemplo.com"
                            />
                        </div>
                        <p v-if="errors.email" class="err">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div class="field">
                        <div class="field-head">
                            <div class="label">Contraseña</div>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="forgot-link"
                                :tabindex="5"
                            >
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>
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
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Tu contraseña"
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

                    <label class="remember-row">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="switch-input"
                            :tabindex="3"
                        />
                        <span class="switch-track"
                            ><span class="switch-knob"
                        /></span>
                        <span class="remember-label">Recuérdame</span>
                    </label>

                    <button
                        type="submit"
                        class="cta"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        {{ processing ? 'Ingresando…' : 'Iniciar sesión' }}
                    </button>
                </Form>

                <div class="signin">
                    ¿No tienes cuenta?
                    <Link :href="register()" :tabindex="6" class="signin-link">
                        Regístrate
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page {
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--studio-bg);
    padding: 24px;
}
.panel {
    position: relative;
    width: 100%;
    max-width: 440px;
    height: min(880px, calc(100dvh - 48px));
    background: var(--bg-frame);
    border: 1px solid var(--nav-border);
    border-radius: 22px;
    overflow-y: auto;
    box-shadow: 0 24px 60px rgba(5, 18, 28, 0.28);
}
.content {
    padding: 40px 28px 32px;
}
.logo {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 22px;
}
.logo-mark {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--accent-pdx);
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 24px;
    color: var(--text);
}
.headline {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 26px;
    line-height: 1.1;
    letter-spacing: -0.01em;
    color: var(--text);
}
.sub {
    font-size: 14px;
    color: var(--text-soft);
    margin-top: 10px;
    line-height: 1.5;
}
.status-msg {
    margin-top: 16px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    color: var(--teal);
}
.passkey-wrap {
    margin-top: 24px;
}
.field {
    margin-top: 18px;
}
.field-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 7px;
}
.field .label,
.field-head .label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin-bottom: 7px;
}
.field-head .label {
    margin-bottom: 0;
}
.forgot-link {
    font-size: 12px;
    font-weight: 600;
    color: var(--teal);
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
.remember-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    cursor: pointer;
    width: fit-content;
}
.switch-input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    overflow: hidden;
}
.switch-track {
    width: 46px;
    height: 27px;
    border-radius: 999px;
    background: var(--track-border);
    position: relative;
    flex: none;
    transition: background 0.15s ease;
}
.switch-knob {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 21px;
    height: 21px;
    border-radius: 50%;
    background: var(--text-faint);
    transition:
        transform 0.15s ease,
        background 0.15s ease;
}
.switch-input:checked + .switch-track {
    background: var(--teal);
}
.switch-input:checked + .switch-track .switch-knob {
    transform: translateX(19px);
    background: #fff;
}
.switch-input:focus-visible + .switch-track {
    outline: 2px solid var(--teal);
    outline-offset: 2px;
}
.remember-label {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-soft);
}
.cta {
    display: block;
    width: 100%;
    text-align: center;
    margin-top: 24px;
    background: var(--accent-pdx);
    color: var(--accent-ink);
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    padding: 14px;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(245, 165, 36, 0.45);
}
.cta:disabled {
    opacity: 0.6;
}
.signin {
    text-align: center;
    margin-top: 20px;
    font-size: 13.5px;
    color: var(--icon-muted);
}
.signin-link {
    color: var(--teal);
    font-weight: 600;
}
</style>
