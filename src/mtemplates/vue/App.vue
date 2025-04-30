<script setup>
import { useDirekttStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useDirekttStore();

const postId = ref(direktt_mtemplates_object.postId);
const marketing_consent = ref(false);
const direktt_user_id = ref()
const direktt_admin_user_id = ref()
const items = ref([]);

const page = ref(0)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["marketing-consent", postId.value],
  queryFn: getMarketingConsent,
});

async function getMarketingConsent() {
  let ret = {};
  const response = await doAjax({
    action: "direktt_get_marketing_consent", // the action to fire in the server
    postId: postId.value
  });

  ret = response.data;
  marketing_consent.value = response.data.marketing_consent === "1"
  direktt_user_id.value = response.data.direktt_user_id
  direktt_admin_user_id.value = response.data.direktt_admin_user_id
  return ret;
}

async function getUserEvents() {
  let ret = {};
  console.log('Ucitavam');
  console.log(page.value);
  const response = await doAjax({
    action: "direktt_get_user_events", // the action to fire in the server
    postId: postId.value,
    page: page.value
  });

  ret = response.data;
  return ret;
}

async function load({ done }) {
  // Perform API call
  const res = await getUserEvents();
  if (res.length == 0) {
    done("empty");
  } else {
    items.value.push(...res);
    page.value = items.value[items.value.length - 1].ID
    done("ok");
  }
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
  console.log('mtemplates')
});
</script>

<template>

  <p></p>

  <v-card class="pa-4">
    <h1 class="mt-4">Send Message Template</h1>
    <table class="form-table" role="presentation">
      <tbody v-if="data">
        <tr>
          <td>
            <v-checkbox label="Send only to users who gave consent"></v-checkbox>
          </td>
        </tr>
        <tr>
          <td>
            <v-radio-group>
              <v-radio label="All Direktt Users" value="all"></v-radio>
              <v-radio label="Selected Users" value="selected"></v-radio>
            </v-radio-group>
          </td>
        </tr>
      </tbody>
    </table>
    <v-card class="pa-4">
      <v-autocomplete v-model="friends" :disabled="isUpdating" :items="people" color="blue-grey-lighten-2"
        item-title="name" item-value="name" label="Categories" chips closable-chips multiple>
        <template v-slot:chip="{ props, item }">
          <v-chip v-bind="props" :prepend-avatar="item.raw.avatar" :text="item.raw.name"></v-chip>
        </template>

        <template v-slot:item="{ props, item }">
          <v-list-item v-bind="props" :prepend-avatar="item.raw.avatar" :subtitle="item.raw.group"
            :title="item.raw.name"></v-list-item>
        </template>
      </v-autocomplete>

      <v-autocomplete v-model="friends" :disabled="isUpdating" :items="people" color="blue-grey-lighten-2"
        item-title="name" item-value="name" label="Tags" chips closable-chips multiple>
        <template v-slot:chip="{ props, item }">
          <v-chip v-bind="props" :prepend-avatar="item.raw.avatar" :text="item.raw.name"></v-chip>
        </template>

        <template v-slot:item="{ props, item }">
          <v-list-item v-bind="props" :prepend-avatar="item.raw.avatar" :subtitle="item.raw.group"
            :title="item.raw.name"></v-list-item>
        </template>
      </v-autocomplete>
      <v-btn variant="flat" class="text-none text-caption" color="#2271b1">
  Send template
</v-btn>
    </v-card>
    
    <p></p>

  </v-card>

  <h1 class="mt-4">Direktt User Events</h1>
  <v-infinite-scroll :height="300" :items="items" :onLoad="load">
    <template v-for="(item, index) in items" :key="item">
      <div :class="['pa-2', index % 2 === 0 ? 'bg-grey-lighten-2' : '']">
        Item number #{{ item }}
      </div>
    </template>
  </v-infinite-scroll>
</template>

<style></style>
