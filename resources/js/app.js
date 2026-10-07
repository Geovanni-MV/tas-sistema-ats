import './bootstrap';

import $ from 'jquery';

/*
|--------------------------------------------------------------------------
| jQuery global
|--------------------------------------------------------------------------
| Los scripts de cada módulo se cargan desde su propio template
| mediante @push('scripts') y comparten esta misma instancia de jQuery.
*/

window.$ = $;
window.jQuery = $;


/*
|--------------------------------------------------------------------------
| Menú estático colapsable
|--------------------------------------------------------------------------
*/

$(document).on('click', '[data-collapse-button]', function () {

    const key = $(this).data('collapse-button');

    $(`[data-collapse-panel="${key}"]`).toggleClass('hidden');

    $(`[data-collapse-icon="${key}"]`).toggleClass('rotate-180');

});
