<script setup>
import { watch, ref, nextTick } from "vue";

const props = defineProps({
  obj: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:obj"]);

// Helper: convert object to pairs
function objectToPairs(obj) {
  return Object.entries(obj).map(([key, value], i) => ({
    key,
    value,
    id: i + "_" + Date.now(),
  }));
}

// Track if update is coming from internal change
let isInternalUpdate = false;

// Initial conversion
const pairs = ref(objectToPairs(props.obj));

// Watch ONLY for external changes to props.obj, not on every update
watch(
  () => props.obj,
  (newObj) => {
    if (!isInternalUpdate) {
      pairs.value = objectToPairs(newObj);
    }
    isInternalUpdate = false;
  },
  { deep: true }
);

function onKeyChange(idx) {
  const pair = pairs.value[idx];
  // Check for duplicate key
  if (
    pairs.value.some(
      (p, i) => i !== idx && p.key.trim() && p.key === pair.key
    )
  ) {
    alert("Duplicate key! Please use a unique key.");
    nextTick(() => {
      pairs.value[idx].key = "";
    });
    return;
  }
  updateObj();
}

function onValueChange(idx) {
  updateObj();
}

function updateObj() {
  // Build a new object from pairs
  const newObj = {};
  pairs.value.forEach((pair) => {
    if (pair.key) newObj[pair.key] = pair.value;
  });

  isInternalUpdate = true; // Prevent re-entry in watcher!
  emit("update:obj", newObj);
}

function addPair() {
  pairs.value.push({
    key: "",
    value: "",
    id: Date.now() + "_" + Math.random(),
  });
  updateObj();
}

function removePair(idx) {
  pairs.value.splice(idx, 1);
  updateObj();
}
</script>

<template>
  <v-card width="100%" class="pa-4">
    <strong>Return Variables:</strong> <v-spacer></v-spacer>
    <v-btn variant="flat" class="text-none text-caption mb-4 mt-4" color="info" @click="addPair()">
      Add Variable
    </v-btn>
    <v-row v-for="(pair, idx) in pairs" :key="pair.id">
      <v-col cols="5">
        <div><strong>Key:</strong> <v-text-field v-model="pair.key" @change="onKeyChange(idx)"
            variant="outlined"></v-text-field></div>
      </v-col>
      <v-col cols="5">
        <div><strong>Value:</strong> <v-text-field v-model="pair.value" @change="onValueChange(idx)"
            variant="outlined"></v-text-field></div>
      </v-col>
      <v-col align-self="end">
        <v-btn variant="flat" class="text-none text-caption" color="info" @click="removePair(idx)">
          Remove Variable
        </v-btn>
      </v-col>
    </v-row>
  </v-card>
</template>