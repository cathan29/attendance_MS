import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    bindToasts();
    registerPwa();
    bindInstallPrompt();
    bindConnectionState();
    syncQueuedAttendance();

    const requiredModal = document.querySelector('[data-required-modal]');
    if (requiredModal) {
        requiredModal.querySelector('input')?.focus();
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = button.closest('.password-field')?.querySelector('input');
        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.classList.toggle('is-visible', isHidden);
            button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            button.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
        });
    });

    // Right Sidebar Toggle
    const rightSidebar = document.getElementById('rightSidebar');
    const toggleButton = document.getElementById('rightSidebarToggle');
    const closeButton = document.getElementById('rightSidebarClose');

    if (toggleButton && rightSidebar) {
        toggleButton.addEventListener('click', () => {
            rightSidebar.classList.toggle('is-open');
        });
    }

    if (closeButton && rightSidebar) {
        closeButton.addEventListener('click', () => {
            rightSidebar.classList.remove('is-open');
        });
    }

    // Close sidebar when clicking outside
    if (rightSidebar) {
        document.addEventListener('click', (e) => {
            if (!rightSidebar.contains(e.target) && !toggleButton?.contains(e.target)) {
                rightSidebar.classList.remove('is-open');
            }
        });
    }

    // Load calendar and schedule data
    loadCalendarData();
    loadScheduleData();
    loadUpcomingClasses();
    loadWeatherData();
    loadAdminSchoolCalendar();

    bindLiveSearch();
    bindAjaxAttendance();
    bindAjaxAdminPanels();
    bindGlobalAjaxPanelNavigation();
});

function bindToasts(root = document) {
    root.querySelectorAll('.toast-notice').forEach((toast) => {
        if (toast.dataset.boundToast) {
            return;
        }

        toast.dataset.boundToast = 'true';
        const close = () => {
            toast.classList.add('is-hiding');
            setTimeout(() => toast.remove(), 200);
        };

        toast.querySelector('.toast-close')?.addEventListener('click', close);
        setTimeout(close, 4200);
    });
}

function showToast(type, message) {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-atomic', 'true');
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = `toast-notice ${type === 'danger' ? 'toast-danger' : 'toast-success'}`;
    toast.setAttribute('role', type === 'danger' ? 'alert' : 'status');
    toast.innerHTML = `
        <span class="toast-dot"></span>
        <div>
            <strong>${type === 'danger' ? 'Action needed' : 'Success'}</strong>
            <p>${escapeHtml(message)}</p>
        </div>
        <button type="button" class="toast-close" aria-label="Close notification">x</button>
    `;
    stack.appendChild(toast);
    bindToasts(stack);
}

const ATTENDANCE_QUEUE_KEY = 'cipher_attendance_queue_v1';
let deferredInstallPrompt = null;

function registerPwa() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.warn('Service worker registration failed:', error);
        });
    });
}

function bindInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallBanner();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        document.querySelector('[data-pwa-install-banner]')?.remove();
        localStorage.setItem('cipher_pwa_installed', 'true');
    });
}

function showInstallBanner() {
    if (!deferredInstallPrompt || document.querySelector('[data-pwa-install-banner]')) {
        return;
    }

    const banner = document.createElement('div');
    banner.className = 'pwa-install-banner';
    banner.dataset.pwaInstallBanner = 'true';
    banner.innerHTML = `
        <div>
            <strong>Install attendance app</strong>
            <span>Mas mabilis buksan sa phone at may offline attendance queue.</span>
        </div>
        <button type="button" class="btn btn-primary" data-pwa-install>Install</button>
        <button type="button" class="pwa-install-dismiss" data-pwa-dismiss aria-label="Dismiss install prompt">x</button>
    `;

    document.body.appendChild(banner);

    banner.querySelector('[data-pwa-install]')?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice.catch(() => null);
        deferredInstallPrompt = null;
        banner.remove();
    });

    banner.querySelector('[data-pwa-dismiss]')?.addEventListener('click', () => {
        banner.remove();
    });
}

function bindConnectionState() {
    const update = () => {
        document.body.classList.toggle('is-offline', !navigator.onLine);
        updateOfflineBadge();
        if (navigator.onLine) {
            syncQueuedAttendance();
        }
    };

    window.addEventListener('online', update);
    window.addEventListener('offline', update);
    update();
}

