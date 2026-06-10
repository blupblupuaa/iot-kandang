function appUrl(path) {
    const base = (window.APP_BASE || '').replace(/\/$/, '');
    const file = String(path).replace(/^\//, '');
    return (base ? base + '/' : '/') + file;
}
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => navLinks.classList.toggle('open'));
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navLinks.contains(e.target))
            navLinks.classList.remove('open');
    });
}
const SENSOR_STATUS_LABEL = { normal:'Normal', warning:'Perhatian', danger:'Bahaya' };
const SENSOR_UNITS = { suhu:'°C', humidity:'%', amonia:'ppm', cahaya:'lux', pakan:'g' };
function formatSensorValue(key, value) { if (value === null || value === undefined) return '—'; return value; }
function getSensorBadgeLabel(key, value, status) {
    if (key === 'pakan' && (value === null || value === undefined)) return 'Belum terpasang';
    return SENSOR_STATUS_LABEL[status] || status;
}
function updateSensorCard(key, value, status) {
    const card = document.querySelector(`[data-sensor="${key}"]`);
    if (!card) return;
    card.className = `sensor-card status-${status}`;
    const unit = SENSOR_UNITS[key] || '';
    const display = formatSensorValue(key, value);
    const unitHtml = key === 'pakan' && (value === null || value === undefined) ? '' : `<span class="sensor-unit">${unit}</span>`;
    const valueEl = card.querySelector('.sensor-value');
    if (valueEl) valueEl.innerHTML = display + unitHtml;
    const badge = card.querySelector('.sensor-badge');
    if (badge) { badge.className = `sensor-badge badge-${status}`; badge.textContent = getSensorBadgeLabel(key, value, status); }
}
function renderConditionAlerts(statuses) {
    const container = document.getElementById('conditionAlerts');
    if (!container || !statuses) return;
    let html = '';
    for (const [key, st] of Object.entries(statuses)) {
        if (st === 'danger') html += `<div class="alert alert-danger">🚨 <strong>${key.toUpperCase()}</strong> dalam kondisi BAHAYA!</div>`;
        else if (st === 'warning') html += `<div class="alert alert-warning">⚠️ <strong>${key.toUpperCase()}</strong> mendekati batas normal.</div>`;
    }
    container.innerHTML = html;
}
function updateSourceBadge(data) {
    const el = document.getElementById('sourceBadge');
    if (!el || !data) return;
    el.style.cssText = 'padding:0.3rem 0.8rem; margin:0; font-size:0.75rem;';
    if (data.source === 'esp32') { el.className = 'alert alert-success'; el.textContent = '● Live ESP32'; }
    else if (data.source === 'cached') { el.className = 'alert alert-warning'; el.textContent = `⚠️ Offline — ${data.saved_at||'-'}`; }
    else { el.className = 'alert alert-info'; el.textContent = 'Mode Demo'; }
}
function updateMonitorUI(data, statuses) {
    if (!data) return;
    ['suhu','humidity','amonia','cahaya','pakan'].forEach(key => updateSensorCard(key, data[key]??null, statuses?.[key]||'normal'));
    renderConditionAlerts(statuses);
    updateSourceBadge(data);
}
async function fetchSensorData() {
    const res = await fetch(appUrl('api.php'), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'sensor'}) });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    return json;
}
function startSensorPolling(intervalMs) {
    const counter = document.getElementById('refreshCounter');
    if (!document.querySelector('[data-sensor]')) return;
    let seconds = Math.floor(intervalMs / 1000), polling = false;
    async function poll() {
        if (polling) return;
        polling = true;
        if (counter) counter.textContent = '…';
        try {
            const json = await fetchSensorData();
            updateMonitorUI(json.data, json.status);
            if (typeof appendMonitorHistory === 'function') appendMonitorHistory(json.data);
        } catch { if (typeof showToast === 'function') showToast('Gagal memperbarui data', 'warning'); }
        finally { polling = false; seconds = Math.floor(intervalMs/1000); if (counter) counter.textContent = seconds+'s'; }
    }
    setInterval(() => { if (seconds <= 0) { poll(); return; } seconds--; if (counter) counter.textContent = seconds+'s'; }, 1000);
}
function sendControl(device, state, toggleEl) {
    const wrapper = toggleEl ? toggleEl.closest('.toggle-wrapper') : null;
    const stateEl = wrapper ? wrapper.querySelector('.toggle-state') : null;
    if (stateEl) { stateEl.textContent = state?'ON':'OFF'; stateEl.className = 'toggle-state '+(state?'on':'off'); }
    fetch(appUrl('api.php'), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'control', device, state}) })
    .then(r=>r.json()).then(data => showToast(data.success ? `${device} berhasil di${state?'aktifkan':'matikan'}` : `Gagal: ${device}`, data.success?'success':'danger'))
    .catch(() => showToast('Tidak dapat terhubung','danger'));
}
function sendThreshold(e) {
    e.preventDefault();
    const payload = {action:'threshold'};
    new FormData(e.target).forEach((v,k) => payload[k] = parseFloat(v));
    fetch(appUrl('api.php'), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) })
    .then(r=>r.json()).then(data => showToast(data.success?'Threshold disimpan!':'Gagal simpan threshold', data.success?'success':'danger'))
    .catch(() => showToast('Tidak dapat terhubung','danger'));
}
function showToast(message, type='success') {
    let c = document.getElementById('toast-container');
    if (!c) { c = document.createElement('div'); c.id='toast-container'; c.style.cssText='position:fixed;bottom:1.5rem;right:1.5rem;display:flex;flex-direction:column;gap:0.5rem;z-index:9999;pointer-events:none;'; document.body.appendChild(c); }
    const colors = { success:{bg:'rgba(0,229,160,0.12)',border:'rgba(0,229,160,0.3)',text:'#00e5a0'}, warning:{bg:'rgba(245,166,35,0.12)',border:'rgba(245,166,35,0.3)',text:'#f5a623'}, danger:{bg:'rgba(255,77,109,0.12)',border:'rgba(255,77,109,0.3)',text:'#ff4d6d'} };
    const col = colors[type]||colors.success;
    const t = document.createElement('div');
    t.style.cssText = `padding:.75rem 1.1rem;background:${col.bg};border:1px solid ${col.border};color:${col.text};border-radius:8px;font-family:'Outfit',sans-serif;font-size:.875rem;font-weight:500;backdrop-filter:blur(8px);animation:fadeIn .3s ease;pointer-events:auto;max-width:280px;`;
    t.textContent = message; c.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3000);
}

