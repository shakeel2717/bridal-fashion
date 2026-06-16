import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

window.handleFocusSelect = function (e) {
    const el = e.target;
    if (el._skipAutoSelect) {
        el._skipAutoSelect = false;
        return;
    }
    setTimeout(() => {
        if (document.activeElement === el) el.select();
    }, 50);
};

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

window.setupEnterNav = function (container) {
    const target = container || document;

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

            const excludedIds = [
                'po_product_search', 'po_new_qty', 'po_new_price',
                'rental_product_search', 'rental_price_input',
                'sale_product_search', 'sale_new_qty', 'sale_new_price',
            ];
            if (excludedIds.includes(e.target.id)) return;

            if (e.target.closest('[data-rental-input]')) return;

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
    document._enterNavBound = false;
    initAll();
});

document.addEventListener('livewire:updated', () => {
    document.querySelectorAll(
        'input:not([type="file"]):not([type="checkbox"]):not([type="radio"]), textarea'
    ).forEach(el => {
        if (!el._focusSelectBound) {
            el._focusSelectBound = true;
            el.addEventListener('focus', window.handleFocusSelect);
        }
    });
});