<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref } from 'vue'

const store = useDirekttStore()

const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {

})

</script>

<template>
<div>{{ store.initial }}</div>
</template>

<style>

</style>