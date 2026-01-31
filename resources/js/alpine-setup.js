import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { computePosition, offset, flip, shift, arrow } from '@floating-ui/dom';
import moment from 'moment';
import 'animate.css';
import axios from 'axios';

// Register Alpine plugins
Alpine.plugin(collapse);

// Make Alpine available globally
window.Alpine = Alpine;
window.moment = moment;
window.axios = axios;

// Configure axios defaults
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Alpine.js Tooltip directive using Floating UI
Alpine.directive('tooltip', (el, { expression, modifiers }, { evaluate, cleanup }) => {
    const text = expression ? evaluate(expression) : el.getAttribute('title') || el.getAttribute('data-tooltip');
    if (!text) return;

    let tooltipEl = null;
    let arrowEl = null;

    const placement = modifiers.includes('top') ? 'top' :
                      modifiers.includes('bottom') ? 'bottom' :
                      modifiers.includes('left') ? 'left' :
                      modifiers.includes('right') ? 'right' : 'top';

    const showTooltip = () => {
        tooltipEl = document.createElement('div');
        tooltipEl.className = 'fixed z-[99999] px-2 py-1 text-sm text-white bg-gray-900 dark:bg-gray-700 rounded shadow-lg pointer-events-none';
        tooltipEl.innerHTML = text;

        arrowEl = document.createElement('div');
        arrowEl.className = 'absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45';
        tooltipEl.appendChild(arrowEl);

        document.body.appendChild(tooltipEl);

        computePosition(el, tooltipEl, {
            placement: placement,
            middleware: [
                offset(10),
                flip(),
                shift({ padding: 5 }),
                arrow({ element: arrowEl })
            ]
        }).then(({ x, y, placement, middlewareData }) => {
            Object.assign(tooltipEl.style, {
                left: `${x}px`,
                top: `${y}px`,
            });

            const { x: arrowX, y: arrowY } = middlewareData.arrow;
            const staticSide = {
                top: 'bottom',
                right: 'left',
                bottom: 'top',
                left: 'right',
            }[placement.split('-')[0]];

            Object.assign(arrowEl.style, {
                left: arrowX != null ? `${arrowX}px` : '',
                top: arrowY != null ? `${arrowY}px` : '',
                right: '',
                bottom: '',
                [staticSide]: '-4px',
            });
        });
    };

    const hideTooltip = () => {
        if (tooltipEl) {
            tooltipEl.remove();
            tooltipEl = null;
        }
    };

    el.addEventListener('mouseenter', showTooltip);
    el.addEventListener('mouseleave', hideTooltip);
    el.addEventListener('focus', showTooltip);
    el.addEventListener('blur', hideTooltip);

    cleanup(() => {
        el.removeEventListener('mouseenter', showTooltip);
        el.removeEventListener('mouseleave', hideTooltip);
        el.removeEventListener('focus', showTooltip);
        el.removeEventListener('blur', hideTooltip);
        hideTooltip();
    });
});

// Alpine.js Popover component
Alpine.data('popover', (config = {}) => ({
    open: false,
    placement: config.placement || 'bottom',
    trigger: config.trigger || 'click',

    init() {
        if (this.trigger === 'hover') {
            this.$refs.trigger?.addEventListener('mouseenter', () => this.show());
            this.$refs.trigger?.addEventListener('mouseleave', () => this.hide());
        }
    },

    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => this.position());
        }
    },

    show() {
        this.open = true;
        this.$nextTick(() => this.position());
    },

    hide() {
        this.open = false;
    },

    position() {
        const trigger = this.$refs.trigger;
        const content = this.$refs.content;
        if (!trigger || !content) return;

        computePosition(trigger, content, {
            placement: this.placement,
            middleware: [offset(8), flip(), shift({ padding: 5 })]
        }).then(({ x, y }) => {
            Object.assign(content.style, {
                left: `${x}px`,
                top: `${y}px`,
            });
        });
    }
}));

// Alpine.js Modal component
Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,

    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },

    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },

    toggle() {
        this.open ? this.hide() : this.show();
    }
}));

// Alpine.js Dropdown component
Alpine.data('dropdown', () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    }
}));

// Alpine.js Collapse component
Alpine.data('collapse', (initialOpen = false) => ({
    open: initialOpen,

    toggle() {
        this.open = !this.open;
    }
}));

// Sidebar toggle store
Alpine.store('sidebar', {
    active: false,

    toggle() {
        this.active = !this.active;
    },

    init() {
        // Check screen size and set initial state
        if (window.innerWidth >= 992) {
            this.active = true;
        }
    }
});

// Start Alpine
Alpine.start();

// Export for use in other files
export { Alpine, computePosition, offset, flip, shift, arrow };
