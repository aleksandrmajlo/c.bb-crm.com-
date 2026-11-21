(function () {
    'use strict';

    var button = document.querySelector('[data-print-receipt]');
    if (button) button.addEventListener('click', function () { window.print(); });
})();
