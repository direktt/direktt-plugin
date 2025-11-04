<script setup>
import { useDirekttStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";
import { mdiAlertOutline, mdiCheckBold } from '@mdi/js'

const store = useDirekttStore();

const postId = ref(direktt_users_object.postId);
const marketing_consent = ref(false);
const direktt_user_id = ref("")
const direktt_admin_subscription = ref("")
const direktt_membership_id = ref("")
const direktt_avatar_url = ref("")
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
    postId: postId.value,
    nonce: direktt_users_object.nonce
  });

  ret = response.data;
  marketing_consent.value = response.data.marketing_consent === "1"
  direktt_admin_subscription.value = response.data.admin_subscription === "1"
  direktt_user_id.value = response.data.direktt_user_id
  direktt_avatar_url.value = response.data.avatar_url
  direktt_membership_id.value = response.data.membership_id
  //direktt_admin_subscription.value = response.data.admin_subscription
  return ret;
}

async function getUserEvents() {
  let ret = {};
  const response = await doAjax({
    action: "direktt_get_user_events", // the action to fire in the server
    postId: postId.value,
    page: page.value,
    nonce: direktt_users_object.nonce
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

function createChatQRCode(subscriptionId) {
  const actionObject = {
    action: {
      type: "chat",
      params: {
        subscriptionId: subscriptionId
      },
      retVars: {}
    }
  }
  return JSON.stringify(actionObject)
}

onMounted(() => { });
</script>

<template>



    <table class="widefat striped" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="blogname">Direktt Subscription ID:</label></th>
          <td>
            {{ direktt_user_id }}
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="blogname">Admin Subscription:</label></th>
          <td>
            {{ direktt_admin_subscription }}
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="blogname">Avatar URL:</label></th>
          <td>
            {{ direktt_avatar_url }}
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="blogname">Memebrship ID:</label></th>
          <td>
            {{ direktt_membership_id }}
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="blogname">Marketing consent:</label></th>
          <td>
            <div v-if="!marketing_consent">
              <v-icon color="error" :icon="mdiAlertOutline" size="large" class='rm-4'></v-icon>
              Consent not given!
            </div>
            <div v-if="marketing_consent">
              <v-icon color="info" :icon="mdiCheckBold" size="large" class='rm-4'></v-icon>
              Consent given
            </div>
          </td>
        </tr>
        <tr v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">
          <th scope="row"><label for="blogname">QR Code for subscription:</label></th>
          <td>
            <div>
              <vue-qrcode :value="createChatQRCode( direktt_user_id )"
                :options="{ width: 300 }"></vue-qrcode>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <p></p>

  <!--<h1 class="mt-4">Direktt User Events</h1>
  <v-infinite-scroll :height="300" :items="items" :onLoad="load">
    <template v-for="(item, index) in items" :key="item">
      <div :class="['pa-2', index % 2 === 0 ? 'bg-grey-lighten-2' : '']">
        Item number #{{ item }}
      </div>
    </template>
  </v-infinite-scroll>-->
</template>

<style></style>
