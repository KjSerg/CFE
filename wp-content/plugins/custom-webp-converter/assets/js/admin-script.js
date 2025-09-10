var checking = false;

jQuery(function ($) {
    console.log('✅ admin-script.js підключився');

    // --- 1) Підписка на plupload (якщо є) ---
    try {
        if (typeof wp !== 'undefined' && wp.Uploader && wp.Uploader.uploader) {
            var pl = wp.Uploader.uploader;
            console.log('plupload знайдено, підписуюсь на події');

            pl.bind('FilesAdded', function (up, files) {
                files.forEach(function (f) {
                    console.log('plupload: FilesAdded', f);
                });
            });

            pl.bind('FileUploaded', function (up, file, response) {
                console.log('plupload: FileUploaded', file, response);
                // спробуємо розпарсити відповідь WP
                try {
                    var txt = (response && response.response) ? response.response : response;
                    var json = JSON.parse(txt);
                    console.log('plupload JSON:', json);
                } catch (e) {
                    console.log('plupload response (no json):', response);
                }
            });

            pl.bind('Error', function (up, err) {
                console.error('plupload Error', err);
            });
        }
    } catch (e) {
        console.warn('plupload bind error', e);
    }

    // --- 2) Перехоплення fetch (якщо використовується) ---
    if (window.fetch) {
        var _fetch = window.fetch;
        window.fetch = function (input, init) {
            var url = (typeof input === 'string') ? input : (input && input.url);
            if (url && url.indexOf('async-upload.php') !== -1) {
                console.log('fetch -> async-upload.php починається', url, init);
                return _fetch.apply(this, arguments).then(function (response) {
                    // Клонуємо, щоб зчитати текст не знищуючи оригіналу
                    response.clone().text().then(function (text) {
                        try {
                            var json = JSON.parse(text);
                            console.log('fetch -> async-upload.php відповідь (json):', json);
                        } catch (e) {
                            console.log('fetch -> async-upload.php відповідь (text):', text);
                        }
                    });
                    return response;
                });
            }
            return _fetch.apply(this, arguments);
        };
    }

    // --- 3) Перехоплення XMLHttpRequest ---
    (function () {
        var origOpen = XMLHttpRequest.prototype.open;
        var origSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function (method, url) {
            this._requestMethod = method;
            this._requestURL = url;
            return origOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function (body) {
            try {
                if (this._requestURL && this._requestURL.indexOf('async-upload.php') !== -1) {
                    console.log('XHR -> async-upload.php SEND', this._requestURL, this._requestMethod, body);
                    this.addEventListener('load', function () {
                        console.log('XHR -> async-upload.php LOAD', this.status, this.responseText);
                        try {
                            var json = JSON.parse(this.responseText);
                            console.log('XHR parsed JSON:', json);
                            getLastErrors();
                        } catch (e) {
                            console.log('XHR response is not JSON');
                        }
                    });

                    this.addEventListener('error', function () {
                        console.error('XHR -> async-upload.php ERROR', this);
                    });

                    this.addEventListener('abort', function () {
                        console.warn('XHR -> async-upload.php ABORT', this);
                    });
                }
            } catch (e) {
                console.warn('XHR hook error', e);
            }

            return origSend.apply(this, arguments);
        };
    })();


    function getLastErrors() {
        if (checking) return;
        setTimeout(function () {
            checking = true;
            $.post(cwc_params.ajax_url, {
                action: cwc_params.action,
                nonce: cwc_params.nonce
            }).done(function (response) {
                checking = false;
                if (response.success && response.data.message) {
                    alert(response.data.message);
                }
            }).fail(function () {
                checking = false;
                alert('HTTP error. Could not retrieve details from server.');
            });

        }, 100);
    }
});
