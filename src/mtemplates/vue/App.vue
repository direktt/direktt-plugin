<script setup>
import { useDirekttStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useDirekttStore();
const consent = ref(true)
const userSet = ref('all')

const categories = ref([]);
const tags = ref([]);
const selectedCategories = ref([]);
const selectedTags = ref([]);
const nonce = ref([]);

const send_message = ref(false)
const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'Message Template Sent'

const postId = ref(direktt_mtemplates_object.postId);
const marketing_consent = ref(false);
const direktt_user_id = ref()

const page = ref(0)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["mtemplates-taxonomies"],
  queryFn: getMTemplatesTaxonomies,
});

async function getMTemplatesTaxonomies() {
  let ret = {};
  const response = await doAjax({
    action: "direktt_get_mtemplates_taxonomies",
  });

  ret = response.data;
  categories.value = response.data.categories
  tags.value = response.data.tags
  nonce.value = response.data.nonce
  return ret;
}

async function clickSendMessage() {
  send_message.value = true
  let ret = {};
  try {
    const response = await doAjax({
      action: "direktt_send_mtemplates_message", // the action to fire in the server
      userSet: userSet.value,
      categories: JSON.stringify(selectedCategories.value),
      tags: JSON.stringify(selectedTags.value),
      nonce: nonce.value,
      postId: document.getElementById('post_ID').value
    })

    ret = response.data;
    if (ret.succ) {
      snackbar_color.value = 'success'
      snackbar_text.value = snack_succ_text
      snackbar.value = true
    }
  } catch (error) {

    snackbar_color.value = 'error'
    snackbar_text.value = error.responseJSON.data[0].message
    snackbar.value = true
  }
  send_message.value = false
  return ret;
}

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_mtemplates_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

const openInNewTab = (url) => {
  const newWindow = window.open(url, "_blank", "noopener,noreferrer");
  if (newWindow) newWindow.opener = null;
};

onMounted(() => {
});

</script>

<template>

  <v-card width="100%" class="pa-4 mb-4 sendMessageCard">
    <v-row class="pl-4 pr-4 text-caption">
      <v-checkbox label="Send only to users who gave consent" v-model="consent" color="info"></v-checkbox>
    </v-row>
    <v-row class="pl-4 pr-4 text-caption">
      <v-radio-group inline v-model="userSet">
        <v-radio label="All Channel Subscribers" value="all" color="info"></v-radio>
        <v-radio label="Selected Channel Subscribers" value="selected" color="info"></v-radio>
        <v-radio label="Channel Admin" value="admin" color="info"></v-radio>
      </v-radio-group>

    </v-row>
    <v-row class="pl-4 pr-4 text-caption">
      <v-col cols="6">
        <v-autocomplete v-model="selectedCategories" :items="categories" color="blue-grey-lighten-2" item-title="name"
          item-value="value" label="Categories" chips closable-chips multiple v-show="userSet == 'selected'"
          density="comfortable">
          <template v-slot:chip="{ props, item }">
            <v-chip v-bind="props" :prepend-avatar="item.raw.avatar" :text="item.raw.name" color="info"
              variant="flat"></v-chip>
          </template>

          <template v-slot:item="{ props, item }">
            <v-list-item v-bind="props" :prepend-avatar="item.raw.avatar" :subtitle="item.raw.group"
              :title="item.raw.name"></v-list-item>
          </template>
        </v-autocomplete>
      </v-col>
      <v-col cols="6">
        <v-autocomplete v-model="selectedTags" :items="tags" color="blue-grey-lighten-2" item-title="name"
          item-value="value" label="Tags" chips closable-chips multiple v-show="userSet == 'selected'"
          density="comfortable">
          <template v-slot:chip="{ props, item }">
            <v-chip v-bind="props" :prepend-avatar="item.raw.avatar" :text="item.raw.name" color="green"
              variant="flat"></v-chip>
          </template>

          <template v-slot:item="{ props, item }">
            <v-list-item v-bind="props" :prepend-avatar="item.raw.avatar" :subtitle="item.raw.group"
              :title="item.raw.name"></v-list-item>
          </template>
        </v-autocomplete>
      </v-col>

    </v-row>
    <v-row class="pa-4 text-caption">
      <v-btn variant="flat" class="text-none text-caption" color="info" :loading="send_message"
        @click="clickSendMessage">
        Send Template as Message
      </v-btn>
    </v-row>

  </v-card>
  <v-snackbar v-model="snackbar" :timeout="3000" :color="snackbar_color">
    {{ snackbar_text }}
    <template v-slot:actions>
      <v-btn variant="text" @click="snackbar = false">
        X
      </v-btn>
    </template>
  </v-snackbar>
</template>

<style></style>
