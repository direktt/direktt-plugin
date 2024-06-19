<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useDirekttStore()

const nonce = ref(direktt_settings_object.nonce)

const api_key = ref('')
const save_loading = ref(false)

const snackbar = ref(false);

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['direktt-settings'],
  queryFn: getSettings
})

const mutation = useMutation({
  mutationFn: saveSettings,
  onSuccess: async () => {
    // Invalidate and refetch
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] })
    save_loading.value = false
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
    console.error(error);
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

  return ret
}

function clickSaveSettings() {
  save_loading.value = true
  mutation.mutate({
    api_key: api_key.value
  })
}

async function saveSettings(obj) {

  obj.action = "direktt_save_settings"
  obj.nonce = nonce.value

  const response = await doAjax(obj)
  console.log(response.data)
}

const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {
  console.log(nonce.value)
})

</script>

<template>

  <h1>Direktt Settings</h1>

  <v-card class="pa-4 mr-4">

    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="blogname">Direktt API Key</label></th>
          <td>
            <input type="text" name="direkttapikey" id="direkttapikey" size="50" placeholder="" v-model="api_key">
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="blogname">Activation status:</label></th>
          <td>
            Not activated
          </td>

        </tr>
      </tbody>
    </table>
    <p></p>

    <v-btn variant="flat" class="text-none text-caption" color="#2271b1" @click="clickSaveSettings"
      :loading="save_loading">
      Save API key and (re)activate instance
    </v-btn>

    <v-snackbar v-model="snackbar" :timeout="2000" color="#2271b1">
      Direktt API Key Saved and Validated

      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>

  </v-card>
</template>

<style></style>