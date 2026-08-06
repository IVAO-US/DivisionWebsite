/**
 * Tabs Accessibility Shim - MaryUI >= 2.9.9
 *
 * MaryUI 2.9.9 rewrote <x-tab> around x-teleport and dropped the hidden
 * <input type="radio"> that every tab label used to wrap, replacing it with a
 * bare @click handler. The rendered labels therefore carry no tabindex, no
 * role and no aria-selected, and hold no focusable element at all: the tabs
 * became mouse-only - unreachable by keyboard and opaque to screen readers.
 * MaryUI <= 2.9.8 is NOT affected and is left strictly untouched.
 *
 * This module re-layers the WAI-ARIA tabs pattern on top of the rendered
 * markup, without patching vendor components, Blade views or CSS:
 * - role=tablist / role=tab / role=tabpanel, plus the id wiring between them,
 * - aria-selected and a roving tabindex derived from the `tab-active` class
 *   MaryUI already toggles (the selection state is never duplicated here),
 * - ArrowLeft / ArrowRight / Home / End / Enter / Space handling.
 *
 * Activation always goes through label.click(), so MaryUI's own Alpine handler
 * remains the single owner of the selection. No class and no visual output is
 * changed - daisyUI's `.tab:focus-visible` outline appears on its own as soon
 * as a label becomes focusable.
 */

// Marks a container whose listeners and observers are already attached.
const CONTAINER_FLAG = 'data-tabs-a11y';

// Marks the labels and panels enhanced by this module.
const TAB_FLAG = 'data-tabs-a11y-tab';
const PANEL_FLAG = 'data-tabs-a11y-panel';

// Keys handled on the tablist. preventDefault() is called for these only.
const NAV_KEYS = ['ArrowLeft', 'ArrowRight', 'Home', 'End', 'Enter', ' ', 'Spacebar'];

