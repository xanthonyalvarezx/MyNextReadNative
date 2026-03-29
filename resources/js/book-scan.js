import { BrowserMultiFormatReader } from '@zxing/browser';

const root = document.querySelector('[data-book-scan]');

if (root) {
    const baseUrl = root.dataset.searchUrl ?? '/search';
    const openBtn = root.querySelector('[data-book-scan-open]');
    const dialog = root.querySelector('[data-book-scan-dialog]');
    const video = root.querySelector('[data-book-scan-video]');
    const closeBtn = root.querySelector('[data-book-scan-cancel]');
    const statusEl = root.querySelector('[data-book-scan-status]');

    let activeControls = null;

    function stopScan() {
        activeControls?.stop();
        activeControls = null;
        if (video instanceof HTMLVideoElement) {
            video.srcObject = null;
        }
    }

    function pickDeviceId(devices) {
        if (!devices.length) {
            return undefined;
        }
        const back = devices.find((d) => /back|rear|environment|wide/i.test(d.label));
        return back?.deviceId ?? devices[0].deviceId;
    }

    dialog?.addEventListener('close', () => stopScan());

    closeBtn?.addEventListener('click', () => {
        stopScan();
        dialog?.close();
    });

    openBtn?.addEventListener('click', async () => {
        if (!(dialog instanceof HTMLDialogElement) || !(video instanceof HTMLVideoElement)) {
            return;
        }

        stopScan();
        if (statusEl) {
            statusEl.textContent = 'Point the camera at the ISBN barcode on the book.';
        }
        dialog.showModal();

        try {
            const reader = new BrowserMultiFormatReader();
            const devices = await BrowserMultiFormatReader.listVideoInputDevices();
            if (!devices.length) {
                if (statusEl) {
                    statusEl.textContent = 'No camera found on this device.';
                }
                return;
            }

            const deviceId = pickDeviceId(devices);

            activeControls = await reader.decodeFromVideoDevice(deviceId, video, (result, _err, controls) => {
                if (!result) {
                    return;
                }
                const text = result.getText().trim();
                controls.stop();
                activeControls = null;
                dialog.close();
                const url = new URL(baseUrl, document.baseURI);
                url.searchParams.set('q', text);
                window.location.assign(url.href);
            });
        } catch {
            if (statusEl) {
                statusEl.textContent =
                    'Could not use the camera. Allow camera access in your browser or app settings, then try again.';
            }
        }
    });

    /* Keyboard-wedge USB / Bluetooth scanners: type digits (+ optional hyphens) then Enter or Tab */
    const form = root.querySelector('form.search-page__form');
    const queryInput = form?.querySelector('input[name="q"]');

    if (queryInput instanceof HTMLInputElement && form) {
        const GAP_MS = 90;
        let wedgeBuffer = '';
        let lastKeyTs = 0;
        let gapClearTimer = null;

        function clearWedgeBuffer() {
            wedgeBuffer = '';
        }

        function normalizeWedge(raw) {
            return raw.toUpperCase().replace(/[^0-9X]/g, '');
        }

        /** @returns {string | null} */
        function finalizeWedgeBuffer(raw) {
            const c = normalizeWedge(raw);
            if (/^(97[89]\d{10}|\d{9}[\dX])$/.test(c)) {
                return c;
            }
            if (/^\d{13}$/.test(c) || /^\d{8}$/.test(c)) {
                return c;
            }
            return null;
        }

        function isOpenScanDialog() {
            return dialog instanceof HTMLDialogElement && dialog.open;
        }

        document.addEventListener(
            'keydown',
            (e) => {
                if (isOpenScanDialog()) {
                    return;
                }

                if (e.target === queryInput) {
                    return;
                }

                if (
                    e.target instanceof HTMLInputElement ||
                    e.target instanceof HTMLTextAreaElement ||
                    e.target instanceof HTMLSelectElement
                ) {
                    return;
                }

                const now = Date.now();
                if (now - lastKeyTs > GAP_MS) {
                    wedgeBuffer = '';
                }
                lastKeyTs = now;
                if (gapClearTimer) {
                    clearTimeout(gapClearTimer);
                }
                gapClearTimer = setTimeout(clearWedgeBuffer, GAP_MS * 3);

                if (e.key === 'Enter' || e.key === 'Tab') {
                    const hadContent = wedgeBuffer.length > 0;
                    const code = finalizeWedgeBuffer(wedgeBuffer);
                    wedgeBuffer = '';
                    if (code) {
                        e.preventDefault();
                        queryInput.value = code;
                        form.requestSubmit();
                    } else if (hadContent) {
                        e.preventDefault();
                    }
                    return;
                }

                if (e.key.length === 1 && /[0-9Xx\-]/.test(e.key)) {
                    e.preventDefault();
                    wedgeBuffer += e.key;
                    queryInput.focus();
                    queryInput.value = normalizeWedge(wedgeBuffer);
                    return;
                }

                if (e.key.length === 1) {
                    wedgeBuffer = '';
                }
            },
            true,
        );
    }
}