// Profil: slide vertikal dengan animasi fade in/out saat scroll
function initProfilCarousel() {
    const sectionList = Array.from(document.querySelectorAll('.profil-slide'));
    const dots        = document.querySelectorAll('.profil-dot');
    const counter     = document.getElementById('profilCurrent');
    const total       = parseInt(document.getElementById('profilTotal')?.textContent || '0', 10);
    const prevBtn     = document.getElementById('profilPrev');
    const nextBtn     = document.getElementById('profilNext');
    const container   = document.getElementById('profilSections');
    if (!container || !sectionList.length) return;

    let activeIndex = 0;
    let scrollTimer;

    function updateCounter(index) {
        if (!counter) return;
        if (index === 0) {
            counter.textContent = 'Profil';
        } else {
            counter.textContent = `${index} / ${total}`;
        }
    }

    function updateDots(index) {
        dots.forEach((dot, idx) => dot.classList.toggle('active', idx === index - 1));
    }

    function setActive(index) {
        const newIndex = Math.max(0, Math.min(index, sectionList.length - 1));
        if (newIndex === activeIndex) return;

        const previous = sectionList[activeIndex];
        const next = sectionList[newIndex];

        previous?.classList.remove('is-active');
        previous?.classList.add('is-exiting');
        next?.classList.add('is-active');
        next?.classList.remove('is-exiting');

        setTimeout(() => {
            previous?.classList.remove('is-exiting');
        }, 600);

        activeIndex = newIndex;
        updateCounter(newIndex);
        updateDots(newIndex);
    }

    function scrollToSection(index) {
        const target = sectionList[Math.max(0, Math.min(index, sectionList.length - 1))];
        if (!target) return;
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function detectActive() {
        const centerY = container.scrollTop + container.clientHeight / 2;
        let closest = 0;
        let minDist = Infinity;

        sectionList.forEach((section, idx) => {
            const sectionCenter = section.offsetTop + section.offsetHeight / 2;
            const distance = Math.abs(sectionCenter - centerY);
            if (distance < minDist) {
                minDist = distance;
                closest = idx;
            }
        });

        setActive(closest);
    }

    container.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(detectActive, 80);
    }, { passive: true });

    prevBtn?.addEventListener('click', () => scrollToSection(activeIndex - 1));
    nextBtn?.addEventListener('click', () => scrollToSection(activeIndex + 1));

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const index = parseInt(dot.dataset.index, 10) + 1;
            scrollToSection(index);
        });
    });

    sectionList[0].classList.add('is-active');
    updateCounter(0);
    updateDots(0);
}

document.addEventListener('DOMContentLoaded', initProfilCarousel);