// Matches the tab name inside an Alpine expression: `selected = 'tours'` on a
// label (@click) and `selected == 'tours'` on a panel (x-show).
const NAME_PATTERN = /selected\s*={1,3}\s*(['"])([\s\S]*?)\1/;

let sequence = 0;

/**
 * Read the tab name a label assigns, from any @click / x-on:click attribute.
 * Alpine leaves those attributes in the DOM, so they can be parsed back.
 */
function labelName(label) {
    for (const attribute of label.attributes) {
        if (!attribute.name.startsWith('@click') && !attribute.name.startsWith('x-on:click')) {
            continue;
        }

        const match = attribute.value.match(NAME_PATTERN);

        if (match) {
            return match[2];
        }
    }

    return null;
}

/**
 * Read the tab name a panel is bound to, from its x-show expression.
 */
function panelName(panel) {
    const match = (panel.getAttribute('x-show') || '').match(NAME_PATTERN);

    return match ? match[2] : null;
}

/**
 * True when a label is already keyboard-operable on its own. MaryUI <= 2.9.8
 * wrapped every label in a hidden radio input, and a future upstream fix may
 * ship its own roles - in both cases this module must stay out of the way.
 */
function isOperable(label) {
    return label.hasAttribute('tabindex')
        || label.getAttribute('role') === 'tab'
        || label.querySelector('input, button, a, [tabindex]') !== null;
}

/**
 * Turn an arbitrary tab name into an id-safe fragment.
 */
function idSafe(value) {
    return value.replace(/[^A-Za-z0-9_-]/g, '-');
}

/**
 * Return the element id, generating a stable one when it has none.
 */
function ensureId(element, prefix) {
    if (!element.id) {
        element.id = `${prefix}-${++sequence}`;
    }

    return element.id;
}

/**
 * Collect the panels belonging to a labels container. Panels live outside of
 * it - MaryUI teleports the labels into `#<uuid>-labels` while the panels stay
 * in the sibling "original data" wrapper - so the search starts at the parent.
 * Nested <x-tabs> render their own [x-data] root, which is used to discard
 * panels owned by an inner group; the full list is kept as a fallback should
 * that markup ever change upstream.
 */
function panelsFor(container) {
    const scope = container.parentElement;

    if (!scope) {
        return [];
    }

    const root = container.closest('[x-data]');
    const panels = Array.from(scope.querySelectorAll('.tab-content'));
    const owned = root ? panels.filter((panel) => panel.closest('[x-data]') === root) : [];

    return owned.length > 0 ? owned : panels;
}

/**
 * The tabs reachable by keyboard: disabled and hidden ones are skipped.
 */
function navigableTabs(container) {
    return Array.from(container.querySelectorAll('label.tab[role="tab"]')).filter(
        (tab) => !tab.classList.contains('tab-disabled') && tab.offsetParent !== null
    );
}

/**
 * Mirror MaryUI's `tab-active` class into aria-selected and a roving tabindex.
 * The class is the single source of truth - nothing here decides the selection.
 */
function syncState(container) {
    const tabs = Array.from(container.querySelectorAll('label.tab[role="tab"]'));

    if (tabs.length === 0) {
        return;
    }

    let hasActive = false;

    tabs.forEach((tab) => {
        const active = tab.classList.contains('tab-active');

        if (active) {
            hasActive = true;
        }

        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        tab.setAttribute('tabindex', active ? '0' : '-1');
    });

    // Keep the tablist reachable with a single Tab stop even before Alpine has
    // applied `tab-active`, or when the bound value matches no tab at all.
    if (!hasActive) {
        const first = navigableTabs(container)[0];

        if (first) {
            first.setAttribute('tabindex', '0');
        }
    }
}

/**
 * Activate a tab through a real click, so MaryUI's Alpine @click handler stays
 * the only writer of `selected`; focus then follows the activated tab.
 */
function activate(tab) {
    if (!tab) {
        return;
    }

    tab.click();
    tab.focus();
}

/**
 * Keyboard navigation, delegated from the tablist container.
 */
function onKeydown(event) {
    if (!NAV_KEYS.includes(event.key)) {
        return;
    }

    const container = event.currentTarget;
    const target = event.target instanceof Element ? event.target : null;
    const current = target ? target.closest('label.tab[role="tab"]') : null;

    if (!current || !container.contains(current)) {
        return;
    }

    const tabs = navigableTabs(container);
    const index = tabs.indexOf(current);

    if (index === -1) {
        return;
    }

    event.preventDefault();

    if (event.key === 'ArrowRight') {
        activate(tabs[(index + 1) % tabs.length]);
    } else if (event.key === 'ArrowLeft') {
        activate(tabs[(index - 1 + tabs.length) % tabs.length]);
    } else if (event.key === 'Home') {
        activate(tabs[0]);
    } else if (event.key === 'End') {
        activate(tabs[tabs.length - 1]);
    } else {
        activate(current);
    }
}

/**
 * Apply the ARIA wiring to one labels container. Labels and panels are paired
 * by NAME, never by index, so a hidden or reordered tab cannot desynchronise
 * the mapping. Returns true when the container is one this module owns.
 */
function enhance(container) {
    const labels = Array.from(container.querySelectorAll('label.tab'));

    if (labels.length === 0) {
        return false;
    }

    // Neutrality: leave already-operable tabs alone. Labels enhanced here are
    // not counted, otherwise the module would opt out of its own work.
    const alreadyOperable = labels.some(
        (label) => !label.hasAttribute(TAB_FLAG) && isOperable(label)
    );

    if (alreadyOperable) {
        return false;
    }

    const named = labels
        .map((label) => ({ label, name: labelName(label) }))
        .filter((tab) => tab.name !== null);

    if (named.length === 0) {
        return false;
    }

    const panelByName = new Map();

    panelsFor(container).forEach((panel) => {
        const name = panelName(panel);

        if (name !== null && !panelByName.has(name)) {
            panelByName.set(name, panel);
        }
    });

    const containerId = ensureId(container, 'tabs-a11y');

    container.setAttribute('role', 'tablist');
    container.setAttribute('aria-orientation', 'horizontal');

    named.forEach(({ label, name }) => {
        const labelId = label.id || `${containerId}-tab-${idSafe(name)}`;

        label.id = labelId;
        label.setAttribute('role', 'tab');
        label.setAttribute(TAB_FLAG, '');

        if (label.classList.contains('tab-disabled')) {
            label.setAttribute('aria-disabled', 'true');
        } else {
            label.removeAttribute('aria-disabled');
        }

        const panel = panelByName.get(name);

        if (panel) {
            const panelId = panel.id || `${containerId}-panel-${idSafe(name)}`;

            panel.id = panelId;
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('aria-labelledby', labelId);
            panel.setAttribute(PANEL_FLAG, '');
            label.setAttribute('aria-controls', panelId);
        }
    });

    syncState(container);

    return true;
}

/**
 * Scan the document and enhance every tabs container found. Attribute writes
 * are idempotent, so this is safe to run on every DOM change; listeners and
 * observers are attached once per container.
 */
function enhanceAll() {
    document.querySelectorAll('.tabs').forEach((container) => {
        if (!enhance(container)) {
            return;
        }

        if (container.hasAttribute(CONTAINER_FLAG)) {
            return;
        }

        container.setAttribute(CONTAINER_FLAG, '');
        container.addEventListener('keydown', onKeydown);

        // MaryUI toggles `tab-active` on the labels; mirror it back into ARIA.
        // Only `class` is watched, and this module never writes classes, so
        // the observer cannot re-trigger itself.
        new MutationObserver(() => syncState(container)).observe(container, {
            attributes: true,
            attributeFilter: ['class'],
            subtree: true,
        });
    });
}

let scheduled = false;

/**
 * Coalesce bursts of DOM mutations into a single pass per animation frame.
 */
function schedule() {
    if (scheduled) {
        return;
    }

    scheduled = true;

    requestAnimationFrame(() => {
        scheduled = false;
        enhanceAll();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', schedule);
} else {
    schedule();
}

// The labels are teleported after Alpine boots, and Livewire can re-render
// both labels and panels, so the pass has to run again on SPA navigation and
// on any DOM insertion. Only childList is observed at this level: watching
// attributes here would loop on this module's own ARIA writes.
document.addEventListener('livewire:navigated', schedule);

new MutationObserver(schedule).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
