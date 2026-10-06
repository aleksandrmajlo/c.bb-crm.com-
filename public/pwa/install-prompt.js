(function () {
    'use strict';

    var deferredInstallPrompt = null;
    var banner = null;
    var installButton = null;
    var iosHelp = null;
    var description = null;
    var dismissalKey = null;
    var dismissalPeriod = 7 * 24 * 60 * 60 * 1000;

    function isMobileDevice() {
        var userAgentMobile = navigator.userAgentData && navigator.userAgentData.mobile;
        var touchViewport = navigator.maxTouchPoints > 0
            && window.matchMedia('(max-width: 900px)').matches;

        return Boolean(userAgentMobile || touchViewport || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent));
    }

    function isIosDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function wasRecentlyDismissed() {
        try {
            var dismissedAt = Number(window.localStorage.getItem(dismissalKey));
            return dismissedAt > 0 && Date.now() - dismissedAt < dismissalPeriod;
        } catch (error) {
            return false;
        }
    }

    function rememberDismissal() {
        try {
            window.localStorage.setItem(dismissalKey, String(Date.now()));
        } catch (error) {
            // The banner can still be closed when storage is unavailable.
        }
    }

    function showBanner() {
        if (!banner || !isMobileDevice() || isStandalone() || wasRecentlyDismissed()) {
            return;
        }

        banner.hidden = false;
        banner.classList.add('is-visible');
    }

    function hideBanner(remember) {
        if (!banner) {
            return;
        }

        banner.classList.remove('is-visible');
        banner.hidden = true;

        if (remember) {
            rememberDismissal();
        }
    }

    function showIosInstructions() {
        if (description) {
            description.hidden = true;
        }
        if (iosHelp) {
            iosHelp.hidden = false;
        }
        if (installButton) {
            installButton.textContent = 'Зрозуміло';
        }
    }

    function handleInstallClick() {
        if (isIosDevice()) {
            if (iosHelp && !iosHelp.hidden) {
                hideBanner(true);
                return;
            }

            showIosInstructions();
            return;
        }

        if (!deferredInstallPrompt) {
            return;
        }

        var promptEvent = deferredInstallPrompt;
        deferredInstallPrompt = null;
        promptEvent.prompt();
        promptEvent.userChoice.then(function (choice) {
            hideBanner(choice.outcome !== 'accepted');
        });
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;
        showBanner();
    });

    window.addEventListener('appinstalled', function () {
        deferredInstallPrompt = null;
        hideBanner(false);
    });

    document.addEventListener('DOMContentLoaded', function () {
        banner = document.getElementById('pwa-install-banner');
        if (!banner) {
            return;
        }

        installButton = banner.querySelector('[data-pwa-install]');
        iosHelp = banner.querySelector('[data-pwa-ios-help]');
        description = banner.querySelector('[data-pwa-description]');
        dismissalKey = 'pwa-install-dismissed:' + banner.dataset.appId;

        banner.querySelector('[data-pwa-dismiss]').addEventListener('click', function () {
            hideBanner(true);
        });
        installButton.addEventListener('click', handleInstallClick);

        if (isIosDevice()) {
            showBanner();
        } else if (deferredInstallPrompt) {
            showBanner();
        }
    });
}());