function updateOfflineBadge() {
    const pending = getQueuedAttendance().length;
    let badge = document.querySelector('[data-offline-status]');

    if (navigator.onLine && pending === 0) {
        badge?.remove();
        return;
    }

    if (!badge) {
        badge = document.createElement('div');
        badge.className = 'offline-status';
        badge.dataset.offlineStatus = 'true';
        document.body.appendChild(badge);
    }

    badge.textContent = navigator.onLine
        ? `${pending} attendance ${pending === 1 ? 'set' : 'sets'} waiting to sync`
        : `Offline mode${pending > 0 ? ` / ${pending} queued` : ''}`;
}

function getQueuedAttendance() {
    try {
        return JSON.parse(localStorage.getItem(ATTENDANCE_QUEUE_KEY) || '[]');
    } catch (error) {
        return [];
    }
}

function setQueuedAttendance(queue) {
    localStorage.setItem(ATTENDANCE_QUEUE_KEY, JSON.stringify(queue));
    updateOfflineBadge();
}

function queueAttendanceSubmission(form) {
    const formData = new FormData(form);
    const payload = {
        id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
        action: form.action,
        method: (form.method || 'POST').toUpperCase(),
        createdAt: new Date().toISOString(),
        entries: Array.from(formData.entries()),
    };

    const queue = getQueuedAttendance();
    const assignmentId = formData.get('assignment_id');
    const subjectId = formData.get('subject_id');
    const scheduleId = formData.get('class_schedule_id') || '';
    const attendanceDate = formData.get('attendance_date');
    const sameClassIndex = queue.findIndex((item) => {
        const itemData = new Map(item.entries);
        return itemData.get('assignment_id') === assignmentId
            && itemData.get('subject_id') === subjectId
            && (itemData.get('class_schedule_id') || '') === scheduleId
            && itemData.get('attendance_date') === attendanceDate;
    });

    if (sameClassIndex >= 0) {
        queue.splice(sameClassIndex, 1, payload);
    } else {
        queue.push(payload);
    }

    setQueuedAttendance(queue);
}

