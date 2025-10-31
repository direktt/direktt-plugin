<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref, watch } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useDirekttStore()

const nonce = ref(direktt_settings_object.nonce)

const activation_status = ref(null)
const channel_data = ref(null)

const sync_loading = ref(false)

const api_key = ref('')
const redirect_url = ref('')
const pairing_prefix = ref('')
const pairing_succ_template = ref('')
const save_loading = ref(false)
const templates = ref([])
const selected_template = ref({})
const reset_pairings = ref(false)

const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'Settings Saved'

// Sync progress bar controles

const syncing = ref(false);
const sync_progress = ref(0);
const sync_total = ref(1);
const sync_current = ref(0);
const sync_bar_visible = ref(false);
const sync_message = ref('');


const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['direktt-settings'],
  queryFn: getSettings,
  refetchOnWindowFocus: false
})

const mutation = useMutation({
  mutationFn: saveSettings,
  onSuccess: async () => {
    // Invalidate and refetch
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] })
    save_loading.value = false

    snackbar_color.value = 'success'
    snackbar_text.value = snack_succ_text
    snackbar.value = true
  },
  onError: (error, variables, context) => {
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] })
    save_loading.value = false

    snackbar_color.value = 'error'
    snackbar_text.value = error.responseJSON.data[0].message
    snackbar.value = true
  },
})

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_settings_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

async function getSettings() {

  let ret = {}
  const response = await doAjax(
    {
      action: "direktt_get_settings",  // the action to fire in the server
    }
  )
  ret = response.data

  api_key.value = response.data.api_key
  redirect_url.value = response.data.redirect_url
  pairing_prefix.value = response.data.pairing_prefix
  pairing_succ_template.value = response.data.pairing_succ_template
  templates.value = response.data.templates

  selected_template.value = templates.value.find(
    function (elem) {
      return elem.value == pairing_succ_template.value
    }
  )
  return ret
}

function clickSaveSettings() {
  save_loading.value = true

  let mutation_obj = {
    api_key: api_key.value,
    redirect_url: redirect_url.value,
    pairing_prefix: pairing_prefix.value,
    reset_pairings: reset_pairings.value
  }

  if (selected_template.value) {
    mutation_obj.pairing_succ_template = selected_template.value.value
  }

  mutation.mutate(mutation_obj)

  reset_pairings.value = false
}

async function clickSyncUsers() {

  sync_bar_visible.value = true;
  sync_progress.value = 0;
  sync_current.value = 0;
  sync_total.value = 1;
  syncing.value = true;
  sync_message.value = "Preparing to sync...";

  let offset = 0;
  const batchSize = 5;

  try {
    let finished = false;

    while (!finished) {

      let response = await doAjax({
        action: 'direktt_sync_users',
        nonce: nonce.value,
        offset: offset,
        batch_size: batchSize
      });

      if (response.success) {
        const resp = response.data;

        sync_total.value = resp.total > 0 ? resp.total : 1;
        sync_current.value = resp.current ?? 0;
        sync_progress.value = (sync_current.value / sync_total.value) * 100;

        // Optionally show per-batch user details
        if (resp.details && Array.isArray(resp.details) && resp.details.length) {
          sync_message.value = `Processing: ${resp.details.join(', ')}`;
        } else {
          sync_message.value = `Syncing: ${sync_current.value} / ${sync_total.value}`;
        }

        finished = resp.finished;
        offset = sync_current.value;
      } else {
        throw response;
      }

      sync_message.value = `Syncing users... (${sync_current.value} / ${sync_total.value})`;
    }

    sync_message.value = "Subscribers' Database Successfully Synced";
    snackbar_color.value = 'success';
    snackbar_text.value = sync_message.value;
    snackbar.value = true;
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] });
  } catch (error) {
    let errMsg = error?.responseJSON?.data?.[0]?.message || "An error occurred";
    snackbar_color.value = 'error';
    snackbar_text.value = errMsg;
    snackbar.value = true;
    sync_message.value = "Error during syncing.";
  }
  syncing.value = false;
  setTimeout(() => { sync_bar_visible.value = false; }, 1000);
}

