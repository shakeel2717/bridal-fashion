import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// ── Global Enter Key Navigation + Auto-select ─────────
window.handleFocusSelect = function(e) {
    const el = e.target;
    setTimeout(() => {
        if (el.tagName.toLowerCase() === 'input' ||
            el.tagName.toLowerCase() === 'textarea') {
            el.select();
        }
    }, 50);
};

window.focusNext = function(current) {
    // Find the closest scrollable container or use document
    const container = current.closest('.modal') || document;

    const focusable = Array.from(
        container.querySelectorAll(
            'input:not([disabled]):not([readonly]):not([type="file"]):not([type="checkbox"]):not([type="radio"]), ' +
            'select:not([disabled]), ' +
            'textarea:not([disabled])'
        )
    ).filter(el => el.offsetParent !== null && !el.closest('[wire\\:loading]'));

    const idx = focusable.indexOf(current);
    if (idx > -1 && idx < focusable.length - 1) {
        const next = focusable[idx + 1];
        next.focus();
        setTimeout(() => {
            if (next.tagName.toLowerCase() === 'input' ||
                next.tagName.toLowerCase() === 'textarea') {
                next.select();
            }
        }, 50);
    }
};

window.setupEnterNav = function(container) {
    const target = container || document;

    // Auto-select all text on focus
    target.querySelectorAll('input:not([type="file"]):not([type="checkbox"]):not([type="radio"]), textarea')
        .forEach(el => {
            el.removeEventListener('focus', window.handleFocusSelect);
            el.addEventListener('focus', window.handleFocusSelect);
        });

    // Enter key moves to next
    if (!target._enterNavBound) {
        target.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            const tag = e.target.tagName.toLowerCase();
            if (tag === 'textarea' || tag === 'button') return;
            if (tag === 'select' || tag === 'input') {
                e.preventDefault();
                window.focusNext(e.target);
            }
        });
        target._enterNavBound = true;
    }
};

// Run on every Livewire update and page load
function initAll() {
    window.setupEnterNav(document);
}

document.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('livewire:initialized', initAll);
document.addEventListener('livewire:navigated', initAll);
document.addEventListener('livewire:updated', initAll);