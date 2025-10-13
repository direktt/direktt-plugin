import { createApp } from 'vue'
import AppBuilder from '../vue/AppBuilder.vue'
import App from '../vue/App.vue'
import { createPinia } from 'pinia'

import { VueQueryPlugin } from '@tanstack/vue-query'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'


(function ($) {
    'use strict';

    $(function () {

        'use strict'

        const vueappBuilder = createApp(AppBuilder);
        const vueapp = createApp(App);

        const pinia = createPinia()

        const vuetify = createVuetify({
            components,
            directives,
        })

        vueapp.use(pinia)
        vueapp.use(vuetify)
        vueapp.use(VueQueryPlugin)
        vueapp.mount("#app")

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
