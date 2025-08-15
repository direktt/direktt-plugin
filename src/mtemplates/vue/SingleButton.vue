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

    <v-textarea label="Text above button" variant="outlined" v-model="props.btn.txt" rows="2" no-resize></v-textarea>
    <v-text-field label="Button label" v-model="props.btn.label" variant="outlined"></v-text-field>

    <v-radio-group v-model="props.btn.action.type">
        <v-radio label="Link" value="link"></v-radio>
        <v-radio label="Api" value="api"></v-radio>
        <v-radio label="Chat" value="chat"></v-radio>
        <v-radio label="Profile" value="profile"></v-radio>
    </v-radio-group>
    <div v-if="props.btn.action.type == 'link'" class="pb-4">
        <v-text-field label="Link Url" v-model="btn.action.params.url" variant="outlined"></v-text-field>
        Target:
        <select v-model="btn.action.params.target" style="border-style:solid; border-color: #666; width:200px;">
            <option value="app">App</option>
            <option value="browser">Browser</option>
        </select>
        <RetVars :obj="btn.action.retVars" @update:obj="onUpdateObj"></RetVars>
    </div>
    <div v-if="props.btn.action.type == 'api'" class="pb-4">
        <v-text-field label="Api Action Type" v-model="btn.action.params.actionType" variant="outlined"></v-text-field>
        <RetVars :obj="btn.action.retVars" @update:obj="onUpdateObj"></RetVars>
    </div>
    <div v-if="props.btn.action.type == 'chat'" class="pb-4">
        <v-text-field label="User Subscription Id" v-model="btn.action.params.subscriptionId"
            variant="outlined"></v-text-field>
    </div>
    <div v-if="props.btn.action.type == 'profile'" class="pb-4">
        <v-text-field label="User Subscription Id" v-model="btn.action.params.subscriptionId"
            variant="outlined"></v-text-field>
    </div>


</template>
<style></style>
