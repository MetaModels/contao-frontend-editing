/**
 * This file is part of MetaModels/contao-frontend-editing.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/contao-frontend-editing
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/contao-frontend-editing/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

(function() {
    let widgets = document.querySelectorAll('.mm_fee_js_helper .widget.submitOnChange');
    if (!widgets.length) {
        return false;
    }

    const uuid  = location.pathname.split('/').slice(-1).join('');
    let overlay = document.querySelector('.fee-helper-overlay');

    widgets.forEach(function(widget) {
        let inputs = widget.querySelectorAll('input, select');
        if (!inputs.length) {
            return false;
        }

        inputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if(overlay) {
                    overlay.style.display = 'block';
                }
                let hidden   = document.createElement('input');
                hidden.type  = 'hidden';
                hidden.name  = 'SUBMIT_TYPE';
                hidden.value = 'auto';

                sessionStorage.setItem(
                    'fee-helper-focus',
                    uuid + ',' + location.hash + ',' + input.id + ',' + window.scrollY
                );

                input.form.append(hidden);
                input.form.submit();
            });
        });
    });

    const focus = sessionStorage.getItem('fee-helper-focus');
    if (!focus) {
        return;
    }

    setTimeout(function() {
        sessionStorage.removeItem('fee-helper-focus');

        const focusItems = focus.split(',');
        if ((uuid !== focusItems[0]) || (location.hash !== focusItems[1])) {
            return false;
        }

        const focusElement = document.querySelector('#' + focusItems[2]);
        if (!focusElement) {
            return false;
        }

        focusElement.focus();
        document.body.scrollTop            = parseInt(focusItems[3]); // For Safari
        document.documentElement.scrollTop = parseInt(focusItems[3]); // For Chrome, Firefox, IE and Opera
    }, 50);
})();
