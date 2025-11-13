<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref, watch } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'
import { mdiAlertOutline, mdiCheckBold, mdiContentCopy } from '@mdi/js'
import QRCodeStyling from "../../qrcode/vue/QRCodeStyling.vue";

const queryClient = useQueryClient()

const store = useDirekttStore()

const activation_status = ref(null)
const channel_data = ref(null)

const keyCopied = ref(false);

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['direktt-dashboard'],
  queryFn: getDashboard
})

const refDashboardObject = ref(window.direktt_dashboard_object);

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_dashboard_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

async function getDashboard() {

  let ret = {}
  const response = await doAjax(
    {
      action: "direktt_get_dashboard",  // the action to fire in the server
    }
  )
  ret = response.data
  return ret
}

function createSubscribeQRCode(channelId, channelTitle) {
  const actionObject = {
    action: {
      type: "subscription",
      params: {
        channelId: channelId,
        channelTitle: channelTitle
      },
      retVars: {}
    }
  }
  //return JSON.stringify(actionObject)
  return ("https://direktt.com/subscribe/" + channelId)
}

const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

async function getActivationData(channelId) {
  const response = await doAjax({
    action: "direktt_get_activation_data",
    channel_id: channelId
  });
  return response.data; // adjust if response structure is different
}

async function copyToClipboard(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(text);
  } else {
    // Fallback for older browsers
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';  // Prevent scrolling to bottom
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      // @ts-ignore: execCommand is deprecated, but used as fallback
      document.execCommand('copy');
      return Promise.resolve();
    } catch (err) {
      return Promise.reject(err);
    } finally {
      document.body.removeChild(textarea);
    }
  }
}

const copyKey = async (APIKey) => {
  try {
    await copyToClipboard(APIKey)
    //await navigator.clipboard.writeText(APIKey);
    keyCopied.value = true;
    setTimeout(() => {
      keyCopied.value = false;
    }, 2000);
  } catch (e) {
    console.error("Copy failed", e);
  }
};

watch(data, async (val) => {
  if (val && val.direktt_channel_id) {
    // Optionally, you could set a "loading" state for activation
    try {
      activation_status.value = null
      const activationData = await getActivationData(val.direktt_channel_id)
      if (activationData && activationData.hasOwnProperty("activatedAt") && activationData.hasOwnProperty("domain") && activationData.domain !== "") {
        activation_status.value = true
        channel_data.value = activationData
      } else {
        activation_status.value = false
      }
    } catch (e) {
      activation_status.value = null // or null/error if you want
    }
  }
})

onMounted(() => {
})

</script>

<template>
  <h1>Direktt Dashboard</h1>

  <div class="wrap">

    <table class="widefat striped" role="presentation">

      <tbody v-if="data">

        <tr v-if="data.isSSL !== true">
          <th scope=" row"><label for="blogname">SSL Status:</label></th>
          <td>
            <div>
              <v-icon color="error" :icon="mdiAlertOutline" size="large" class='rm-4'></v-icon>
              Your site url in your WordPress' General Settings not set to use https protocol.
              <br></br><strong>Direktt requires that your site is served via secured https connection</strong>
            </div>
          </td>
        </tr>

        <tr v-if="data.direktt_channel_title != ''">
          <th scope=" row"><label for="blogname">Channel title:</label></th>
          <td>
            <div>
              {{ channel_data?.title ? channel_data.title : data.direktt_channel_title }}
            </div>
          </td>
        </tr>

        <tr v-if="data.direktt_channel_id != ''">
          <th scope="row"><label for="blogname">Channel Id:</label></th>
          <td>
            <div>
              {{ channel_data?.id ? channel_data.id : data.direktt_channel_id }}
            </div>
          </td>
        </tr>

        <tr v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">
          <th scope="row"><label for="blogname">QR Code for subscription:</label></th>
          <td>
            <div>
              <QRCodeStyling v-if="refDashboardObject"
                :qr-code-data="createSubscribeQRCode(data.direktt_channel_id, data.direktt_channel_title)"
                :qr-code-logo-url="refDashboardObject.qr_code_logo_url"
                :qr-code-color="refDashboardObject.qr_code_color"
                :qr-code-bckg-color="refDashboardObject.qr_code_bckg_color" qr-code-download=true />
            </div>
          </td>
        </tr>

        <tr v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">
          <th scope="row"><label for="blogname">URL for subscription:</label></th>
          <td>
            <div>
              {{ createSubscribeQRCode(data.direktt_channel_id, data.direktt_channel_title) }}
              <v-icon tag="i" color="info" :icon="mdiContentCopy" class="cursor-pointer"
                @click="copyKey(createSubscribeQRCode(data.direktt_channel_id, data.direktt_channel_title))" />
              <v-badge v-if="keyCopied" color="info" content="Copied" inline></v-badge>
            </div>
          </td>
        </tr>


        <tr>
          <th scope="row"><label for="blogname">Activation status:</label></th>
          <td v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">
            <div v-if="activation_status === null">
              <v-progress-circular :size="30" :width="4" color="info" indeterminate></v-progress-circular>
            </div>
            <div v-if="activation_status === false">
              <v-icon color="error" :icon="mdiAlertOutline" size="large" class='rm-4'></v-icon>
              Not activated
              <br></br> <strong>Note: Your WordPress Instance is not activated.<br></br>You should activate your
                WordPress instance
                on the Settings Panel.</strong>
            </div>
            <div v-if="activation_status === true">
              <v-icon color="info" :icon="mdiCheckBold" size="large" class='rm-4'></v-icon>
              Activated
            </div>
          </td>

          <td v-else>
            <div>
              <v-icon color="error" :icon="mdiAlertOutline" size="large" class='rm-4'></v-icon>
              Not activated
              <br></br> <strong>Note: Your WordPress Instance has
                not yet been activated.<br></br>You should activate your WordPress instance
                on the Settings Panel.</strong>
            </div>
          </td>

        </tr>

        <template v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">

          <tr v-if="activation_status">
            <th scope="row"><label for="blogname">Registered domain:</label></th>
            <td>
              <div v-if="activation_status">
                {{ channel_data.domain }}
              </div>
            </td>
          </tr>

          <tr v-if="activation_status">
            <th scope="row"><label for="blogname">Number of Subscribers:</label></th>
            <td>
              <div v-if="activation_status">
                <v-icon color="error" :icon="mdiAlertOutline" size="large" class='rm-4'
                  v-if="channel_data.count != channel_data.localCount"></v-icon>
                Direktt API: {{ channel_data.count }} / WordPress: {{ channel_data.localCount }}
                <strong v-if="channel_data.count != channel_data.localCount"><br></br>Number of Subscribers in
                  your
                  local database and Direktt API do not match.<br></br>You should synchronize the Subscribers'
                  database
                  on Settings Panel.</strong>
              </div>
            </td>
          </tr>

        </template>

      </tbody>
    </table>

  </div>
</template>

<style></style>