async function saveSettings(obj) {

  obj.action = "direktt_save_settings"
  obj.nonce = nonce.value
  obj.activation_status = activation_status.value

  const response = await doAjax(obj)
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

  <v-card class="pa-4 mr-4" variant="text">

    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr v-if="data.isSSL !== true">
          <th scope=" row"><label for="blogname">SSL Status:</label></th>
          <td>
            <div>
              <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'></v-icon>
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

        <tr>
          <th scope="row"><label for="blogname">Direktt API Key</label></th>
          <td>
            <input type="text" name="direkttapikey" id="direkttapikey" size="50" placeholder="" v-model="api_key">
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="blogname">Activation status:</label></th>
          <td v-if="data.direktt_channel_title != '' && data.direktt_channel_id != ''">
            <div v-if="activation_status === null">
              <v-progress-circular :size="30" :width="4" color="info" indeterminate></v-progress-circular>
            </div>
            <div v-if="activation_status === false">
              <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'></v-icon>
              Not activated
              <br></br> <strong>Note: Your WordPress Instance is not activated.<br></br>Activate your
                WordPress instance by entering your API Key and clicking the button Save Settings & Activate WP
              </strong>
            </div>
            <div v-if="activation_status === true">
              <v-icon color="info" icon="mdi-check-bold" size="large" class='rm-4'></v-icon>
              Activated
            </div>
          </td>

          <td v-else>
            <div>
              <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'></v-icon>
              Not activated
              <br></br> <strong>Note: Your WordPress Instance has
                not yet been activated.<br></br>Activate your
                WordPress instance by entering your API Key and clicking the button Save Settings & Activate WP</strong>
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
                <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'
                  v-if="channel_data.count != channel_data.localCount"></v-icon>
                Direktt API: {{ channel_data.count }} / WordPress: {{ channel_data.localCount }}
                <strong v-if="channel_data.count != channel_data.localCount"><br></br>Number of Subscribers in
                  your
                  local database and Direktt API do not match.<br></br>You should synchronize the Subscribers'
                  database.</strong>

              </div>
            </td>
          </tr>

          <tr v-if="activation_status">
            <th scope="row"></th>
            <td>
              <v-btn variant="flat" class="text-none text-caption" color="info" @click="clickSyncUsers"
                :loading="syncing" :disabled="syncing">
                Sync Subscribers' Database
              </v-btn>

              <v-progress-linear v-if="sync_bar_visible" :value="sync_progress" color="info" height="24" striped
                :active="syncing" class="my-4">
                <strong style="color:#fff;">{{ sync_message }}</strong>
              </v-progress-linear>
            </td>
          </tr>

        </template>

      </tbody>
    </table>
    <template v-if="data && data.direktt_channel_title != '' && data.direktt_channel_id != '' && activation_status">
      <p></p>
      <v-divider class="border-opacity-100"></v-divider>
      <p></p>
      <table class="form-table" role="presentation">

        <tbody v-if="data">
          <tr>
            <th scope="row"><label for="blogname">Optional redirect url upon unaturhorized access</label></th>
            <td>
              <input type="text" name="unauthorized_redirect_url" id="unauthorized_redirect_url" size="50"
                placeholder="" v-model="redirect_url">
            </td>
          </tr>
        </tbody>
      </table>
    </template>
    <template v-if="data && data.direktt_channel_title != '' && data.direktt_channel_id != '' && activation_status">

      <p></p>
      <v-divider class="border-opacity-100"></v-divider>
      <p></p>
      <table class="form-table" role="presentation">

        <tbody v-if="data">
          <tr>
            <th scope="row"><label for="pairing_prefix">Prefix for pairing message</label></th>
            <td>
              <input type="text" name="pairing_prefix" id="pairing_prefix" size="50" placeholder="pair"
                v-model="pairing_prefix">
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="blogname">Message template for successful pairing (you can use placeholder
                #wp_user#
                in the template to display WP username just paired with)</label></th>
            <td>
              <v-select :items="templates" v-model="selected_template" label="Select Message Template" width="500"
                return-object clearable></v-select>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="blogname">Reset all pairing codes (Check this box if you want to do so)</label>
            </th>
            <td>
              <input type="checkbox" name="pairing_reset" id="pairing_reset" v-model="reset_pairings">
            </td>
          </tr>
        </tbody>
      </table>

    </template>

    <p></p>

    <v-btn variant="flat" class="text-none text-caption" color="info" @click="clickSaveSettings"
      :loading="save_loading">
      {{ activation_status === false || (data && (data.direktt_channel_title == '' || data.direktt_channel_id == '')) ?
        "Save Settings & Activate WP" : "Save Settings" }}
    </v-btn>

    <v-snackbar v-model="snackbar" :timeout="3000" :color="snackbar_color">
      {{ snackbar_text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>

  </v-card>
</template>

<style></style>