async function syncQueuedAttendance() {
    if (!navigator.onLine) {
        updateOfflineBadge();
        return;
    }

    let queue = getQueuedAttendance();
    if (queue.length === 0) {
        updateOfflineBadge();
        return;
    }

    for (const item of [...queue]) {
        const formData = new FormData();
        item.entries.forEach(([name, value]) => formData.append(name, value));

        try {
            const response = await fetch(item.action, {
                method: item.method || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                const message = data.message || Object.values(data.errors || {})[0]?.[0] || 'Queued attendance sync failed.';
                throw new Error(message);
            }

            queue = getQueuedAttendance().filter((queued) => queued.id !== item.id);
            setQueuedAttendance(queue);
            showToast('success', 'Queued attendance synced to admin.');
        } catch (error) {
            console.warn('Attendance sync paused:', error);
            updateOfflineBadge();
            break;
        }
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function loadAdminSchoolCalendar() {
    const calendar = document.getElementById('admin-calendar-container');
    const holidays = document.getElementById('admin-holiday-container');
    const status = document.getElementById('admin-calendar-status');
    const monthTitle = document.getElementById('admin-calendar-month');

    if (!calendar || !holidays) {
        return;
    }

    try {
        const response = await fetch('/api/school-calendar', {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load school calendar.');
        }

        const data = await response.json();
        const today = new Date(`${data.date}T00:00:00`);
        const year = today.getFullYear();
        const month = today.getMonth();
        const holidayByDate = new Map((data.holidays || []).map((holiday) => [holiday.date, holiday]));
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();

        if (monthTitle) {
            monthTitle.textContent = data.month || 'Calendar';
        }

        if (status) {
            status.textContent = data.is_no_class_day ? data.today_label : 'Regular school day';
        }

        let calendarHtml = '<div class="admin-calendar-days">';
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach((day) => {
            calendarHtml += `<span class="calendar-day-name">${day}</span>`;
        });

        for (let i = 0; i < firstDay; i++) {
            calendarHtml += '<span class="calendar-date is-empty"></span>';
        }

        for (let date = 1; date <= lastDate; date++) {
            const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            const itemDate = new Date(`${dateKey}T00:00:00`);
            const holiday = holidayByDate.get(dateKey);
            const classes = [
                'calendar-date',
                date === today.getDate() ? 'is-today' : '',
                holiday ? 'is-holiday' : '',
                [0, 6].includes(itemDate.getDay()) ? 'is-weekend' : '',
            ].filter(Boolean).join(' ');
            const title = holiday ? ` title="${escapeHtml(holiday.name)}"` : '';
            calendarHtml += `<span class="${classes}"${title}>${date}</span>`;
        }

        calendarHtml += '</div>';
        calendar.innerHTML = calendarHtml;

        const upcoming = data.upcoming || [];
        holidays.innerHTML = upcoming.length > 0
            ? upcoming.map((holiday) => `
                <div class="schedule-item holiday-item">
                    <div class="schedule-item-time">${escapeHtml(formatReadableDate(holiday.date))}</div>
                    <div class="schedule-item-title">${escapeHtml(holiday.name)}</div>
                    <span class="holiday-type">${escapeHtml(holiday.type || 'holiday')}</span>
                </div>
            `).join('')
            : '<div class="schedule-placeholder">No upcoming holidays found.</div>';
    } catch (error) {
        if (status) {
            status.textContent = 'Unavailable';
        }
        calendar.innerHTML = '<div class="calendar-placeholder">Unable to load calendar</div>';
        holidays.innerHTML = '<div class="schedule-placeholder">Unable to load holidays</div>';
    }
}

function formatReadableDate(dateString) {
    const date = new Date(`${dateString}T00:00:00`);
    return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function bindLiveSearch(root = document) {
    root.querySelectorAll('[data-live-search]').forEach((input) => {
        if (input.dataset.boundLiveSearch) {
            return;
        }

        input.dataset.boundLiveSearch = 'true';
        const targetSelector = input.dataset.liveSearchTarget;
        if (!targetSelector) {
            return;
        }

        const filter = () => {
            const terms = input.value
                .toLowerCase()
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            document.querySelectorAll(targetSelector).forEach((item) => {
                const haystack = (item.dataset.searchText || item.textContent || '').toLowerCase();
                item.hidden = terms.length > 0 && !terms.every((term) => haystack.includes(term));
            });
        };

        input.addEventListener('input', filter);
        filter();
    });
}

function bindAjaxAdminPanels(root = document) {
    root.querySelectorAll('[data-ajax-panel]').forEach((panel) => {
        if (panel.dataset.boundAjaxPanel) {
            return;
        }

        panel.dataset.boundAjaxPanel = 'true';
        const panelName = panel.dataset.ajaxPanel;

        panel.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link || !panel.contains(link)) {
                return;
            }

            if (!link.closest('.pagination-shell') && !link.classList.contains('btn-outline-primary')) {
                return;
            }

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) {
                return;
            }

            event.preventDefault();
            loadAjaxPanel(panelName, url.toString());
        });

        panel.querySelectorAll('form').forEach((form) => {
            if ((form.method || 'get').toLowerCase() !== 'get') {
                return;
            }

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const url = `${form.action}?${new URLSearchParams(new FormData(form)).toString()}`;
                loadAjaxPanel(panelName, url);
            });
        });
    });
}

function bindGlobalAjaxPanelNavigation() {
    if (document.body.dataset.boundGlobalAjaxPanels) {
        return;
    }

    document.body.dataset.boundGlobalAjaxPanels = 'true';

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        const panel = link?.closest('[data-ajax-panel]');

        if (!link || !panel) {
            return;
        }

        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) {
            return;
        }

        const isPagination = Boolean(link.closest('.pagination-shell, nav[role="navigation"]'));
        const isPanelControl = link.classList.contains('btn') || link.classList.contains('btn-outline-primary');

        if (!isPagination && !isPanelControl) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        loadAjaxPanel(panel.dataset.ajaxPanel, url.toString());
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form');
        const panel = form?.closest('[data-ajax-panel]');

        if (!form || !panel || (form.method || 'get').toLowerCase() !== 'get') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        const url = `${form.action}?${new URLSearchParams(new FormData(form)).toString()}`;
        loadAjaxPanel(panel.dataset.ajaxPanel, url);
    }, true);
}

