<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { goBack } from '@/lib/navigation';
import { computed, nextTick, ref, useTemplateRef } from 'vue';
import Icon from '@/components/pescadex/Icon.vue';

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm<{
    name: string;
    email: string;
    avatar: File | null;
    remove_avatar: boolean;
}>({
    name: user.value.name,
    email: user.value.email,
    avatar: null,
    remove_avatar: false,
});

const avatarInput = useTemplateRef<HTMLInputElement>('avatarInput');
const avatarPreview = ref<string | null>(null);

// Current avatar to display: local preview > stored avatar > striped placeholder.
const currentAvatar = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (form.remove_avatar) return null;
    return (user.value.avatar as string | undefined) ?? null;
});

function pickAvatar() {
    avatarInput.value?.click();
}

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    if (!file) return;
    form.avatar = file;
    form.remove_avatar = false;
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = URL.createObjectURL(file);
}

function removeAvatar() {
    form.avatar = null;
    form.remove_avatar = true;
    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
        avatarPreview.value = null;
    }
    if (avatarInput.value) avatarInput.value.value = '';
}

function save() {
    form.patch('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            form.avatar = null;
            form.remove_avatar = false;
            if (avatarPreview.value) {
                URL.revokeObjectURL(avatarPreview.value);
                avatarPreview.value = null;
            }
        },
    });
}

function resendVerification() {
    router.post(
        '/email/verification-notification',
        {},
        { preserveScroll: true },
    );
}

const showDelete = ref(false);
const passwordInput = useTemplateRef<HTMLInputElement>('passwordInput');
const deleteForm = useForm({ password: '' });

function openDelete() {
    showDelete.value = true;
    nextTick(() => passwordInput.value?.focus());
}
function closeDelete() {
    showDelete.value = false;
    deleteForm.reset();
    deleteForm.clearErrors();
}
function confirmDelete() {
    deleteForm.delete('/settings/profile', {
        preserveScroll: true,
        onError: () => passwordInput.value?.focus(),
    });
}
</script>

