// =============================================================
//  KANDANGSMART — Global JavaScript
// =============================================================

function appUrl(path) {
    const base = (window.APP_BASE || '').replace(/\/$/, '');
    const file = String(path).replace(/^\//, '');
    return (base ? base + '/' : '/') + file;
}

// --- Mobile Navbar Toggle ---
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
    });
    // Tutup menu saat klik di luar
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('open');
        }
    });
}

// --- Monitor: AJAX polling sensor (monitor.php) ---
const SENSOR_STATUS_LABEL = {
    normal:  'Normal',
    warning: 'Perhatian',
    danger:  'Bahaya',
};

const SENSOR_UNITS = {
    suhu:     '°C',
    humidity: '%',
    amonia:   'ppm',
    cahaya:   'lux',
    pakan:    'g',
};

function formatSensorValue(key, value) {
    if (value === null || value === undefined) return '—';
    return value;
}

function getSensorBadgeLabel(key, value, status) {
    if (key === 'pakan' && (value === null || value === undefined)) {
        return 'Belum terpasang';
    }
    return SENSOR_STATUS_LABEL[status] || status;
}

function updateSensorCard(key, value, status) {
    const card = document.querySelector(`[data-sensor="${key}"]`);
    if (!card) return;

    card.className = `sensor-card status-${status}`;

    const unit = SENSOR_UNITS[key] || '';
    const display = formatSensorValue(key, value);
    const unitHtml = key === 'pakan' && (value === null || value === undefined)
        ? ''
        : `<span class="sensor-unit">${unit}</span>`;

    const valueEl = card.querySelector('.sensor-value');
    if (valueEl) valueEl.innerHTML = display + unitHtml;

    const badge = card.querySelector('.sensor-badge');
    if (badge) {
        badge.className = `sensor-badge badge-${status}`;
        badge.textContent = getSensorBadgeLabel(key, value, status);
    }
}

function renderConditionAlerts(statuses) {
    const container = document.getElementById('conditionAlerts');
    if (!container || !statuses) return;

    let html = '';
    for (const [key, st] of Object.entries(statuses)) {
        const label = key.toUpperCase();
        if (st === 'danger') {
            html += `<div class="alert alert-danger">🚨 <strong>${label}</strong> dalam kondisi BAHAYA! Segera periksa kandang.</div>`;
        } else if (st === 'warning') {
            html += `<div class="alert alert-warning">⚠️ <strong>${label}</strong> mendekati batas normal. Pantau terus.</div>`;
        }
    }
    container.innerHTML = html;
}

function updateSourceBadge(data) {
    const el = document.getElementById('sourceBadge');
    if (!el || !data) return;

    const base = 'alert';
    el.style.cssText = 'padding:0.3rem 0.8rem; margin:0; font-size:0.75rem;';

    if (data.source === 'esp32') {
        el.className = `${base} alert-success`;
        el.textContent = '● Live ESP32';
    } else if (data.source === 'cached') {
        el.className = `${base} alert-warning`;
        el.textContent = `⚠️ ESP32 Offline — Data terakhir: ${data.saved_at || '-'}`;
    } else {
        el.className = `${base} alert-info`;
        el.textContent = 'Mode Demo';
    }
}

function updateMonitorUI(data, statuses) {
    if (!data) return;

    const keys = ['suhu', 'humidity', 'amonia', 'cahaya', 'pakan'];
    keys.forEach((key) => {
        updateSensorCard(key, data[key] ?? null, statuses?.[key] || 'normal');
    });

    renderConditionAlerts(statuses);

    const ts = document.getElementById('sensorTimestamp');
    if (ts && data.timestamp) {
        ts.textContent = 'Update: ' + data.timestamp;
    }

    updateSourceBadge(data);
}

async function fetchSensorData() {
    const res = await fetch(appUrl('api.php'), {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ action: 'sensor' }),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal ambil data');
    return json;
}

function startSensorPolling(intervalMs) {
    const counter = document.getElementById('refreshCounter');
    if (!document.querySelector('[data-sensor]')) return;

    let seconds = Math.floor(intervalMs / 1000);
    let polling = false;

    async function poll() {
        if (polling) return;
        polling = true;
        if (counter) counter.textContent = '…';

        try {
            const json = await fetchSensorData();
            updateMonitorUI(json.data, json.status);
            if (typeof appendMonitorHistory === 'function') {
                appendMonitorHistory(json.data);
            }
        } catch {
            if (typeof showToast === 'function') {
                showToast('Gagal memperbarui data sensor', 'warning');
            }
        } finally {
            polling = false;
            seconds = Math.floor(intervalMs / 1000);
            if (counter) counter.textContent = seconds + 's';
        }
    }

    setInterval(() => {
        if (seconds <= 0) {
            if (counter) counter.textContent = '0s';
            poll();
            return;
        }
        seconds--;
        if (counter) counter.textContent = seconds + 's';
    }, 1000);
}

// --- Kirim Kontrol Aktuator (control.php) ---
function sendControl(device, state, toggleEl) {
    const wrapper = toggleEl ? toggleEl.closest('.toggle-wrapper') : null;
    const stateEl = wrapper ? wrapper.querySelector('.toggle-state') : null;

    // Update label langsung (optimistic UI)
    if (stateEl) {
        stateEl.textContent = state ? 'ON' : 'OFF';
        stateEl.className   = 'toggle-state ' + (state ? 'on' : 'off');
    }

    fetch(appUrl('api.php'), {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ action: 'control', device, state })
    })
    .then(r => r.json())
    .then(data => {
        showToast(
            data.success
                ? `${device} berhasil di${state ? 'aktifkan' : 'matikan'}`
                : `Gagal mengontrol ${device}`,
            data.success ? 'success' : 'danger'
        );
    })
    .catch(() => showToast('Tidak dapat terhubung ke server', 'danger'));
}

// --- Kirim Threshold (control.php) ---
function sendThreshold(e) {
    e.preventDefault();
    const form   = e.target;
    const formData = new FormData(form);
    const payload  = { action: 'threshold' };
    formData.forEach((v, k) => payload[k] = parseFloat(v));

    fetch(appUrl('api.php'), {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        showToast(
            data.success ? 'Threshold berhasil disimpan!' : 'Gagal menyimpan threshold',
            data.success ? 'success' : 'danger'
        );
    })
    .catch(() => showToast('Tidak dapat terhubung ke server', 'danger'));
}

// --- Toast Notification ---
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            display: flex; flex-direction: column; gap: 0.5rem;
            z-index: 9999; pointer-events: none;
        `;
        document.body.appendChild(container);
    }

    const colors = {
        success: { bg: 'rgba(0,229,160,0.12)', border: 'rgba(0,229,160,0.3)',  text: '#00e5a0' },
        warning: { bg: 'rgba(245,166,35,0.12)', border: 'rgba(245,166,35,0.3)', text: '#f5a623' },
        danger:  { bg: 'rgba(255,77,109,0.12)', border: 'rgba(255,77,109,0.3)', text: '#ff4d6d' },
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement('div');
    toast.style.cssText = `
        padding: 0.75rem 1.1rem;
        background: ${c.bg};
        border: 1px solid ${c.border};
        color: ${c.text};
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease;
        pointer-events: auto;
        max-width: 280px;
    `;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
