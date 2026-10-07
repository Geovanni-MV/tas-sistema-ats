import $ from 'jquery';

/**
 * Capa base para peticiones AJAX.
 * Mantiene la configuración HTTP separada de la lógica de cada módulo.
 */
export const Api = {
    request: async function (options) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        return $.ajax({
            dataType: 'json',
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            ...options,
        });
    },

    get: function (url) {
        return this.request({ method: 'GET', url });
    },

    post: function (url, payload) {
        return this.request({
            method: 'POST',
            url,
            contentType: 'application/json',
            data: JSON.stringify(payload),
        });
    },

    put: function (url, payload) {
        return this.request({
            method: 'PUT',
            url,
            contentType: 'application/json',
            data: JSON.stringify(payload),
        });
    },

    patch: function (url, payload) {
        return this.request({
            method: 'PATCH',
            url,
            contentType: 'application/json',
            data: JSON.stringify(payload),
        });
    },
};
