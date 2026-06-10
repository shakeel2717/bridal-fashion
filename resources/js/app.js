import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// ── Auto-select on focus (click or tab only, not programmatic) ─
window.handleFocusSelect = function (e) {
    const el = e.target;
    // Only select if focus came from user interaction, not programmatic
    if (el._skipAutoSelect) {
        el._skipAutoSelect = false;
        return;
    }
    setTimeout(() => {
        if (document.activeElement === el) {
            el.select();
        }
    }, 50);
};

// ── Focus next input ───────────────────────────────────
window.focusNext = function (current) {
    const container = current.closest('.modal') || document;

    const focusable = Array.from(
        container.querySelectorAll(
            'input:not([disabled]):not([readonly]):not([type="file"]):not([type="checkbox"]):not([type="radio"]), ' +
            'select:not([disabled]), textarea:not([disabled])'
        )
    ).filter(el => el.offsetParent !== null);

    const idx = focusable.indexOf(current);
    if (idx > -1 && idx < focusable.length - 1) {
        const next = focusable[idx + 1];
        next._skipAutoSelect = true;
        next.focus();
        setTimeout(() => {
            if (document.activeElement === next &&
                (next.tagName.toLowerCase() === 'input' ||
                    next.tagName.toLowerCase() === 'textarea')) {
                next.select();
            }
        }, 50);
    }
};

// ── Global Enter nav ───────────────────────────────────
window.setupEnterNav = function (container) {
    const target = container || document;

    // Attach focus-select only to inputs not already bound
    target.querySelectorAll(
        'input:not([type="file"]):not([type="checkbox"]):not([type="radio"]), textarea'
    ).forEach(el => {
        if (!el._focusSelectBound) {
            el._focusSelectBound = true;
            el.addEventListener('focus', window.handleFocusSelect);
        }
    });

    if (!target._enterNavBound) {
        target._enterNavBound = true;
        target.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            const tag = e.target.tagName.toLowerCase();
            if (tag === 'textarea' || tag === 'button') return;

            // Skip PO row inputs — handled in blade script
            const poIds = ['po_product_search', 'po_new_qty', 'po_new_price'];
            if (poIds.includes(e.target.id)) return;

            if (tag === 'select' || tag === 'input') {
                e.preventDefault();
                window.focusNext(e.target);
            }
        });
    }
};

function initAll() {
    window.setupEnterNav(document);
}

document.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('livewire:initialized', initAll);
document.addEventListener('livewire:navigated', () => {
    // Reset bound flags on navigation
    document._enterNavBound = false;
    initAll();
});

// On livewire:updated only bind NEW inputs, don't rebind everything
document.addEventListener('livewire:updated', () => {
    document.querySelectorAll(
        'input:not([type="file"]):not([type="checkbox"]):not([type="radio"]):not([data-focus-bound]), textarea:not([data-focus-bound])'
    ).forEach(el => {
        if (!el._focusSelectBound) {
            el._focusSelectBound = true;
            el.addEventListener('focus', window.handleFocusSelect);
        }
    });
});