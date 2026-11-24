(function () {
    'use strict';

    let progress_navigation_frame = null;
    let navigation_pending = false;
    let resuming_form = null;
    let resumed_submission = null;

    /**
     * Shows or hides the shared overlay with the caption for the pending operation.
     *
     * @param {boolean} state
     * Whether to show the overlay.
     *
     * @param {string} caption
     * The loading or saving message to display.
     *
     * @returns {void}
     * Does not return a value.
     *
     * @author Oleg Schildt
     */
    function show_sys_progress_indicator(state, caption = 'Loading data ...') {
        const overlay = document.getElementById('sys_progress_indicator');

        if (!overlay) {
            return;
        }

        overlay.querySelector('.progress_caption').textContent = caption;
        overlay.style.display = state ? 'table' : 'none';

        overlay.setAttribute('aria-hidden', String(!state));
    } // show_sys_progress_indicator

    /**
     * Cancels pending animation callbacks and restores a page returned from browser history.
     *
     * @returns {void}
     * Does not return a value.
     *
     * @author Oleg Schildt
     */
    function reset_progress_indicator() {
        if (progress_navigation_frame !== null) {
            window.cancelAnimationFrame(progress_navigation_frame);

            progress_navigation_frame = null;
        }

        navigation_pending = false;

        show_sys_progress_indicator(false);
    } // reset_progress_indicator

    /**
     * Waits for two animation frames so the overlay can paint before navigation or submission.
     *
     * @param {function(): void} action
     * The navigation or form submission to perform once.
     *
     * @returns {void}
     * Does not return a value.
     *
     * @throws {Error}
     * If the deferred action fails, hides the overlay and propagates the error.
     *
     * @author Oleg Schildt
     */
    function after_progress_paint(action) {
        progress_navigation_frame = window.requestAnimationFrame(function () {
            progress_navigation_frame = window.requestAnimationFrame(function () {
                progress_navigation_frame = null;

                try {
                    action();
                } catch (error) {
                    reset_progress_indicator();

                    throw error;
                }
            });
        });
    } // after_progress_paint

    document.addEventListener('click', function (event) {
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

        if (
            !link ||
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey ||
            (link.target && link.target.toLowerCase() !== '_self') ||
            link.hasAttribute('download')
        ) {
            return;
        }

        const destination = new URL(link.href, document.baseURI);
        const current = new URL(window.location.href);

        // Fragment links and non-HTTP protocols do not replace the current page.
        if (
            !['http:', 'https:'].includes(destination.protocol) ||
            link.getAttribute('href').startsWith('#') ||
            (destination.origin === current.origin &&
                destination.pathname === current.pathname &&
                destination.search === current.search &&
                destination.hash)
        ) {
            return;
        }

        event.preventDefault();

        if (navigation_pending) {
            return;
        }

        navigation_pending = true;

        show_sys_progress_indicator(true);

        after_progress_paint(function () {
            window.location.assign(destination.href);
        });
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        const submitter = event.submitter;

        if (resuming_form === form) {
            resumed_submission = event;

            return;
        }

        const target = submitter?.getAttribute('formtarget') ?? form.target;
        const method = submitter?.getAttribute('formmethod') ?? form.method;

        // The JSON and XML API forms open their responses in a separate window.
        if (
            event.defaultPrevented ||
            (target && target.toLowerCase() !== '_self') ||
            method.toLowerCase() === 'dialog'
        ) {
            return;
        }

        event.preventDefault();

        if (navigation_pending) {
            return;
        }

        navigation_pending = true;

        show_sys_progress_indicator(true, form.dataset.progressMessage || 'Loading data ...');

        after_progress_paint(function () {
            resuming_form = form;
            resumed_submission = null;

            try {
                // Repeat native submission to preserve validation and the clicked button's name and value.
                HTMLFormElement.prototype.requestSubmit.call(form, submitter || undefined);

                if (!resumed_submission || resumed_submission.defaultPrevented) {
                    reset_progress_indicator();
                }
            } finally {
                resuming_form = null;
                resumed_submission = null;
            }
        });
    });

    window.addEventListener('pageshow', reset_progress_indicator);

    window.addEventListener('pagehide', reset_progress_indicator);
})();
