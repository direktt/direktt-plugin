import { createApp } from 'vue'
import App from '../vue/App.vue'
import { createPinia } from 'pinia'
import naive from "naive-ui";

import { bt_import } from './utils.js'
import { useBTImportStore } from '../vue/store.js'

'use strict'

// vue app setup

vueapp = createApp(App);
const pinia = createPinia()
vueapp.use(pinia)
vueapp.use(naive);

bt_import.store = useBTImportStore()

vueapp.mount("#app");


