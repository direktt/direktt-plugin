if (process.env.NODE_ENV !== 'production') {
  __VUE_PROD_DEVTOOLS__ = true;
} else {
  __VUE_PROD_DEVTOOLS__ = false;
}

import { createApp } from 'vue'
import App from '../vue/App.vue'

import { VueQueryPlugin } from '@tanstack/vue-query'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'

'use strict'


vueapp = createApp(App);

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