async function loadAjaxPanel(panelName, url) {
    const currentPanel = document.querySelector(`[data-ajax-panel="${panelName}"]`);
    if (!currentPanel) {
        window.location.href = url;
        return;
    }

    const scrollY = window.scrollY;
    currentPanel.classList.add('is-ajax-loading');

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load records.');
        }

        const html = await response.text();
        const nextDocument = new DOMParser().parseFromString(html, 'text/html');
        const nextPanel = nextDocument.querySelector(`[data-ajax-panel="${panelName}"]`);

        if (!nextPanel) {
            throw new Error('Updated panel was not found.');
        }

        currentPanel.replaceWith(nextPanel);
        window.history.pushState({}, '', url);
        window.scrollTo({ top: scrollY, behavior: 'auto' });

        bindLiveSearch(nextPanel);
        bindAjaxAdminPanels(nextPanel);
        bindToasts(document);
    } catch (error) {
        currentPanel.classList.remove('is-ajax-loading');
        showToast('danger', error.message || 'Unable to load records.');
    }
}

function bindAjaxAttendance(root = document) {
    const loadForm = root.querySelector('[data-attendance-load-form]');
    const saveForm = root.querySelector('[data-attendance-save-form]');

    if (loadForm && !loadForm.dataset.boundAjaxAttendance) {
        loadForm.dataset.boundAjaxAttendance = 'true';

        const loadClass = async () => {
            const url = `${loadForm.action}?${new URLSearchParams(new FormData(loadForm)).toString()}`;
            const panels = document.querySelectorAll('[data-attendance-filter-panel], [data-attendance-panel]');
            panels.forEach((panel) => panel.classList.add('is-loading'));

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load class.');
                }

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextFilterPanel = nextDocument.querySelector('[data-attendance-filter-panel]');
                const nextAttendancePanel = nextDocument.querySelector('[data-attendance-panel]');
                const currentFilterPanel = document.querySelector('[data-attendance-filter-panel]');
                const currentAttendancePanel = document.querySelector('[data-attendance-panel]');

                if (!nextFilterPanel || !nextAttendancePanel || !currentFilterPanel || !currentAttendancePanel) {
                    throw new Error('Class view is incomplete.');
                }

                currentFilterPanel.replaceWith(nextFilterPanel);
                currentAttendancePanel.replaceWith(nextAttendancePanel);
                window.history.replaceState({}, '', url);
                bindLiveSearch(document);
                bindAjaxAttendance(document);
            } catch (error) {
                showToast('danger', error.message || 'Unable to load class.');
            } finally {
                document
                    .querySelectorAll('[data-attendance-filter-panel], [data-attendance-panel]')
                    .forEach((panel) => panel.classList.remove('is-loading'));
            }
        };

        loadForm.querySelectorAll('[data-attendance-autoload]').forEach((field) => {
            field.addEventListener('change', loadClass);
        });

        loadForm.addEventListener('submit', (event) => {
            event.preventDefault();
            loadClass();
        });
    }

    if (saveForm && !saveForm.dataset.boundAjaxAttendance) {
        saveForm.dataset.boundAjaxAttendance = 'true';

        bindAttendanceStatusInputs(saveForm);
        syncDuplicateAttendanceInputs(saveForm);
        saveForm.addEventListener('input', () => syncDuplicateAttendanceInputs(saveForm));
        saveForm.addEventListener('change', () => syncDuplicateAttendanceInputs(saveForm));

        saveForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            syncDuplicateAttendanceInputs(saveForm);

            const submitButton = saveForm.querySelector('[type="submit"]');
            const originalText = submitButton?.textContent;
            submitButton?.setAttribute('disabled', 'disabled');
            if (submitButton) {
                submitButton.textContent = 'Saving...';
            }

            try {
                if (!navigator.onLine) {
                    queueAttendanceSubmission(saveForm);
                    showToast('success', 'Attendance saved offline. It will sync when internet comes back.');
                    return;
                }

                const response = await fetch(saveForm.action, {
                    method: 'POST',
                    body: new FormData(saveForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const message = data.message || Object.values(data.errors || {})[0]?.[0] || 'Unable to save attendance.';
                    throw new Error(message);
                }

                showToast('success', data.message || 'Attendance saved successfully.');
                if (data.redirect_url) {
                    window.history.replaceState({}, '', data.redirect_url);
                }
            } catch (error) {
                if (error instanceof TypeError || !navigator.onLine) {
                    queueAttendanceSubmission(saveForm);
                    showToast('success', 'Connection dropped, so attendance was saved offline and queued for sync.');
                } else {
                    showToast('danger', error.message || 'Unable to save attendance.');
                }
            } finally {
                if (submitButton) {
                    submitButton.textContent = originalText || 'Save Attendance';
                    submitButton.removeAttribute('disabled');
                }
            }
        });
    }
}

