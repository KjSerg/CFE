var requiredFields = [];
jQuery(document).ready(function ($) {
    jQuery('#titlediv').append('<div id="required-fields-notice" class="notice notice-error" style="display: none;"></div>');
    jQuery(document).on('change', '.cf-container__fields input, .cf-container__fields select, .cf-container__fields textarea', checkRequiredFields);
    jQuery(document).on('input', '.cf-container__fields input, .cf-container__fields textarea', checkRequiredFields);
    jQuery(document).on('click', '.cf-complex__inserter-button, .cf-complex__group-action, .cf-media-gallery__item-remove, .cf-complex__tabs-item', checkRequiredFields);
    jQuery(document).on('click', function (e) {
        if (jQuery(e.target).hasClass('media-button-select')) {
            checkRequiredFields();
        }
    });
    setTimeout(checkRequiredFields, 1000);
});


function checkRequiredFields() {
    setRequiredFields();
    var missingFields = [];
    requiredFields.forEach(function (fieldName) {
        var $field = jQuery('#' + fieldName);
        var fieldValue = $field.val();
        var $wrapper = $field.closest('.cf-field');
        if ($field.hasClass('cf-media-gallery__browse')) {
            fieldValue = $wrapper.find('.cf-media-gallery__item').length;
        }
        if (!fieldValue) {
            var $wrapperGroup = $field.closest('.cf-complex__group');
            var title = $wrapper.find('.cf-field__label').text();
            title = '<strong>' + title + '</strong>';
            if ($wrapperGroup.length > 0) {
                var wrapperGroupIndex = $wrapperGroup.index();
                var $cfBody = $wrapperGroup.closest('.cf-field__body');
                var $tab = $cfBody.find('.cf-complex__tabs').eq(0).find('.cf-complex__tabs-item').eq(wrapperGroupIndex);
                if ($tab.length > 0) {
                    var tabName = $tab.text();
                    title = '<em>' + tabName + ' [#' + (wrapperGroupIndex + 1) + ']</em> ' + title;
                }
            }
            var html = title;
            missingFields.push(html);
        }
    });

    if (missingFields.length > 0) {
        var message = '<h1 class="wp-heading-inline">\n' +
            'Mandatory fields omitted:</h1><br><br> ' + missingFields.join('; <hr> ');
        jQuery('#required-fields-notice').html(message).show();
    } else {
        jQuery('#required-fields-notice').hide();
    }
}

function setRequiredFields() {
    requiredFields = [];
    jQuery(document).find('.cf-field__asterisk').each(function (index) {
        var $t = jQuery(this);
        var $wrapper = $t.closest('.cf-field');
        var $field = $wrapper.find('textarea').eq(0);
        if ($field.length === 0) {
            $field = $wrapper.find('select').eq(0);
        }
        if ($field.length === 0) {
            $field = $wrapper.find('input').eq(0);
        }
        if ($field.length === 0) {
            $field = $wrapper.find('.cf-media-gallery__browse').eq(0);
        }
        if ($field.length > 0) {
            var fieldID = $field.attr('id');
            var wrapperID = $wrapper.attr('id');
            if (wrapperID === undefined) $wrapper.attr('id', 'required-field-wrapper-' + index);
            if (fieldID === undefined) $field.attr('id', 'required-field-' + index);
            fieldID = $field.attr('id');
            wrapperID = $wrapper.attr('id');
            requiredFields.push(fieldID);
        }

    });
    console.log(requiredFields);
}