import { createApp } from 'vue'
import App from '../vue/App.vue'
import { createPinia } from 'pinia'

import { VueQueryPlugin } from '@tanstack/vue-query'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'

'use strict'


vueapp = createApp(App);

const pinia = createPinia()

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

vueapp.use(pinia)
vueapp.use(vuetify)
vueapp.use(VueQueryPlugin)

vueapp.mount("#direktt-meta-app")