function bindAttendanceStatusInputs(form) {
    form.querySelectorAll('[data-attendance-status-option]').forEach((input) => {
        if (input.dataset.boundAttendanceStatus) {
            return;
        }

        input.dataset.boundAttendanceStatus = 'true';
        input.addEventListener('change', () => {
            if (!input.checked) {
                return;
            }

            const studentId = input.dataset.attendanceStatusOption;
            const statusInput = form.querySelector(`[data-attendance-status-value="${studentId}"]`);
            if (statusInput) {
                statusInput.value = input.value;
            }

            form
                .querySelectorAll(`[data-attendance-status-option="${studentId}"]`)
                .forEach((option) => {
                    option.checked = option.value === input.value;
                });
        });
    });
}

function syncDuplicateAttendanceInputs(form) {
    const inputsByName = new Map();
    form.querySelectorAll('input[name^="remarks["]').forEach((input) => {
        const inputs = inputsByName.get(input.name) || [];
        inputs.push(input);
        inputsByName.set(input.name, inputs);
    });

    inputsByName.forEach((inputs) => {
        if (inputs.length < 2) {
            return;
        }

        const focused = inputs.find((input) => input === document.activeElement);
        const source = focused || inputs.find((input) => input.value.trim() !== '') || inputs[0];
        inputs.forEach((input) => {
            if (input !== source) {
                input.value = source.value;
            }
        });
    });
}

// Calendar Data Loader
async function loadCalendarData() {
    const container = document.getElementById('calendar-container');
    if (!container) return;

    try {
        // Generate a simple calendar for this month
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        
        let html = `<div style="text-align: center; margin-bottom: 16px;">
            <strong style="font-size: 14px; color: var(--ink);">${monthNames[month]} ${year}</strong>
        </div>`;
        
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
        
        html += '<div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; font-size: 12px;">';
        
        // Day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            html += `<div style="text-align: center; font-weight: 600; color: var(--muted); padding: 4px;">${day}</div>`;
        });
        
        // Empty cells
        for (let i = 0; i < firstDay; i++) {
            html += '<div></div>';
        }
        
        // Dates
        for (let i = 1; i <= lastDate; i++) {
            const isToday = i === now.getDate();
            const style = isToday ? 
                'background: var(--primary); color: white; border-radius: 6px; font-weight: 600;' :
                'color: var(--ink);';
            html += `<div style="text-align: center; padding: 6px; ${style}">${i}</div>`;
        }
        
        html += '</div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading calendar:', error);
        container.innerHTML = '<div class="calendar-placeholder">Unable to load calendar</div>';
    }
}

