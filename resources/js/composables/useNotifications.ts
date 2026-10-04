import { ref } from 'vue';
import { mockNotifications } from '@/data/notifications';
import type { NotificationItem } from '@/types/models';

const notifications = ref<Record<string, NotificationItem[]>>({
    ...mockNotifications,
});

export const useNotifications = () => {
    const getNotificationsForRole = (role: string): NotificationItem[] => {
        return notifications.value[role] || [];
    };

    const getUnreadCount = (role: string) => {
        return (notifications.value[role] || []).filter((n) => !n.read).length;
    };

    const markAsRead = (role: string, id: number) => {
        const list = notifications.value[role];
        if (!list) return;
        const item = list.find((n) => n.id === id);
        if (item) item.read = true;
    };

    const markAllAsRead = (role: string) => {
        const list = notifications.value[role];
        if (!list) return;
        list.forEach((n) => {
            n.read = true;
        });
    };

    return {
        notifications,
        getNotificationsForRole,
        getUnreadCount,
        markAsRead,
        markAllAsRead,
    };
};
