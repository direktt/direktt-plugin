import { createApp } from 'vue'
import App from '../vue/App.vue'
import { createPinia } from 'pinia'

'use strict'


vueapp = createApp(App);
const pinia = createPinia()
vueapp.use(pinia)

vueapp.mount("#app");


