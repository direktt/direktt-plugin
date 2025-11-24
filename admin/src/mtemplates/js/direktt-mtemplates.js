if (process.env.NODE_ENV !== 'production') {
  __VUE_PROD_DEVTOOLS__ = true;
} else {
  __VUE_PROD_DEVTOOLS__ = false;
}

import { createApp } from 'vue'
import AppBuilder from '../vue/AppBuilder.vue'
import App from '../vue/App.vue'

import { VueQueryPlugin } from '@tanstack/vue-query'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'


(function ($) {
    'use strict';

    $(function () {

        'use strict'

        const vueappBuilder = createApp(AppBuilder);
        const vueapp = createApp(App);

        const vuetify = createVuetify({
            components,
            directives,
            icons: {
                defaultSet: 'mdi',
                aliases,
                sets: {
                    mdi,
                },
            },
        })

        vueapp.use(vuetify)
        vueapp.use(VueQueryPlugin)
        vueapp.mount("#direktt-meta-app")

        vueappBuilder.use(vuetify)
        vueappBuilder.mount("#appBuilder")

    });

    $('#publish').one('click', function (event) {
        $('#direktt_mt_json_hidden').val($('#direktt_mt_json').val())
    });

    window.onbeforeunload = function () {

        if ($('#direktt_mt_json').val() != $('#direktt_mt_json_hidden').val()) {
            return ('Are you sure you want to leave? Changes might be unsaved');
        }
    };

})(jQuery)
