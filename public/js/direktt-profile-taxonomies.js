'use strict'

jQuery(document).ready(function ($) {
    // Show confirmation popup
    $('#saveTaxonomiesBtn').off('click').on('click', function (e) {
        e.preventDefault();
        $('.direktt-loader-overlay').fadeIn();
        setTimeout(function () {
            $('form').submit();
        }, 500);
    });

});