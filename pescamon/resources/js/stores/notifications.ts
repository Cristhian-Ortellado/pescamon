import { defineStore } from 'pinia';
import { ref } from 'vue';

interface Notification {
    id: string;
    type: 'success' | 'error' | 'info' | 'xp';
    message: string;
    xp?: number;
}

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<Notification[]>([]);

    function push(notification: Omit<Notification, 'id'>) {
        const id = Math.random().toString(36).slice(2);
        notifications.value.push({ ...notification, id });

        setTimeout(() => remove(id), 4000);
    }

    function remove(id: string) {
        notifications.value = notifications.value.filter((n) => n.id !== id);
    }

    function pushXp(xp: number, message: string) {
        push({ type: 'xp', message, xp });
    }

    return { notifications, push, remove, pushXp };
});
