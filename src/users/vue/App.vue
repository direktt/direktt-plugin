<script setup>
import { useDirekttStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useDirekttStore();

const postId = ref(direktt_users_object.postId);
const marketing_consent = ref(false);
const direktt_user_id = ref()

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["marketing-consent", postId.value],
  queryFn: getMarketingConsent,
});

async function getMarketingConsent() {
  console.log('Ucitvam');
  let ret = {};
  const response = await doAjax({
    action: "direktt_get_marketing_consent", // the action to fire in the server
    postId: postId.value
  });

  ret = response.data;
  marketing_consent.value = response.data.marketing_consent === "1"
  direktt_user_id.value = response.data.direktt_user_id
  return ret;
}

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_users_object.ajaxurl,
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

<h1 class="mt-4">Direktt User Properties</h1>

<v-card class="pa-4">

  <table class="form-table" role="presentation">

    <tbody v-if="data">
      <tr>
        <th scope="row"><label for="blogname">Direktt Subscription ID:</label></th>
        <td>
          {{ direktt_user_id }}
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="blogname">Marketing consent:</label></th>
        <td>
          <div v-if="!marketing_consent">
              <v-icon color="error" icon="mdi-alert-outline" size="large"class='rm-4'></v-icon>
              Consent not given!
            </div>
            <div v-if="marketing_consent">
              <v-icon color="success" icon="mdi-check-bold" size="large"class='rm-4'></v-icon>
              Consent given
            </div>
        </td>
      </tr>
    </tbody>
  </table>
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
