import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast-notice').forEach((toast) => {
        const close = () => {
            toast.classList.add('is-hiding');
            setTimeout(() => toast.remove(), 200);
        };

        toast.querySelector('.toast-close')?.addEventListener('click', close);
        setTimeout(close, 4200);
    });

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

    document.querySelectorAll('[data-live-search]').forEach((input) => {
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
});

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
