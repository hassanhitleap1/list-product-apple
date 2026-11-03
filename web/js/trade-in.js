/* Trade-in checklist calculator (site/index).
 * Data comes from window.TRADE_IN: {catalog, steps, lowFactor}.
 * Flow: choose phone -> checklist questions one by one -> offer.
 */
(function () {
    'use strict';

    var data = window.TRADE_IN || {catalog: [], steps: [], lowFactor: 0.75};
    var $ = function (id) { return document.getElementById(id); };
    var stage = $('tiStage');
    if (!stage) return;

    var els = {
        bar: $('tiProgressBar'), barText: $('tiProgressText'), back: $('tiBack'), next: $('tiNext'),
        sidePhone: $('tiSidePhone'), sideOffer: $('tiSideOffer'), sideAmount: $('tiSideAmount'), answers: $('tiAnswers'),
        mBar: $('tiBar'), mBarAmount: $('tiBarAmount'), mBarStep: $('tiBarStep'),
        reject: $('tiReject'), rejectReason: $('tiRejectReason'),
        rejectRestart: $('tiRejectRestart'), rejectBack: $('tiRejectBack')
    };

    var stepById = {};
    data.steps.forEach(function (s) { stepById[s.id] = s; });
    var phoneById = {};
    data.catalog.forEach(function (p) { phoneById[p.id] = p; });
    var brands = [];
    data.catalog.forEach(function (p) { if (brands.indexOf(p.brand) === -1) brands.push(p.brand); });

    var state;
    function freshState() {
        return {brand: brands[0] || null, model: null, phoneId: null, search: '', answers: {}, current: 'phone', confirmed: {}};
    }
    state = freshState();

    /* ---------- Helpers ---------- */

    function el(tag, attrs, children) {
        var n = document.createElement(tag);
        if (attrs) Object.keys(attrs).forEach(function (k) {
            if (k === 'text') n.textContent = attrs[k];
            else if (k === 'class') n.className = attrs[k];
            else if (k.indexOf('on') === 0) n.addEventListener(k.slice(2), attrs[k]);
            else if (attrs[k] !== null && attrs[k] !== undefined && attrs[k] !== false) n.setAttribute(k, attrs[k] === true ? '' : attrs[k]);
        });
        (children || []).forEach(function (c) { if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
        return n;
    }
    function ar(text, cls) { return text ? el('span', {lang: 'ar', dir: 'rtl', class: cls || 'ti-ar', text: text}) : null; }
    function fmt(n) { return Math.round(n).toLocaleString('en-US'); }
    function pct(share) { return '−' + (Math.round(share * 1000) / 10) + '%'; }
    function norm(s) { return String(s || '').toLowerCase().replace(/[^a-z0-9+]/g, ''); }
    function name(step) { return step.short || step.title; }
    function optionOf(step, value) {
        for (var i = 0; i < step.options.length; i++) if (step.options[i].value === value) return step.options[i];
        return null;
    }

    /* ---------- Flow ---------- */

    function isAsked(step) {
        return !step.when || state.answers[step.when.step] === step.when.is;
    }

    // The screens in order for the current answers: phone, the questions that apply, result.
    function sequence() {
        var seq = ['phone'];
        data.steps.forEach(function (s) { if (isAsked(s)) seq.push(s.id); });
        seq.push('result');
        return seq;
    }

    // Drop answers to questions that no longer apply (after an earlier answer changed).
    function pruneAnswers() {
        var changed = true;
        while (changed) {
            changed = false;
            data.steps.forEach(function (s) {
                if (state.answers[s.id] !== undefined && !isAsked(s)) {
                    delete state.answers[s.id];
                    delete state.confirmed[s.id];
                    changed = true;
                }
            });
        }
    }

    function calc() {
        var phone = phoneById[state.phoneId];
        var items = [], share = 0;
        sequence().forEach(function (id) {
            var s = stepById[id];
            if (!s || state.answers[id] === undefined) return;
            var o = optionOf(s, state.answers[id]);
            if (o && o.deduct) { items.push({step: s, option: o}); share += +o.deduct; }
        });
        share = Math.round(share * 1000) / 1000;
        var keep = 1 - share;
        if (!phone) return {phone: null, items: items, share: share};
        var high = Math.floor(phone.max * keep);
        var low = Math.floor(phone.min * keep * data.lowFactor);
        return {phone: phone, items: items, share: share, high: high, low: low, avg: Math.round((high + low) / 2)};
    }

    // First answered question whose answer stops the process, if any.
    function firstReject() {
        var seq = sequence();
        for (var i = 0; i < seq.length; i++) {
            var s = stepById[seq[i]];
            var o = s && optionOf(s, state.answers[s.id]);
            if (o && o.reject) return s.id;
        }
        return null;
    }

    function go(id) {
        // Never move past a rejected answer, and never show a price at 100%+ deduction.
        var seq = sequence(), stop = firstReject();
        if (stop && seq.indexOf(id) > seq.indexOf(stop)) {
            var s = stepById[stop], o = optionOf(s, state.answers[stop]);
            state.current = stop; render();
            reject(name(s) + ': ' + o.label, stop);
            return;
        }
        if (id === 'result' && calc().share >= 1) {
            reject('The device does not reach a price after the checks.', state.current);
            return;
        }
        state.current = id;
        render();
        stage.focus({preventScroll: true});
        var top = stage.getBoundingClientRect().top + window.scrollY - 96;
        if (window.scrollY > top) window.scrollTo({top: top, behavior: 'smooth'});
    }

    function goNext() {
        var seq = sequence();
        var i = seq.indexOf(state.current);
        if (i < seq.length - 1) go(seq[i + 1]);
    }

    function goBack() {
        var seq = sequence();
        var i = seq.indexOf(state.current);
        if (i > 0) go(seq[i - 1]);
    }

    function restart() {
        state = freshState();
        if (els.reject.open) els.reject.close();
        go('phone');
    }

    /* ---------- Reject ---------- */

    var rejectedStep = null;
    function reject(reason, stepId) {
        rejectedStep = stepId;
        els.rejectReason.textContent = reason || '';
        els.rejectReason.hidden = !reason;
        if (typeof els.reject.showModal === 'function') els.reject.showModal();
        else window.alert(reason);
    }
    els.rejectRestart.addEventListener('click', restart);
    els.rejectBack.addEventListener('click', function () {
        if (rejectedStep) {
            delete state.answers[rejectedStep];
            delete state.confirmed[rejectedStep];
            pruneAnswers();
        }
        els.reject.close();
        go(rejectedStep || state.current);
    });

    /* ---------- Answering ---------- */

    function answer(step, option) {
        state.answers[step.id] = option.value;
        delete state.confirmed[step.id];
        pruneAnswers();

        if (option.reject) {
            render();
            reject(name(step) + ': ' + option.label, step.id);
            return;
        }
        if (calc().share >= 1) {
            render();
            reject('The device does not reach a price after the checks.', step.id);
            return;
        }
        if (option.confirm || step.visual) { render(); return; } // wait for Confirm / Continue
        render();
        setTimeout(goNext, 180);
    }

    /* ---------- Screens ---------- */

    function renderPhone() {
        var wrap = el('div', {class: 'ti-q'});
        wrap.appendChild(el('h2', {class: 'ti-q-title', id: 'tiQTitle'}, ['Which phone do you have?', ar('ما هو جهازك؟', 'ti-q-ar')]));

        var brandRow = el('div', {class: 'ti-choices ti-choices--row', role: 'radiogroup', 'aria-label': 'Brand'});
        brands.forEach(function (b) {
            brandRow.appendChild(el('button', {
                type: 'button', class: 'ti-choice ti-choice--pill', role: 'radio', 'aria-checked': String(state.brand === b),
                text: b, onclick: function () {
                    if (state.brand === b) return;
                    state.brand = b; state.model = null; state.phoneId = null; state.search = '';
                    render();
                }
            }));
        });
        wrap.appendChild(brandRow);

        var search = el('input', {
            type: 'search', id: 'tiSearch', class: 'ti-search-input', placeholder: 'Search, e.g. 15 Pro or S24',
            'aria-label': 'Search models', value: state.search
        });
        search.addEventListener('input', function () { state.search = search.value; drawModels(); });
        wrap.appendChild(el('div', {class: 'ti-search'}, [search]));

        wrap.appendChild(el('p', {class: 'ti-label', id: 'tiModelLabel'}, ['Model ', ar('الموديل')]));
        var models = el('div', {class: 'ti-choices ti-choices--row', role: 'radiogroup', 'aria-labelledby': 'tiModelLabel'});
        var empty = el('p', {class: 'ti-empty', text: 'No phone matches that search. Try a shorter name, like “14” or “Fold”.', hidden: true});
        wrap.appendChild(models);
        wrap.appendChild(empty);

        var storageWrap = el('div', {class: 'ti-storage'});
        wrap.appendChild(storageWrap);

        function drawModels() {
            var q = norm(state.search), seen = {}, names = [];
            data.catalog.forEach(function (p) {
                if (p.brand !== state.brand || seen[p.name]) return;
                seen[p.name] = 1;
                var n = norm(p.name), short = norm(p.name.replace(/^(iphone|galaxy)\s*/i, ''));
                if (!q || n.indexOf(q) !== -1 || short.indexOf(q) !== -1) names.push(p.name);
            });
            models.textContent = '';
            names.forEach(function (name) {
                models.appendChild(el('button', {
                    type: 'button', class: 'ti-choice ti-choice--pill', role: 'radio', 'aria-checked': String(state.model === name),
                    text: name.replace(/^Galaxy /, ''), onclick: function () {
                        state.model = name;
                        var opts = data.catalog.filter(function (p) { return p.brand === state.brand && p.name === name; });
                        state.phoneId = opts.length === 1 ? opts[0].id : null;
                        render();
                        var first = document.querySelector('.ti-storage .ti-choice');
                        if (first) first.focus();
                    }
                }));
            });
            empty.hidden = names.length > 0;
        }
        drawModels();

        if (state.model) {
            var opts = data.catalog.filter(function (p) { return p.brand === state.brand && p.name === state.model; });
            if (!(opts.length === 1 && !opts[0].storage)) {
                storageWrap.appendChild(el('p', {class: 'ti-label', id: 'tiStorageLabel'}, ['Storage ', ar('السعة')]));
                var row = el('div', {class: 'ti-choices ti-choices--row', role: 'radiogroup', 'aria-labelledby': 'tiStorageLabel'});
                opts.forEach(function (p) {
                    row.appendChild(el('button', {
                        type: 'button', class: 'ti-choice ti-choice--pill', role: 'radio', 'aria-checked': String(state.phoneId === p.id),
                        text: p.storage || 'All sizes', onclick: function () { state.phoneId = p.id; render(); }
                    }));
                });
                storageWrap.appendChild(row);
            }
        }
        return wrap;
    }

    function renderQuestion(step) {
        var wrap = el('div', {class: 'ti-q'});
        wrap.appendChild(el('h2', {class: 'ti-q-title', id: 'tiQTitle'}, [step.title, ar(step.title_ar, 'ti-q-ar')]));
        if (step.help) {
            wrap.appendChild(el('p', {class: 'ti-q-help'}, [step.help, step.help_ar ? el('span', {lang: 'ar', dir: 'rtl', class: 'ti-q-help-ar', text: step.help_ar}) : null]));
        }

        var chosen = state.answers[step.id];
        var list = el('div', {class: 'ti-choices', role: 'radiogroup', 'aria-labelledby': 'tiQTitle'});
        step.options.forEach(function (o) {
            var badge = o.reject ? el('span', {class: 'ti-badge ti-badge--stop', text: 'Branch check'})
                : o.deduct ? el('span', {class: 'ti-badge', text: pct(o.deduct)}) : null;
            list.appendChild(el('button', {
                type: 'button', class: 'ti-choice', role: 'radio', 'aria-checked': String(chosen === o.value),
                onclick: function () { answer(step, o); }
            }, [
                el('span', {class: 'ti-choice-text'}, [el('span', {text: o.label}), o.label_ar ? el('small', {lang: 'ar', dir: 'rtl', text: o.label_ar}) : null]),
                badge
            ]));
        });
        wrap.appendChild(list);

        var o = chosen !== undefined ? optionOf(step, chosen) : null;
        if (o && o.confirm) {
            var box = el('div', {class: 'ti-confirm'});
            var cb = el('input', {type: 'checkbox', id: 'tiConfirm'});
            cb.checked = !!state.confirmed[step.id];
            cb.addEventListener('change', function () { state.confirmed[step.id] = cb.checked; render(); });
            box.appendChild(el('label', {for: 'tiConfirm', class: 'ti-confirm-label'}, [
                cb, el('span', {class: 'ti-check-box', 'aria-hidden': 'true'}),
                el('span', null, [o.confirm, o.confirm_ar ? el('small', {lang: 'ar', dir: 'rtl', text: o.confirm_ar}) : null])
            ]));
            wrap.appendChild(box);
        }

        if (step.visual === 'scratch') wrap.appendChild(renderScratchVisual(o ? +o.value : 0));
        return wrap;
    }

    function renderResult() {
        var r = calc();
        var wrap = el('div', {class: 'ti-q ti-result'});
        wrap.appendChild(el('h2', {class: 'ti-q-title', id: 'tiQTitle'}, ['Your trade-in offer', ar('عرض الاستبدال', 'ti-q-ar')]));
        wrap.appendChild(el('p', {class: 'ti-result-phone', text: r.phone ? r.phone.name + (r.phone.storage ? ', ' + r.phone.storage : '') : ''}));

        wrap.appendChild(el('div', {class: 'ti-capsule', 'aria-live': 'polite'}, [
            el('span', {class: 'ti-amount', text: r.low === r.high ? fmt(r.high) : fmt(r.low) + ' – ' + fmt(r.high)}),
            el('span', {class: 'ti-currency', text: 'JOD'})
        ]));
        wrap.appendChild(el('p', {class: 'ti-average'}, ['Average price ', el('strong', {text: fmt(r.avg) + ' JOD'}), ' ', ar('متوسط السعر')]));

        var dl = el('dl', {class: 'ti-breakdown'});
        function row(label, value, cls) {
            dl.appendChild(el('div', {class: cls || ''}, [el('dt', {text: label}), el('dd', {text: value})]));
        }
        row('Grade A price', r.phone.max > r.phone.min ? fmt(r.phone.min) + ' – ' + fmt(r.phone.max) + ' JOD' : fmt(r.phone.min) + ' JOD');
        r.items.forEach(function (it) { row(name(it.step) + ': ' + it.option.label, pct(it.option.deduct), 'is-minus'); });
        row('Total deduction', r.share > 0 ? pct(r.share) : 'None', 'is-total');
        wrap.appendChild(dl);

        wrap.appendChild(el('p', {class: 'ti-result-note'}, [
            'Bring your phone to any Action Mobile branch to confirm the final price.',
            el('span', {lang: 'ar', dir: 'rtl', text: 'أحضر جهازك إلى أي فرع من فروع Action Mobile لتأكيد السعر النهائي.'})
        ]));
        wrap.appendChild(el('button', {type: 'button', class: 'ti-btn ti-btn--ghost', onclick: restart}, ['Start over ', ar('ابدأ من جديد', '')]));
        return wrap;
    }

    /* ---------- Scratch drawing (iPhone / Samsung, front and back) ---------- */

    var SVGNS = 'http://www.w3.org/2000/svg';
    function svg(tag, attrs) {
        var n = document.createElementNS(SVGNS, tag);
        Object.keys(attrs || {}).forEach(function (k) { n.setAttribute(k, attrs[k]); });
        return n;
    }

    // Small deterministic random so the scratches don't jump between renders.
    function rng(seed) { return function () { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; }; }

    function phoneSvg(brand, side, coverage, uid) {
        var isApple = brand === 'Apple';
        var W = 120, H = 240, r = isApple ? 22 : 16;
        var s = svg('svg', {viewBox: '0 0 ' + W + ' ' + H, width: W, height: H, role: 'img',
            'aria-label': brand + ' phone ' + side + (coverage ? ', about ' + coverage + '% scratched' : '')});
        var clipId = 'tiClip' + uid;
        var defs = svg('defs');
        var clip = svg('clipPath', {id: clipId});
        clip.appendChild(svg('rect', {x: 4, y: 4, width: W - 8, height: H - 8, rx: r}));
        defs.appendChild(clip);
        s.appendChild(defs);

        s.appendChild(svg('rect', {x: 4, y: 4, width: W - 8, height: H - 8, rx: r, fill: side === 'front' ? '#16203a' : '#e9eef5', stroke: '#16203a', 'stroke-width': 3}));

        if (side === 'front') {
            s.appendChild(svg('rect', {x: 10, y: 10, width: W - 20, height: H - 20, rx: r - 6, fill: '#dff1ff'}));
            if (isApple) s.appendChild(svg('rect', {x: W / 2 - 16, y: 17, width: 32, height: 9, rx: 4.5, fill: '#16203a'}));
            else s.appendChild(svg('circle', {cx: W / 2, cy: 21, r: 4, fill: '#16203a'}));
        } else if (isApple) {
            s.appendChild(svg('rect', {x: 14, y: 14, width: 50, height: 50, rx: 13, fill: '#d3dbe6', stroke: '#16203a', 'stroke-width': 2}));
            [[28, 28], [28, 50], [50, 39]].forEach(function (c) {
                s.appendChild(svg('circle', {cx: c[0], cy: c[1], r: 8.5, fill: '#16203a'}));
                s.appendChild(svg('circle', {cx: c[0], cy: c[1], r: 3.5, fill: '#00a4ff'}));
            });
            s.appendChild(svg('circle', {cx: 50, cy: 21, r: 3, fill: '#fff', stroke: '#16203a', 'stroke-width': 1}));
        } else {
            [26, 48, 70].forEach(function (y) {
                s.appendChild(svg('circle', {cx: 24, cy: y, r: 9, fill: '#16203a'}));
                s.appendChild(svg('circle', {cx: 24, cy: y, r: 3.5, fill: '#00a4ff'}));
            });
            s.appendChild(svg('circle', {cx: 42, cy: 26, r: 3, fill: '#fff', stroke: '#16203a', 'stroke-width': 1}));
        }

        if (coverage > 0) {
            var g = svg('g', {'clip-path': 'url(#' + clipId + ')', stroke: side === 'front' ? '#16203a' : '#b3261e', 'stroke-width': 1.4, 'stroke-linecap': 'round', opacity: 0.75});
            var rand = rng(side === 'front' ? 7 : 13);
            var count = Math.round(coverage * 0.9);
            var areaH = (H - 8) * coverage / 100;
            for (var i = 0; i < count; i++) {
                var x = 8 + rand() * (W - 16);
                var y = H - 4 - rand() * areaH;
                var len = 8 + rand() * 18, ang = (rand() - 0.5) * 1.6;
                g.appendChild(svg('line', {x1: x, y1: y, x2: x + Math.cos(ang) * len, y2: y + Math.sin(ang) * len}));
            }
            s.appendChild(g);
        }
        return s;
    }

    function renderScratchVisual(coverage) {
        var brand = (phoneById[state.phoneId] || {}).brand || state.brand;
        var fig = el('figure', {class: 'ti-scratch'});
        var pair = el('div', {class: 'ti-scratch-pair'});
        ['front', 'back'].forEach(function (side, i) {
            var cell = el('div', {class: 'ti-scratch-cell'});
            cell.appendChild(phoneSvg(brand, side, coverage, i));
            cell.appendChild(el('span', {text: side === 'front' ? 'Front' : 'Back'}));
            pair.appendChild(cell);
        });
        fig.appendChild(pair);
        fig.appendChild(el('figcaption', {text: coverage ? 'About ' + coverage + '% of the device is scratched.' : 'Choose the closest match to your phone.'}));
        return fig;
    }

    /* ---------- Render ---------- */

    function render() {
        var seq = sequence();
        if (seq.indexOf(state.current) === -1) state.current = 'phone';
        var idx = seq.indexOf(state.current);
        var total = seq.length - 1; // result is not a question
        var step = stepById[state.current];

        stage.textContent = '';
        if (state.current === 'phone') stage.appendChild(renderPhone());
        else if (state.current === 'result') stage.appendChild(renderResult());
        else stage.appendChild(renderQuestion(step));

        // Progress
        var done = state.current === 'result' ? total : idx;
        els.bar.style.width = Math.round(done / total * 100) + '%';
        els.barText.textContent = state.current === 'result' ? 'All questions answered' : 'Question ' + (idx + 1) + ' of ' + total;

        // Back / Continue
        els.back.hidden = idx === 0;
        var showNext = false, nextLabel = 'Continue', nextAr = 'التالي';
        if (state.current === 'phone') {
            showNext = !!state.phoneId;
        } else if (step) {
            var o = optionOf(step, state.answers[step.id]);
            if (o && !o.reject) {
                if (o.confirm) { showNext = !!state.confirmed[step.id]; nextLabel = 'Confirm'; nextAr = 'تأكيد'; }
                else showNext = true;
            }
        }
        els.next.hidden = !showNext || state.current === 'result';
        els.next.textContent = nextLabel + ' ';
        els.next.appendChild(el('span', {lang: 'ar', text: nextAr}));

        renderSide();
    }

    function renderSide() {
        var r = calc();
        els.sidePhone.textContent = r.phone ? r.phone.name + (r.phone.storage ? ', ' + r.phone.storage : '') : 'No phone chosen yet';
        els.sideOffer.hidden = !r.phone;
        els.mBar.hidden = !r.phone || state.current === 'result';
        if (r.phone) {
            els.sideAmount.textContent = fmt(r.high);
            els.mBarAmount.textContent = fmt(r.high);
        }
        var seq = sequence();
        els.mBarStep.textContent = state.current === 'result' ? '' : (seq.indexOf(state.current) + 1) + '/' + (seq.length - 1);

        els.answers.textContent = '';
        data.steps.forEach(function (s) {
            if (!isAsked(s) || state.answers[s.id] === undefined) return;
            var o = optionOf(s, state.answers[s.id]);
            if (!o) return;
            var li = el('li', {class: o.reject ? 'is-stop' : (o.deduct ? 'is-minus' : '')}, [
                el('button', {type: 'button', class: 'ti-answer', title: 'Change this answer', onclick: function () { go(s.id); }}, [
                    el('span', {class: 'ti-answer-q', text: name(s)}),
                    el('span', {class: 'ti-answer-a', text: o.label + (o.deduct ? '  ' + pct(o.deduct) : '')})
                ])
            ]);
            els.answers.appendChild(li);
        });
    }

    els.next.addEventListener('click', goNext);
    els.back.addEventListener('click', goBack);

    render();

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
