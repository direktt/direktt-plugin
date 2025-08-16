<script setup>
import {
    ref,
    computed,
    onMounted,
} from "vue";

import RetVars from "./RetVars.vue";

const props = defineProps(["btn"]);

const hasRetVars = ref(false);

function onUpdateObj(newObj) {
    // Mutate 'data' reactively (replace all keys)
    Object.keys(props.btn.action.retVars).forEach(k => delete props.btn.action.retVars[k]);
    Object.entries(newObj).forEach(([k, v]) => {
        props.btn.action.retVars[k] = v;
    });
}

</script>

<template>
    <div style="width:100%">
        <div class="mb-2"><strong>Button label:</strong> <v-text-field v-model="props.btn.label"
                variant="outlined"></v-text-field></div>
        <div class="mb-2">
            <strong>Text above button:</strong>
            <v-textarea variant="outlined" v-model="props.btn.txt" rows="2" no-resize class="mb-2"></v-textarea>
        </div>
        <div class="mb-4"><strong>Action Type:</strong> <v-spacer></v-spacer>
            <select v-model="props.btn.action.type" style="border-style:solid; border-color: #666; width:200px;">
                <option value="link">Link</option>
                <option value="api">Api</option>
                <option value="chat">Chat</option>
                <option value="profile">Profile</option>
            </select>
        </div>

        <div v-if="props.btn.action.type == 'link'" class="pb-4">
            <div class="mb-2"><strong>Link Url:</strong> <v-text-field v-model="btn.action.params.url"
                    variant="outlined"></v-text-field></div>
            <div class="mb-4"><strong>Target:</strong> <v-spacer></v-spacer>
                <select v-model="btn.action.params.target" style="border-style:solid; border-color: #666; width:200px;">
                    <option value="app">App</option>
                    <option value="browser">Browser</option>
                </select>
            </div>
            <div class="mb-2">
                <RetVars :obj="btn.action.retVars" @update:obj="onUpdateObj"></RetVars>
            </div>
        </div>
    </div>

    <div v-if="props.btn.action.type == 'api'" class="pb-4">
        <div class="mb-4"><strong>Api Action Type:</strong> <v-text-field v-model="btn.action.params.actionType"
                variant="outlined"></v-text-field></div>
        <div class="mb-2">
            <RetVars :obj="btn.action.retVars" @update:obj="onUpdateObj"></RetVars>
        </div>
    </div>

    <div v-if="props.btn.action.type == 'chat'" class="pb-4">
         <div class="mb-2"><strong>User Subscription Id:</strong> <v-text-field v-model="btn.action.params.subscriptionId"
                variant="outlined"></v-text-field></div>
    </div>
    <div v-if="props.btn.action.type == 'profile'" class="pb-4">
        <div class="mb-2"><strong>User Subscription Id:</strong> <v-text-field v-model="btn.action.params.subscriptionId"
                variant="outlined"></v-text-field></div>
    </div>


</template>
<style></style>
