jQuery(function ($) {
    $('#direktt_user_tags').autocomplete({
        source: tags,
        minLength: 0
    }).on('focus', function () {
        $(this).autocomplete("search");
    });
});