// Schedule Data Loader
async function loadScheduleData() {
    const container = document.getElementById('schedule-container');
    if (!container) return;

    try {
        // Check if we can fetch schedule data from the server
        const response = await fetch('/api/schedules/today', {
            headers: {
                'Accept': 'application/json',
            }
        }).catch(() => null);

        if (response && response.ok) {
            const data = await response.json();
            let html = '';
            
            if (data.schedules && data.schedules.length > 0) {
                data.schedules.forEach(schedule => {
                    html += `
                        <div class="schedule-item">
                            <div class="schedule-item-time">${schedule.time}</div>
                            <div class="schedule-item-title">${schedule.title}</div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="schedule-placeholder">No classes today</div>';
            }
            container.innerHTML = html;
        } else {
            throw new Error('No API available');
        }
    } catch (error) {
        console.warn('Schedule API not available:', error);
        // Fallback UI
        const now = new Date();
        const hours = now.getHours();
        let timeSlots = [];
        
        for (let i = 0; i < 5; i++) {
            const hour = hours + i;
            if (hour < 17) {
                timeSlots.push({
                    time: `${String(hour % 12 || 12).padStart(2, '0')}:00 ${hour < 12 ? 'AM' : 'PM'}`,
                    title: hour < 12 ? 'Morning Classes' : 'Afternoon Classes'
                });
            }
        }

        let html = timeSlots.length > 0 ? 
            timeSlots.map(slot => `
                <div class="schedule-item">
                    <div class="schedule-item-time">${slot.time}</div>
                    <div class="schedule-item-title">${slot.title}</div>
                </div>
            `).join('') :
            '<div class="schedule-placeholder">No upcoming classes</div>';
        
        container.innerHTML = html;
    }
}

// Upcoming Classes Loader
async function loadUpcomingClasses() {
    const container = document.getElementById('upcoming-container');
    if (!container) return;

    try {
        const response = await fetch('/api/classes/upcoming', {
            headers: {
                'Accept': 'application/json',
            }
        }).catch(() => null);

        if (response && response.ok) {
            const data = await response.json();
            let html = '';
            
            if (data.classes && data.classes.length > 0) {
                data.classes.forEach(cls => {
                    html += `
                        <div class="upcoming-item">
                            <div class="upcoming-item-time">${cls.date} at ${cls.time}</div>
                            <div class="upcoming-item-title">${cls.subject}</div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="upcoming-placeholder">No upcoming classes</div>';
            }
            container.innerHTML = html;
        } else {
            throw new Error('No API available');
        }
    } catch (error) {
        console.warn('Upcoming classes API not available:', error);
        // Fallback: Show mock data
        const mockData = [
            { subject: 'Mathematics 101', date: 'Tomorrow', time: '09:00 AM' },
            { subject: 'English Literature', date: 'Tomorrow', time: '02:00 PM' },
            { subject: 'Physics Lab', date: 'Thursday', time: '10:30 AM' }
        ];
        
        let html = mockData.map(cls => `
            <div class="upcoming-item">
                <div class="upcoming-item-time">${cls.date} at ${cls.time}</div>
                <div class="upcoming-item-title">${cls.subject}</div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }
}

// Weather Data Loader
async function loadWeatherData() {
    const container = document.getElementById('weather-container');
    if (!container) return;

    try {
        // Using Open-Meteo API (completely free, no key required)
        // Default coordinates are for Manila, Philippines
        const response = await fetch('https://api.open-meteo.com/v1/forecast?latitude=14.5994&longitude=120.9842&current=temperature_2m,relative_humidity_2m,weather_code&temperature_unit=celsius', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        });

        if (response.ok) {
            const data = await response.json();
            const current = data.current;

            // Map weather codes to descriptions
            const weatherDescriptions = {
                0: '☀️ Clear sky',
                1: '🌤️ Mostly clear',
                2: '⛅ Partly cloudy',
                3: '☁️ Overcast',
                45: '🌫️ Foggy',
                48: '🌫️ Foggy with rime',
                51: '🌧️ Light drizzle',
                53: '🌧️ Moderate drizzle',
                55: '🌧️ Heavy drizzle',
                61: '🌦️ Slight rain',
                63: '🌧️ Moderate rain',
                65: '⛈️ Heavy rain',
                71: '❄️ Slight snow',
                73: '❄️ Moderate snow',
                75: '❄️ Heavy snow',
                80: '🌦️ Moderate showers',
                81: '⛈️ Heavy showers',
                82: '⛈️ Violent showers',
                85: '❄️ Light snow showers',
                86: '❄️ Heavy snow showers',
                95: '⛈️ Thunderstorm',
                96: '⛈️ Thunderstorm with hail',
                99: '⛈️ Thunderstorm with hail'
            };

            const description = weatherDescriptions[current.weather_code] || 'Weather info unavailable';
            const temperature = Math.round(current.temperature_2m);
            const humidity = current.relative_humidity_2m;

            let html = `
                <div class="weather-info">
                    <div class="weather-current">
                        <div class="weather-temp">${temperature}°C</div>
                        <div class="weather-desc">${description}</div>
                        <div class="weather-detail">💧 Humidity: ${humidity}%</div>
                    </div>
                </div>
            `;
            container.innerHTML = html;
        } else {
            throw new Error('Weather API failed');
        }
    } catch (error) {
        console.warn('Weather API error:', error);
        // Fallback UI
        container.innerHTML = `
            <div class="weather-placeholder">
                Weather data unavailable
            </div>
        `;
    }
}
