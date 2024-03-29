var $doc = jQuery(document);
$doc.ready(function ($) {
    $doc.on('submit', '.custom-form-js', function (e) {
        e.preventDefault();
        var $form = $(this);
        var this_form = $form.attr('id');
        var test = true,
            thsInputs = $form.find('input, textarea'),
            $select = $form.find('select[required]');
        var $address = $form.find('input.address-js[required]');

        $select.each(function () {
            var $ths = $(this);
            var $label = $ths.closest('.form-group');
            var val = $ths.val();
            if (Array.isArray(val) && val.length === 0) {
                test = false;
                $label.addClass('error');
            } else {
                $label.removeClass('error');
                if (val === null || val === undefined) {
                    test = false;
                    $label.addClass('error');
                }
            }
        });
        thsInputs.each(function () {
            var thsInput = $(this),
                $label = thsInput.closest('.form-group'),
                thsInputType = thsInput.attr('type'),
                thsInputVal = thsInput.val().trim(),
                inputReg = new RegExp(thsInput.data('reg')),
                inputTest = inputReg.test(thsInputVal);
            if (thsInput.attr('required')) {
                if (thsInputVal.length <= 0) {
                    test = false;
                    thsInput.addClass('error');
                    $label.addClass('error');
                    thsInput.focus();
                } else {
                    thsInput.removeClass('error');
                    $label.removeClass('error');
                    if (thsInput.data('reg')) {
                        if (inputTest === false) {
                            test = false;
                            thsInput.addClass('error');
                            $label.addClass('error');
                            thsInput.focus();
                        } else {
                            thsInput.removeClass('error');
                            $label.removeClass('error');
                        }
                    }
                }
            }
        });
        if (!validationInputs($form)) return;
        var $inp = $form.find('input[name="consent"]');
        if ($inp.length > 0) {
            if ($inp.prop('checked') === false) {
                $inp.closest('.form-consent').addClass('error');
                return;
            }
            $inp.closest('.form-consent').removeClass('error');
        }
        if ($address.length > 0) {
            var addressTest = true;
            $address.each(function (index) {
                var $el = $(this);
                var val = $el.val() || '';
                var selected = $el.attr('data-selected') || '';
                if (selected.trim() !== val.trim()) {
                    test = false;
                    addressTest = false;
                    $el.addClass('error');
                } else {
                    $el.removeClass('error');
                }
                if (val.length === 0) {
                    test = false;
                    $el.addClass('error');
                }
            });
            if (!addressTest) showMassage(locationErrorString);
        }
        if (test) {
            var thisForm = document.getElementById(this_form);
            var formData = new FormData(thisForm);
            showPreloader();
            $.fancybox.close();
            var data = {
                type: $form.attr('method'),
                url: admin_ajax,
                processData: false,
                contentType: false,
                data: formData,
            };
            $form.trigger('reset');
            if (typeof grecaptcha === 'undefined') {
                sendRequest(data);
            } else {
                grecaptcha.ready(function () {
                    grecaptcha.execute(google_recaptcha_site_key, {action: 'submit'}).then(function (token) {
                        var $token = $form.find('input.token');
                        if ($token.length === 0) {
                            $form.append('<input type="hidden" name="token" class="token" value="' + token + '">');
                        } else {
                            $token.val(token);
                        }
                        thisForm = document.getElementById(this_form);
                        formData = new FormData(thisForm);
                        data.data = formData;
                        sendRequest(data);
                    });
                });
            }
        }
    });
    selectInit();
    telInit();
});

function selectInit() {
    if ($doc.find('.select_st').length > 0) {
        $doc.find('.select_st').selectric({
            disableOnMobile: false,
            nativeOnMobile: false
        });
    }
}

function telInit() {
    $doc.find('.custom-form-render input[type="tel"]').each(function () {
        var $input = $(this);
        console.log($input)
        $input.on('input', function (e) {
            console.log(isNumberKey(e))
            if (!isNumberKey(e)) {
                e.preventDefault();
                return;
            }
        })

    });
}

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode !== 43 && charCode > 31 && (charCode < 48 || charCode > 57));

}

function sendRequest(data) {
    $.ajax(data).done(function (r) {
        if (r) {
            if (isJsonString(r)) {
                var res = JSON.parse(r);
                if (res.msg !== '' && res.msg !== undefined) {
                    showMassage(res.msg);
                }
            } else {
                showMassage(r);
            }
        }
        hidePreloader();
    });
}

function validationInputs($form) {
    var obj = {};
    var res = true;
    var $requiredInputs = $form.find('[data-required]');
    $requiredInputs.each(function () {
        var $t = $(this);
        var name = $t.attr('name');
        if (name !== undefined) {
            var hasChecked = $t.prop('checked') === true;
            if (obj[name] === undefined) obj[name] = [];
            if (hasChecked) {
                obj[name].push($t.val());
            }
        }
    });
    for (var key in obj) {
        var items = obj[key];
        if (items.length === 0) {
            res = false;
            $form.find('[name="' + key + '"]').closest('.form-group').addClass('error');
        } else {
            $form.find('[name="' + key + '"]').closest('.form-group').removeClass('error');
        }

    }
    return res;
}

function showMassage(message) {
    $.fancybox.open(message);
    setTimeout(function () {
        $.fancybox.close();
    }, 3000);
}

function showPreloader() {
    jQuery('.cfe-preloader').addClass('active');
}

function hidePreloader() {
    jQuery('.cfe-preloader').removeClass('active')
}

function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}