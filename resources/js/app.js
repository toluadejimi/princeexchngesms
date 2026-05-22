import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const readSessionCache = (key) => {
    try {
        const cached = JSON.parse(sessionStorage.getItem(key) || 'null');
        if (!cached || cached.expiresAt <= Date.now()) return null;

        return cached.value;
    } catch (_) {
        return null;
    }
};

const writeSessionCache = (key, value, ttlMs) => {
    try {
        sessionStorage.setItem(key, JSON.stringify({
            value,
            expiresAt: Date.now() + ttlMs,
        }));
    } catch (_) {}
};

Alpine.data('navWithNotifications', (listUrl, baseUrl, csrf, openNotificationsOnLogin = false) => ({
    open: false,
    panelOpen: false,
    notifications: [],
    unreadCount: 0,
    selectedNotification: null,
    modalNotification: null,
    loading: false,
    errorMessage: '',
    listUrl,
    baseUrl,
    csrf,
    openNotificationsOnLogin: !!openNotificationsOnLogin,
    cacheKey(name) {
        return `sms:${name}:${this.csrf}:${this.listUrl}`;
    },
    readCache(name) {
        return readSessionCache(this.cacheKey(name));
    },
    writeCache(name, value, ttlMs) {
        writeSessionCache(this.cacheKey(name), value, ttlMs);
    },
    async fetchUnreadCount() {
        const cached = this.readCache('unread-count');
        if (cached !== null) {
            this.unreadCount = Number(cached) || 0;
            return;
        }

        try {
            const r = await fetch(this.listUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (r.ok) {
                const text = await r.text();
                try {
                    const d = JSON.parse(text);
                    this.unreadCount = Number(d.unread_count) || 0;
                    this.writeCache('unread-count', this.unreadCount, 30000);
                } catch (_) {}
            }
        } catch (_) {}
    },
    async fetchNotifications() {
        const cached = this.readCache('notifications');
        if (cached && Array.isArray(cached.notifications)) {
            this.notifications = cached.notifications;
            const unreadCount = Number(cached.unread_count);
            this.unreadCount = Number.isFinite(unreadCount) ? unreadCount : this.unreadCount;
            this.errorMessage = '';
            return;
        }

        this.loading = true;
        this.notifications = [];
        this.errorMessage = '';
        try {
            const r = await fetch(this.listUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const text = await r.text();
            if (r.ok) {
                try {
                    const d = JSON.parse(text);
                    this.notifications = Array.isArray(d.notifications) ? d.notifications : [];
                    const unreadCount = Number(d.unread_count);
                    this.unreadCount = Number.isFinite(unreadCount) ? unreadCount : this.unreadCount;
                    this.writeCache('notifications', {
                        notifications: this.notifications,
                        unread_count: this.unreadCount,
                    }, 60000);
                    this.writeCache('unread-count', this.unreadCount, 30000);
                } catch (_) {
                    this.errorMessage = 'Could not load notifications.';
                }
            } else {
                this.errorMessage = 'Could not load notifications.';
            }
        } catch (_) {
            this.errorMessage = 'Could not load notifications.';
        } finally {
            this.loading = false;
        }
    },
    async openNotification(n) {
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
            this.writeCache('notifications', {
                notifications: this.notifications,
                unread_count: this.unreadCount,
            }, 60000);
            this.writeCache('unread-count', this.unreadCount, 30000);
        }
        this.modalNotification = n;
    },
    closeNotificationDetail() {
        this.modalNotification = null;
    },
    closeNotificationModal() {
        this.panelOpen = false;
        this.modalNotification = null;
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
                // Do not call expire endpoint when countdown hits 0; keep polling so backend can expire and we reload
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
                    const status = (d.status || '').toLowerCase();
                    const hasCode = status === 'completed' || (Array.isArray(d.sms_messages) && d.sms_messages.length > 0) || (d.sms_code && d.sms_code.trim() !== '');
                    const noLongerActive = ['cancelled', 'completed', 'expired'].indexOf(status) !== -1;
                    if (hasCode || noLongerActive) {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        this.pollTimer = null;
                        if (this.timer) clearInterval(this.timer);
                        this.timer = null;
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

Alpine.data('loginPopup', (dismissUrl, csrf) => ({
    open: true,
    dismissUrl,
    csrf,
    dismiss() {
        this.open = false;
        try {
            fetch(this.dismissUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ _token: this.csrf }),
            });
        } catch (_) {}
    },
}));

Alpine.data('dashboardLazy', (dataUrl) => ({
    dataUrl,
    loading: false,
    loaded: false,
    html: '',
    errorMessage: '',
    async load() {
        this.loading = true;
        this.loaded = false;
        this.errorMessage = '';

        try {
            const url = new URL(this.dataUrl, window.location.origin);
            url.search = window.location.search;

            const response = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) throw new Error('Dashboard request failed.');

            const data = await response.json();
            this.html = data.html || '';
            this.loaded = true;

            this.$nextTick(() => {
                if (this.$refs.content) Alpine.initTree(this.$refs.content);
            });
        } catch (_) {
            this.errorMessage = 'Could not load dashboard information. Please try again.';
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('cancelCountdown', (allowedAtIso) => ({
    allowedAtIso,
    label: 'Cancel in 10:00',
    timer: null,
    start() {
        const update = () => {
            const end = new Date(this.allowedAtIso).getTime();
            const now = Date.now();
            const left = Math.max(0, Math.floor((end - now) / 1000));
            if (left <= 0) {
                this.label = 'Cancel available';
                if (this.timer) clearInterval(this.timer);
                this.timer = null;
                window.location.reload();
                return;
            }
            const m = Math.floor(left / 60);
            const s = left % 60;
            this.label = `Cancel in ${m}:${String(s).padStart(2, '0')}`;
        };
        update();
        this.timer = setInterval(update, 1000);
    },
}));

Alpine.start();
