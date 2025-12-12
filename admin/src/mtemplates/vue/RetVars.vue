<script setup>
import { watch, ref, nextTick } from "vue";

const props = defineProps({
  obj: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:obj"]);

const selectedCategoriesAdd = ref([]);
const selectedCategoriesRemove = ref([]);
const selectedTagsAdd = ref([]);
const selectedTagsRemove = ref([]);

const RESERVED_TAX_KEYS = [
  "addDirekttUserCategory",
  "removeDirekttUserCategory",
  "addDirekttUserTag",
  "removeDirekttUserTag",
];

// Normalize possible incoming strings/arrays to array
function toArray(val) {
  if (Array.isArray(val)) return val;
  if (typeof val === "string") {
    try {
      const parsed = JSON.parse(val);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  return [];
}

// Helper: convert object to pairs, excluding reserved tax keys
function objectToPairs(obj) {
  return Object.entries(obj)
    .filter(([key]) => !RESERVED_TAX_KEYS.includes(key))
    .map(([key, value], i) => ({
      key,
      value,
      id: i + "_" + Date.now(),
    }));
}

// Track if update is coming from internal change
let isInternalUpdate = false;

// Initial conversion (will be overridden by watcher with immediate:true)
const pairs = ref([]);

// Hydrate pairs and taxonomy selections from props.obj
function hydrateFromObj(newObj) {
  isInternalUpdate = true;
  pairs.value = objectToPairs(newObj);

  selectedCategoriesAdd.value = toArray(newObj.addDirekttUserCategory);
  selectedCategoriesRemove.value = toArray(newObj.removeDirekttUserCategory);
  selectedTagsAdd.value = toArray(newObj.addDirekttUserTag);
  selectedTagsRemove.value = toArray(newObj.removeDirekttUserTag);

  nextTick(() => {
    isInternalUpdate = false;
  });
}

// Watch ONLY for external changes to props.obj
watch(
  () => props.obj,
  (newObj) => {
    if (isInternalUpdate) {
      isInternalUpdate = false;
      return;
    }
    hydrateFromObj(newObj);
  },
  { deep: true, immediate: true }
);

// Watch taxonomy selections to trigger updateObj
watch(
  [selectedCategoriesAdd, selectedCategoriesRemove, selectedTagsAdd, selectedTagsRemove],
  () => {
    if (isInternalUpdate) return;
    updateObj();
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

function onValueChange() {
  updateObj();
}

function updateObj() {
  // Build a new object from pairs
  const newObj = {};
  pairs.value.forEach((pair) => {
    if (pair.key) newObj[pair.key] = pair.value;
  });

  // Add taxonomy retVars as JSON-encoded strings (if any selection)
  if (selectedCategoriesAdd.value.length) {
    newObj.addDirekttUserCategory = JSON.stringify(selectedCategoriesAdd.value);
  }
  if (selectedCategoriesRemove.value.length) {
    newObj.removeDirekttUserCategory = JSON.stringify(selectedCategoriesRemove.value);
  }
  if (selectedTagsAdd.value.length) {
    newObj.addDirekttUserTag = JSON.stringify(selectedTagsAdd.value);
  }
  if (selectedTagsRemove.value.length) {
    newObj.removeDirekttUserTag = JSON.stringify(selectedTagsRemove.value);
  }

  isInternalUpdate = true; // Prevent re-entry in watcher
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
    <v-btn
      variant="flat"
      class="text-none text-caption mb-4 mt-4"
      color="info"
      @click="addPair()"
    >
      Add Variable
    </v-btn>

    <v-row v-for="(pair, idx) in pairs" :key="pair.id">
      <v-col cols="4">
        <div>
          <strong>Key:</strong>
          <v-text-field
            v-model="pair.key"
            @change="onKeyChange(idx)"
            variant="outlined"
          ></v-text-field>
        </div>
      </v-col>
      <v-col cols="5">
        <div>
          <strong>Value:</strong>
          <v-text-field
            v-model="pair.value"
            @change="onValueChange(idx)"
            variant="outlined"
          ></v-text-field>
        </div>
      </v-col>
      <v-col align-self="end">
        <v-btn
          variant="flat"
          class="text-none text-caption"
          color="info"
          @click="removePair(idx)"
        >
          Remove
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <strong>Taxonomy Related Variables:</strong> <v-spacer></v-spacer>

        <div class="mt-4">
          <strong>Add Categories (addDirekttUserCategory):</strong>
          <v-autocomplete
            v-model="selectedCategoriesAdd"
            :items="props.categories"
            color="blue-grey-lighten-2"
            item-title="name"
            item-value="value"
            label="Categories"
            chips
            closable-chips
            multiple
            density="comfortable"
            id="categories_autocomplete_add"
          >
            <template v-slot:chip="{ props, item }">
              <v-chip
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :text="item.raw.name"
                color="info"
                variant="flat"
              ></v-chip>
            </template>

            <template v-slot:item="{ props, item }">
              <v-list-item
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :subtitle="item.raw.group"
                :title="item.raw.name"
              ></v-list-item>
            </template>
          </v-autocomplete>
        </div>

        <div class="mt-4">
          <strong>Remove Categories (removeDirekttUserCategory):</strong>
          <v-autocomplete
            v-model="selectedCategoriesRemove"
            :items="props.categories"
            color="blue-grey-lighten-2"
            item-title="name"
            item-value="value"
            label="Categories"
            chips
            closable-chips
            multiple
            density="comfortable"
            id="categories_autocomplete_remove"
          >
            <template v-slot:chip="{ props, item }">
              <v-chip
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :text="item.raw.name"
                color="info"
                variant="flat"
              ></v-chip>
            </template>

            <template v-slot:item="{ props, item }">
              <v-list-item
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :subtitle="item.raw.group"
                :title="item.raw.name"
              ></v-list-item>
            </template>
          </v-autocomplete>
        </div>

        <div class="mt-4">
          <strong>Add Tags (addDirekttUserTag):</strong>
          <v-autocomplete
            v-model="selectedTagsAdd"
            :items="props.tags"
            color="blue-grey-lighten-2"
            item-title="name"
            item-value="value"
            label="Tags"
            chips
            closable-chips
            multiple
            density="comfortable"
            id="tags_autocomplete_add"
          >
            <template v-slot:chip="{ props, item }">
              <v-chip
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :text="item.raw.name"
                color="success"
                variant="flat"
              ></v-chip>
            </template>

            <template v-slot:item="{ props, item }">
              <v-list-item
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :subtitle="item.raw.group"
                :title="item.raw.name"
              ></v-list-item>
            </template>
          </v-autocomplete>
        </div>

        <div class="mt-4">
          <strong>Remove Tags (removeDirekttUserTag):</strong>
          <v-autocomplete
            v-model="selectedTagsRemove"
            :items="props.tags"
            color="blue-grey-lighten-2"
            item-title="name"
            item-value="value"
            label="Tags"
            chips
            closable-chips
            multiple
            density="comfortable"
            id="tags_autocomplete_remove"
          >
            <template v-slot:chip="{ props, item }">
              <v-chip
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :text="item.raw.name"
                color="success"
                variant="flat"
              ></v-chip>
            </template>

            <template v-slot:item="{ props, item }">
              <v-list-item
                v-bind="props"
                :prepend-avatar="item.raw.avatar"
                :subtitle="item.raw.group"
                :title="item.raw.name"
              ></v-list-item>
            </template>
          </v-autocomplete>
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>