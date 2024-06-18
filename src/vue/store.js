import { defineStore } from "pinia";
import { ref, watch, computed } from "vue";
import { direktt } from "../js/utils.js";

export const useDirekttStore = defineStore("direkttstore", () => {
  const initial = ref("Initial");

  /* watch(activetab, (newactivetab, prevactivetab) => {
      
    }) */

  /* function increment() {
      count.value++
    } */

  //return { zoomlevel, doubleCount, increment }
  return {
    initial,
  };
});
