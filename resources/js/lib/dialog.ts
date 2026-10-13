import type { ObjectDirective } from 'vue';

interface DialogState {
    opener: HTMLElement | null;
    overflow: string;
    onKey: (event: KeyboardEvent) => void;
    onFocus: (event: FocusEvent) => void;
}

const states = new WeakMap<HTMLElement, DialogState>();
const stack: HTMLElement[] = [];
const focusable = (el: HTMLElement) =>
    Array.from(
        el.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    ).filter(
        (item) =>
            !item.closest('[hidden], [inert]') &&
            item.getClientRects().length > 0,
    );

// Scope the trap to the topmost dialog; nested dialogs restore their own opener.
export const dialog: ObjectDirective<HTMLElement, string | undefined> = {
    mounted(el, binding) {
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-modal', 'true');
        el.setAttribute('aria-label', binding.value || 'Dialog');
        el.setAttribute('tabindex', '-1');
        const state: DialogState = {
            opener: document.activeElement as HTMLElement | null,
            overflow: document.body.style.overflow,
            onKey(event) {
                if (stack.at(-1) !== el || event.key !== 'Tab') return;
                const items = focusable(el);
                const first = items[0] ?? el;
                const last = items.at(-1) ?? el;
                if (
                    !items.length ||
                    !el.contains(document.activeElement) ||
                    (event.shiftKey && document.activeElement === first) ||
                    (!event.shiftKey && document.activeElement === last)
                ) {
                    event.preventDefault();
                    (event.shiftKey ? last : first).focus();
                }
            },
            onFocus(event) {
                if (stack.at(-1) === el && !el.contains(event.target as Node))
                    (focusable(el)[0] ?? el).focus();
            },
        };
        states.set(el, state);
        stack.push(el);
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', state.onKey);
        document.addEventListener('focusin', state.onFocus);
        queueMicrotask(() => {
            if (stack.at(-1) === el) (focusable(el)[0] ?? el).focus();
        });
    },
    updated(el, binding) {
        el.setAttribute('aria-label', binding.value || 'Dialog');
    },
    unmounted(el) {
        const state = states.get(el);
        if (!state) return;
        document.removeEventListener('keydown', state.onKey);
        document.removeEventListener('focusin', state.onFocus);
        const index = stack.indexOf(el);
        if (index >= 0) stack.splice(index, 1);
        // Preserve the original overflow when dialogs close out of order.
        const next = stack[index];
        if (next) {
            const nextState = states.get(next);
            if (nextState) nextState.overflow = state.overflow;
        }
        if (!stack.length) document.body.style.overflow = state.overflow;
        states.delete(el);
        if (state.opener?.isConnected) state.opener.focus();
    },
};
