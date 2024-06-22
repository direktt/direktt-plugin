<script setup>
  import { useDirekttStore } from "./store.js";
  import { onMounted, computed, ref } from "vue";

  const store = useDirekttStore();

  const openInNewTab = (url) => {
    const newWindow = window.open(url, "_blank", "noopener,noreferrer");
    if (newWindow) newWindow.opener = null;
  };

  const items = ref(Array.from({ length: 30 }, (k, v) => v + 1));

  async function api() {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(Array.from({ length: 10 }, (k, v) => v + items.value.at(-1) + 1));
      }, 1000);
    });
  }
  async function load({ done }) {
    // Perform API call
    const res = await api();

    items.value.push(...res);

    done("ok");
  }

  onMounted(() => {});
</script>

<template>
  <v-infinite-scroll :height="300" :items="items" :onLoad="load">
    <template v-for="(item, index) in items" :key="item">
      <div :class="['pa-2', index % 2 === 0 ? 'bg-grey-lighten-2' : '']">
        Item number #{{ item }}
      </div>
    </template>
  </v-infinite-scroll>
</template>

<style></style>