<template>
    <Head title="Editar perfil" />

    <div class="head">
        <div class="top-gap" />
        <div class="app-bar">
            <button class="ic" @click="goBack('/settings')">
                <Icon name="arrow-left" :size="23" color="var(--icon-muted)" />
            </button>
            <span class="title">Editar perfil</span>
        </div>
    </div>

    <div class="group-label plain">Foto de perfil</div>
    <div class="card">
        <div class="avatar-body">
            <button
                type="button"
                class="avatar-ring"
                :class="{ 'has-image': currentAvatar }"
                @click="pickAvatar"
            >
                <img
                    v-if="currentAvatar"
                    :src="currentAvatar"
                    :alt="form.name"
                    class="avatar-img"
                />
                <Icon
                    v-else
                    name="user"
                    :size="34"
                    color="var(--text-faint)"
                />
                <span class="avatar-edit">
                    <Icon name="camera" :size="15" color="var(--accent-ink)" />
                </span>
            </button>

            <div class="avatar-actions">
                <button type="button" class="avatar-btn" @click="pickAvatar">
                    <Icon name="upload" :size="16" color="var(--teal)" />
                    Subir foto
                </button>
                <button
                    v-if="currentAvatar"
                    type="button"
                    class="avatar-btn danger"
                    @click="removeAvatar"
                >
                    <Icon name="trash-2" :size="16" color="#e2737a" />
                    Quitar
                </button>
                <p class="avatar-hint">JPG, PNG o WebP · máx. 4 MB</p>
            </div>

            <input
                ref="avatarInput"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="hidden-input"
                @change="onAvatarChange"
            />
        </div>
        <p v-if="form.errors.avatar" class="err avatar-err">
            {{ form.errors.avatar }}
        </p>
    </div>

    <div class="group-label plain">Datos personales</div>
    <div class="card">
        <div class="card-body">
            <div class="field">
                <div class="label">Nombre</div>
                <div class="input-wrap">
                    <Icon name="user" :size="18" color="var(--text-faint)" />
                    <input
                        v-model="form.name"
                        type="text"
                        class="native-input"
                        name="name"
                        autocomplete="name"
                        placeholder="Tu nombre"
                    />
                </div>
                <p v-if="form.errors.name" class="err">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="field">
                <div class="label">Correo electrónico</div>
                <div class="input-wrap">
                    <Icon name="mail" :size="18" color="var(--text-faint)" />
                    <input
                        v-model="form.email"
                        type="email"
                        class="native-input"
                        name="email"
                        autocomplete="username"
                        placeholder="tucorreo@ejemplo.com"
                    />
                </div>
                <p v-if="form.errors.email" class="err">
                    {{ form.errors.email }}
                </p>
            </div>

            <div
                v-if="mustVerifyEmail && !user.email_verified_at"
                class="verify-note"
            >
                <Icon name="mail" :size="15" color="var(--accent-pdx)" />
                <div class="verify-text">
                    Tu correo no está verificado.
                    <button
                        type="button"
                        class="link-btn"
                        @click="resendVerification"
                    >
                        Reenviar verificación
                    </button>
                    <div
                        v-if="status === 'verification-link-sent'"
                        class="verify-sent"
                    >
                        Enlace de verificación enviado.
                    </div>
                </div>
            </div>

            <button class="save" :disabled="form.processing" @click="save">
                <Icon name="check" :size="19" />
                {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
            </button>
        </div>
    </div>

    <div class="group-label plain">Zona de peligro</div>
    <div class="card danger-card">
        <div class="danger-text">
            <div class="danger-title">Eliminar cuenta</div>
            <div class="danger-desc">
                Esta acción es permanente: se borrarán tus capturas, logros y
                datos de perfil.
            </div>
        </div>
        <button
            class="danger-btn"
            data-test="delete-user-button"
            @click="openDelete"
        >
            Eliminar cuenta
        </button>
    </div>

    <div class="bottom-spacer" />

    <div v-if="showDelete" class="modal-backdrop" @click.self="closeDelete">
        <div class="modal">
            <div class="modal-title">¿Eliminar tu cuenta?</div>
            <div class="modal-desc">
                Esta acción no se puede deshacer. Introduce tu contraseña para
                confirmar.
            </div>

            <div class="field">
                <div class="input-wrap">
                    <Icon name="lock" :size="18" color="var(--text-faint)" />
                    <input
                        ref="passwordInput"
                        v-model="deleteForm.password"
                        type="password"
                        class="native-input"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Contraseña"
                        @keyup.enter="confirmDelete"
                    />
                </div>
                <p v-if="deleteForm.errors.password" class="err">
                    {{ deleteForm.errors.password }}
                </p>
            </div>

            <div class="modal-actions">
                <button class="modal-cancel" @click="closeDelete">
                    Cancelar
                </button>
                <button
                    class="modal-confirm"
                    data-test="confirm-delete-user-button"
                    :disabled="deleteForm.processing"
                    @click="confirmDelete"
                >
                    Eliminar
                </button>
            </div>
        </div>
    </div>
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
.avatar-body {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 18px 16px;
}
.avatar-ring {
    position: relative;
    width: 78px;
    height: 78px;
    flex: none;
    border-radius: 50%;
    border: 2.5px solid var(--teal);
    background: repeating-linear-gradient(
        135deg,
        var(--avatar-a),
        var(--avatar-a) 6px,
        var(--avatar-b) 6px,
        var(--avatar-b) 12px
    );
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    cursor: pointer;
}
.avatar-ring.has-image {
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
.avatar-edit {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--accent-pdx);
    border: 2px solid var(--surface-alt);
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-start;
}
.avatar-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--surface);
    border: 1px solid var(--field-border);
    color: var(--text-soft);
    font-family: inherit;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 14px;
    border-radius: 11px;
    cursor: pointer;
}
.avatar-btn.danger {
    color: #e2737a;
    border-color: #a34751;
}
.avatar-hint {
    font-size: 11.5px;
    color: var(--text-faint);
    margin-top: 2px;
}
.hidden-input {
    display: none;
}
.avatar-err {
    padding: 0 16px 14px;
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
    padding: 4px 14px;
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
.err {
    color: #f08a90;
    font-size: 11.5px;
    margin-top: 5px;
}
.verify-note {
    display: flex;
    gap: 9px;
    margin-top: 16px;
    padding: 11px 13px;
    background: linear-gradient(
        90deg,
        rgba(245, 165, 36, 0.14),
        rgba(245, 165, 36, 0.05)
    );
    border: 1px solid rgba(245, 165, 36, 0.4);
    border-radius: 13px;
}
.verify-text {
    font-size: 12.5px;
    color: var(--text-soft);
    line-height: 1.5;
}
.link-btn {
    background: none;
    border: 0;
    padding: 0;
    font: inherit;
    font-weight: 600;
    font-size: 12.5px;
    color: var(--teal);
    text-decoration: underline;
    text-underline-offset: 2px;
}
.verify-sent {
    margin-top: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--teal);
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
.danger-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px 15px 18px;
}
.danger-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14.5px;
    color: #e2737a;
}
.danger-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    margin-top: 4px;
    line-height: 1.5;
}
.danger-btn {
    align-self: flex-start;
    background: none;
    border: 1px solid #a34751;
    color: #e2737a;
    font-family: inherit;
    font-weight: 600;
    font-size: 13.5px;
    padding: 10px 18px;
    border-radius: 11px;
}
.bottom-spacer {
    height: 40px;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 90;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: var(--glass-bg);
}
.modal {
    width: 100%;
    max-width: 340px;
    background: var(--surface-alt);
    border: 1px solid var(--glass-border);
    border-radius: 18px;
    padding: 20px;
}
.modal-title {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 17px;
    color: var(--text);
}
.modal-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 6px;
    line-height: 1.5;
}
.modal .field {
    margin-top: 16px;
}
.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 18px;
}
.modal-cancel,
.modal-confirm {
    flex: 1;
    text-align: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    padding: 12px;
    border-radius: 12px;
    border: 0;
}
.modal-cancel {
    background: var(--track-bg);
    color: var(--text-soft);
    border: 1px solid var(--track-border);
}
.modal-confirm {
    background: #e2737a;
    color: #2b0a0d;
}
.modal-confirm:disabled {
    opacity: 0.6;
}
</style>
