// app.js
import '../css/app.css'
import './bootstrap'

// Import Preline
import 'preline'
import Swal from 'sweetalert2'

window.Swal = Swal

// ---- Collapse Polyfill ----
function collapsePolyfill() {
    document.querySelectorAll('[data-hs-collapse]').forEach(btn => {
        if (btn.__inited) return;
        btn.__inited = true;

        const selector = btn.dataset.hsCollapse;
        const target = document.querySelector(selector);
        if (!target) return;

        btn.addEventListener('click', () => {
            const isHidden = target.classList.contains('hidden');

            if (isHidden) {
                target.classList.remove('hidden');
                target.style.height = target.scrollHeight + 'px';
                setTimeout(() => target.style.height = '', 300);
            } else {
                target.style.height = target.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    target.style.height = '0px';
                });
                setTimeout(() => {
                    target.classList.add('hidden');
                    target.style.height = '';
                }, 300);
            }

            btn.setAttribute(
                'aria-expanded',
                isHidden ? 'true' : 'false'
            );
        });
    });
}

// ---- Preline Init ----
function initPreline() {
    try {
        if (window.HSStaticMethods && HSStaticMethods.autoInit) {
            HSStaticMethods.autoInit();
            return;
        }

        if (window.HSCollapse && window.HSCollapse.autoInit) {
            HSCollapse.autoInit();
            return;
        }

        // fallback
        collapsePolyfill();
    } catch {
        collapsePolyfill();
    }
}

// ---- Init on Load & After Livewire Navigation ----
document.addEventListener('DOMContentLoaded', initPreline);
document.addEventListener('livewire:navigated', initPreline);
