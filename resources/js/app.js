import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// ── Product Form Enter Key Navigation + Auto-select ───
document.addEventListener('livewire:navigated', () => setupEnterNav());
document.addEventListener('livewire:initialized', () => setupEnterNav());
document.addEventListener('DOMContentLoaded', () => setupEnterNav());
document.addEventListener('livewire:updated', () => setupEnterNav());

function handleFocusSelect(e) {
    const el = e.target;
    setTimeout(() => {
        if (el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
            el.select();
        }
    }, 50);
}

function focusNext(current) {
    const modal = document.getElementById('productModal');
    if (!modal) return;

    const focusable = Array.from(
        modal.querySelectorAll(
            'input:not([disabled]):not([readonly]):not([type="file"]):not([type="checkbox"]), select:not([disabled]), textarea:not([disabled])'
        )
    ).filter(el => el.offsetParent !== null);

    const idx = focusable.indexOf(current);
    if (idx > -1 && idx < focusable.length - 1) {
        const next = focusable[idx + 1];
        next.focus();
        if (next.tagName.toLowerCase() === 'input' || next.tagName.toLowerCase() === 'textarea') {
            setTimeout(() => next.select(), 50);
        }
    }
}

let enterNavInitialized = false;

function setupEnterNav() {
    const modal = document.getElementById('productModal');
    if (!modal) return;

    // Auto-select on focus
    modal.querySelectorAll('input, textarea').forEach(el => {
        el.removeEventListener('focus', handleFocusSelect);
        el.addEventListener('focus', handleFocusSelect);
    });

    // Only attach keydown once
    if (!enterNavInitialized) {
        modal.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            const tag = e.target.tagName.toLowerCase();
            if (tag === 'textarea' || tag === 'button') return;
            if (tag === 'select' || tag === 'input') {
                e.preventDefault();
                focusNext(e.target);
            }
        });
        enterNavInitialized = true;
    }
}