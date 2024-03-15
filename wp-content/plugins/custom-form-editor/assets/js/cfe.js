jQuery(document).ready(function ($) {
    var $doc = jQuery(this);
    if( $doc.find('.select_st').length > 0){
        $doc.find('.select_st').selectric({
            disableOnMobile: false,
            nativeOnMobile: false
        });
    }

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
            $.ajax({
                type: $form.attr('method'),
                url: admin_ajax,
                processData: false,
                contentType: false,
                data: formData,
            }).done(function (r) {
                $form.trigger('reset');
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
    });
});

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