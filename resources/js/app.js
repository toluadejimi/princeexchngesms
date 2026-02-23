import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('navWithNotifications', (listUrl, baseUrl, csrf) => ({
    open: false,
    panelOpen: false,
    notifications: [],
    unreadCount: 0,
    selectedNotification: null,
    loading: false,
    listUrl,
    baseUrl,
    csrf,
    async fetchUnreadCount() {
        try {
            const r = await fetch(this.listUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (r.ok) {
                const d = await r.json();
                this.unreadCount = Number(d.unread_count) || 0;
            }
        } catch (_) {}
    },
    async fetchNotifications() {
        this.loading = true;
        this.notifications = [];
        try {
            const r = await fetch(this.listUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (r.ok) {
                const d = await r.json();
                this.notifications = Array.isArray(d.notifications) ? d.notifications : [];
                this.unreadCount = Number(d.unread_count) || 0;
            }
        } catch (_) {
            this.notifications = [];
        } finally {
            this.loading = false;
        }
    },
    async openNotification(n) {
        this.selectedNotification = n;
        if (!n.read_at) {
            try {
                await fetch(`${this.baseUrl}/${n.id}/read`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ _token: this.csrf }),
                });
            } catch (_) {}
            n.read_at = new Date().toISOString();
            this.unreadCount = Math.max(0, this.unreadCount - 1);
        }
    },
}));

Alpine.data('rentalCountdown', (rentalId, expiresAtIso, expireUrl, csrfToken, statusUrl) => ({
    rentalId,
    expiresAtIso,
    expireUrl,
    csrfToken,
    statusUrl: statusUrl || '',
    display: '--:--',
    expired: false,
    timer: null,
    pollTimer: null,
    start() {
        const update = () => {
            const end = new Date(this.expiresAtIso).getTime();
            const now = Date.now();
            const left = Math.max(0, Math.floor((end - now) / 1000));
            if (left <= 0) {
                this.display = '0:00';
                this.expired = true;
                if (this.timer) clearInterval(this.timer);
                this.timer = null;
                if (this.pollTimer) clearInterval(this.pollTimer);
                this.pollTimer = null;
                this.triggerExpire();
                return;
            }
            const m = Math.floor(left / 60);
            const s = left % 60;
            this.display = `${m}:${String(s).padStart(2, '0')}`;
        };
        update();
        this.timer = setInterval(update, 1000);
        if (this.statusUrl) {
            const poll = async () => {
                try {
                    const r = await fetch(this.statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    const hasCode = d.status === 'completed' || (Array.isArray(d.sms_messages) && d.sms_messages.length > 0) || (d.sms_code && d.sms_code.trim() !== '');
                    if (hasCode) {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        this.pollTimer = null;
                        window.location.reload();
                    }
                } catch (_) {}
            };
            poll();
            this.pollTimer = setInterval(poll, 5000);
        }
    },
    async triggerExpire() {
        try {
            const r = await fetch(this.expireUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ _token: this.csrfToken }),
            });
            if (r.ok) window.location.reload();
        } catch (e) {
            window.location.reload();
        }
    },
}));

Alpine.start();
