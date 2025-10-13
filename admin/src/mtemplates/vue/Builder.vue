<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted,
  onBeforeUnmount,
  nextTick,
} from "vue";

import Editor from "./Editor.vue";

const selected_template= ref({ value: 'all', label: 'All Messages' })
const messageTypes = [
  { value: 'all', label: 'All Messages' },
  { value: 'bulk', label: 'Only Bulk Messages' },
  { value: 'individual', label: 'Only Individual Messages' },
  { value: 'none', label: 'Use it only via API' },
]

</script>

<template>

  <v-card class="pa-4">
    <Editor></Editor>
  </v-card>


</template>

<style></style>
