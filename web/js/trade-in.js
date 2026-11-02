/* Trade-in price calculator (site/index). Data comes from window.TRADE_IN. */
(function () {
    'use strict';

    var data = window.TRADE_IN || {catalog: [], batteries: [], damages: [], lowFactor: 0.75};
    var form = document.getElementById('tiForm');
    if (!form) return;

    var $ = function (id) { return document.getElementById(id); };
    var els = {
        search: $('tiSearch'), models: $('tiModels'), modelsEmpty: $('tiModelsEmpty'),
        storageField: $('tiStorageField'), storage: $('tiStorage'), damageField: $('tiDamageField'),
        phone: $('tiPhone'), amount: $('tiAmount'), status: $('tiStatus'), breakdown: $('tiBreakdown'),
        base: $('tiBase'), deductRows: $('tiDeductRows'), deductTotal: $('tiDeductTotal'),
        reset: $('tiReset'), bar: $('tiBar'), barAmount: $('tiBarAmount')
    };

    var state = {brand: null, name: null, phoneId: null};

    var byId = {};
    data.catalog.forEach(function (p) { byId[p.id] = p; });
    var batteryById = {};
    data.batteries.forEach(function (b) { batteryById[b.id] = b; });
    var damageById = {};
    data.damages.forEach(function (d) { damageById[d.id] = d; });

    function fmt(n) { return Math.round(n).toLocaleString('en-US'); }
    function pct(share) { return '−' + (Math.round(share * 1000) / 10) + '%'; }
    function norm(s) { return String(s || '').toLowerCase().replace(/[^a-z0-9+]/g, ''); }

    function pill(name, value, text, checked) {
        var label = document.createElement('label');
        label.className = 'ti-pill';
        var input = document.createElement('input');
        input.type = 'radio';
        input.name = name;
        input.value = value;
        input.checked = !!checked;
        var span = document.createElement('span');
        span.textContent = text;
        label.appendChild(input);
        label.appendChild(span);
        return label;
    }

    function selectedValue(name) {
        var el = form.querySelector('input[name="' + name + '"]:checked');
        return el ? el.value : null;
    }

    /* ---------- Phone picking ---------- */

    function modelNames(brand) {
        var seen = {}, out = [];
        data.catalog.forEach(function (p) {
            if (p.brand === brand && !seen[p.name]) { seen[p.name] = 1; out.push(p.name); }
        });
        return out;
    }

    function renderModels() {
        var q = norm(els.search.value);
        var names = modelNames(state.brand).filter(function (n) {
            return !q || norm(n).indexOf(q) !== -1 || norm(n.replace(/^(iphone|galaxy)\s*/i, '')).indexOf(q) !== -1;
        });
        els.models.textContent = '';
        names.forEach(function (n) {
            els.models.appendChild(pill('model', n, n.replace(/^Galaxy /, ''), n === state.name));
        });
        els.modelsEmpty.hidden = names.length > 0;
    }

    function renderStorage() {
        els.storage.textContent = '';
        if (!state.name) { els.storageField.hidden = true; return; }
        var options = data.catalog.filter(function (p) { return p.brand === state.brand && p.name === state.name; });
        if (options.length === 1 && !options[0].storage) {
            // One price for every storage size: nothing to ask.
            state.phoneId = options[0].id;
            els.storageField.hidden = true;
            return;
        }
        if (options.length === 1) state.phoneId = options[0].id;
        options.forEach(function (p) {
            els.storage.appendChild(pill('phone', p.id, p.storage || 'Any', p.id === state.phoneId));
        });
        els.storageField.hidden = false;
    }

    function setBrand(brand) {
        state.brand = brand;
        state.name = null;
        state.phoneId = null;
        els.search.value = '';
        renderModels();
        renderStorage();
        update();
    }

    /* ---------- Calculation ---------- */

    // Returns null until a phone is chosen.
    function calculate() {
        var phone = byId[state.phoneId];
        if (!phone) return null;

        var deductions = [];
        var condition = selectedValue('condition');
        if (condition === 'damaged') {
            form.querySelectorAll('input[name="damage[]"]:checked').forEach(function (cb) {
                var d = damageById[cb.value];
                if (d) deductions.push({label: d.label, share: +d.deduct});
            });
        }
        var batteryId = selectedValue('battery');
        var battery = batteryId ? batteryById[batteryId] : null;
        if (battery && battery.deduct > 0) {
            deductions.push({label: 'Battery ' + battery.label, share: +battery.deduct});
        }

        var share = deductions.reduce(function (s, d) { return s + d.share; }, 0);
        share = Math.min(Math.max(share, 0), 1);
        var keep = 1 - share;

        var needBattery = data.batteries.length > 0;
        var damageCount = form.querySelectorAll('input[name="damage[]"]:checked').length;
        var missing = !condition ? 'condition'
            : (condition === 'damaged' && damageCount === 0) ? 'damage'
            : (needBattery && !battery) ? 'battery'
            : null;

        return {
            phone: phone,
            deductions: deductions,
            share: share,
            high: Math.floor(phone.max * keep),
            low: Math.floor(phone.min * keep * data.lowFactor),
            complete: !missing,
            missing: missing
        };
    }

    function update() {
        var r = calculate();
        els.deductRows.textContent = '';

        if (!r) {
            els.phone.textContent = 'No phone chosen yet';
            els.amount.textContent = '—';
            els.status.textContent = 'Choose your phone to see its price.';
            els.breakdown.hidden = true;
            els.bar.hidden = true;
            els.amount.parentNode.classList.remove('is-final');
            els.reset.hidden = true;
            return;
        }
        els.reset.hidden = false;

        els.phone.textContent = r.phone.name + (r.phone.storage ? ', ' + r.phone.storage : '');
        els.base.textContent = r.phone.max > r.phone.min
            ? fmt(r.phone.min) + ' – ' + fmt(r.phone.max) + ' JOD'
            : fmt(r.phone.min) + ' JOD';

        r.deductions.forEach(function (d) {
            var row = document.createElement('div');
            var dt = document.createElement('dt');
            var dd = document.createElement('dd');
            dt.textContent = d.label;
            dd.textContent = pct(d.share);
            row.appendChild(dt);
            row.appendChild(dd);
            els.deductRows.appendChild(row);
        });
        els.deductTotal.textContent = r.share > 0 ? pct(r.share) : 'None';
        els.breakdown.hidden = false;

        var text;
        if (r.complete) {
            text = r.low === r.high ? fmt(r.high) : fmt(r.low) + ' – ' + fmt(r.high);
            els.status.textContent = 'Estimated offer for your phone in this condition.';
        } else {
            text = 'Up to ' + fmt(r.high);
            els.status.textContent = {
                condition: 'Tell us its condition to get your offer.',
                damage: 'Choose what is damaged to get your offer.',
                battery: 'Choose the battery health to get your offer.'
            }[r.missing] || 'Answer the questions to get your offer.';
        }
        els.amount.textContent = text;
        els.barAmount.textContent = text;
        els.amount.parentNode.classList.toggle('is-final', r.complete);
        els.bar.hidden = resultVisible;
    }

    // Hide the mobile summary bar while the full offer panel is on screen.
    var resultVisible = false;
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            resultVisible = entries[0].isIntersecting;
            if (byId[state.phoneId]) els.bar.hidden = resultVisible;
        }, {threshold: 0.25}).observe($('tiResult'));
    }

    /* ---------- Events ---------- */

    form.addEventListener('change', function (e) {
        var t = e.target;
        if (t.name === 'brand') { setBrand(t.value); return; }
        if (t.name === 'model') {
            state.name = t.value;
            state.phoneId = null;
            renderStorage();
        }
        if (t.name === 'phone') state.phoneId = +t.value;
        if (t.name === 'condition') {
            var damaged = t.value === 'damaged';
            els.damageField.hidden = !damaged;
            if (!damaged) form.querySelectorAll('input[name="damage[]"]').forEach(function (cb) { cb.checked = false; });
        }
        update();
    });

    els.search.addEventListener('input', renderModels);

    els.reset.addEventListener('click', function () {
        form.reset();
        els.damageField.hidden = true;
        var first = form.querySelector('input[name="brand"]');
        if (first) { first.checked = true; setBrand(first.value); } else { update(); }
        form.scrollIntoView({behavior: 'smooth', block: 'start'});
    });

    var firstBrand = form.querySelector('input[name="brand"]:checked');
    setBrand(firstBrand ? firstBrand.value : null);

    /* ---------- Disclaimer (shown once per browser) ---------- */

    var dialog = $('tiDisclaimer');
    var KEY = 'actionTradeInDisclaimer';
    var accepted = false;
    try { accepted = localStorage.getItem(KEY) === '1'; } catch (e) { /* storage blocked */ }
    if (dialog && !accepted && typeof dialog.showModal === 'function') {
        dialog.showModal();
        dialog.addEventListener('close', function () {
            try { localStorage.setItem(KEY, '1'); } catch (e) { /* ignore */ }
        });
    }
})();
