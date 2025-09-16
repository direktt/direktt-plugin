<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted,
  onBeforeUnmount,
  nextTick,
} from "vue";
import { v4 as uuidv4 } from "uuid";
import draggable from 'vuedraggable'

import ItemPreview from "./ItemPreview.vue";
import SingleButton from "./SingleButton.vue";

const messages = ref([]);
const activeMessageIndex = ref(0);

let externalInputField = null

function addMessage(type) {
  const newMsg = { id: uuidv4(), type };
  if (type === "text") {
    newMsg.content = "Hi this is your text message. Change this text!";
  }
  if (type === "image" || type === "video") {
    Object.assign(newMsg, {
      content: "",
      media: "",
      width: null,
      height: null,
      thumbnail: "",
    });
  }
  if (type === "file") {
    Object.assign(newMsg, {
      content: "",
      media: "",
    });
  }
  if (type === "rich") {
    // Default: Buttons rich message with empty button
    Object.assign(newMsg, {
      content: {
        subtype: "buttons",
        msgObj: [emptyRichButton()],
      }
    });
  }
  messages.value.push(newMsg);
  activeMessageIndex.value = messages.value.length - 1;
}

function removeMessage(idx) {
  if (!window.confirm('Are you sure you want to remove this message? This cannot be undone.')) {
    return;
  }
  messages.value.splice(idx, 1);

  if (messages.value.length === 0) {
    activeMessageIndex.value = -1;
  } else if (idx === messages.value.length) {
    // Removed last item, select previous
    activeMessageIndex.value = messages.value.length - 1;
  } else if (activeMessageIndex.value > idx) {
    activeMessageIndex.value--;
  } else if (activeMessageIndex.value === idx) {
    activeMessageIndex.value = Math.max(0, Math.min(idx, messages.value.length - 1));
  }

}

// When messages reordered, keep activeMessageIndex in sync with id
function onMessagesReordered(event) {
  // Active index will generally still point to the same message obj
  // Optionally improve this with more robust handling if needed
}

// Tabs for right-panel
const editTab = ref('properties');

const activeMessage = computed(() => {
  if (activeMessageIndex.value >= 0 && activeMessageIndex.value < messages.value.length) {
    return messages.value[activeMessageIndex.value];
  }
  return null;
});

function emptyRichButton() {
  return {
    txt: "",
    label: "",
    action: {
      type: "link",
      params: {
        url: "",
        target: "app",
        subscriptionId: "",
        actionType: ""
      },
      retVars: {},
    },
  };
}

function getRichButtons(msg) {
  // Returns array for 'msgObj' (buttons)
  try {
    //let val = JSON.parse(msg.content);
    let val = msg.content;
    if (val.subtype === "buttons") {
      // msgObj can be array or object
      return Array.isArray(val.msgObj)
        ? val.msgObj
        : [val.msgObj];
    }
    return [];
  } catch {
    return [];
  }
}

function addRichButton(msgIdx) {
  const msg = this.messages[msgIdx];
  let val;
  try {
    //val = JSON.parse(msg.content);
    val = msg.content;
  } catch {
    // fallback: init new
    val = { subtype: "buttons", msgObj: [] };
  }
  if (!Array.isArray(val.msgObj)) {
    val.msgObj = [val.msgObj];
  }
  val.msgObj.push(this.emptyRichButton());
  msg.content = val;
  // msg.content = JSON.stringify(val);
}

function removeRichButton(msgIdx, btnIdx) {
  const msg = this.messages[msgIdx];
  let val;
  try {
    val = msg.content;
  } catch {
    return;
  }
  if (!Array.isArray(val.msgObj)) {
    val.msgObj = [val.msgObj];
  }
  val.msgObj.splice(btnIdx, 1);
  msg.content = val;
  // msg.content = JSON.stringify(val);
}

// Two-way-binding for rich buttons fields
function syncRichButton(msg, btnIdx, field, value) {
  let val = JSON.parse(msg.content);
  if (!Array.isArray(val.msgObj)) val.msgObj = [val.msgObj];
  val.msgObj[btnIdx][field] = value;
  msg.content = JSON.stringify(val);
}

// ----------- JSON BUILDERS -------------

function keepOnlyProperties(obj, propertiesToKeep) {
  Object.keys(obj).forEach(key => {
    if (!propertiesToKeep.includes(key)) {
      delete obj[key];
    }
  });
}

const getMessageJSON = computed(() => {
  let base = JSON.parse(JSON.stringify(activeMessage.value));
  delete base.id;
  // Patch rich content as JSON

  if (base.content.msgObj) {

    base.content.msgObj.forEach(function (obj) {
      if (obj.action.type === "api") {
        keepOnlyProperties(obj.action.params, ["actionType", "successMessage"])
      }
      if (obj.action.type === "link") {
        keepOnlyProperties(obj.action.params, ["url", "target"])
      }
      if (obj.action.type === "chat") {
        keepOnlyProperties(obj.action.params, ["subscriptionId"])
        obj.action.retVars = {}
      }
      if (obj.action.type === "profile") {
        keepOnlyProperties(obj.action.params, ["subscriptionId"])
        obj.action.retVars = {}
      }
    });
  }

  return JSON.stringify(base, null, 2);
})

const getFinalTemplate = computed(() => {

  return JSON.stringify(
    messages.value.map((msg) => {

      let base = JSON.parse(JSON.stringify(msg));

      delete base.id;
      // Rich type content must be stringified JSON
      if (base.content.msgObj) {

        base.content.msgObj.forEach(function (obj) {
          if (obj.action.type === "api") {
            keepOnlyProperties(obj.action.params, ["actionType", "successMessage"])
          }
          if (obj.action.type === "link") {
            keepOnlyProperties(obj.action.params, ["url", "target"])
          }
          if (obj.action.type === "chat") {
            keepOnlyProperties(obj.action.params, ["subscriptionId"])
            obj.action.retVars = {}
          }
          if (obj.action.type === "profile") {
            keepOnlyProperties(obj.action.params, ["subscriptionId"])
            obj.action.retVars = {}
          }
        });
      }

      return base;

    }),
    null,
    2
  );
})

watch(getFinalTemplate, (newVal, oldVal) => {
  if (externalInputField) externalInputField.value = newVal
})

function openMediaPicker(index) {
  if (!window.wp || !window.wp.media) {
    alert('WordPress media library is not available on this page.')
    return
  }

  const frame = window.wp.media({
    title: 'Select or Upload Image',
    library: { type: 'image' },
    button: { text: 'Use this image' },
    multiple: false,
  })

  frame.on('select', () => {
    const attachment = frame.state().get('selection').first().toJSON()

    messages.value[index].media = attachment.url
    //imageUrl.value = attachment.url
    messages.value[index].width = attachment.width || ''
    //width.value = attachment.width || ''
    messages.value[index].height = attachment.height || ''
    //height.value = attachment.height || ''

    // Try well-known thumbnail sizes (you can adjust as needed)
    /*if (attachment.sizes && attachment.sizes.medium) {
      messages.value[index].thumbnail = attachment.sizes.medium.url
      messages.value[index].width = attachment.sizes.medium.width
      messages.value[index].height = attachment.sizes.medium.height
      // thumbnailUrl.value = attachment.sizes.medium.url
    } else {
    messages.value[index].thumbnail = attachment.url
      //thumbnailUrl.value = attachment.url
    //}*/
  })

  frame.open()
}

function openMediaPickerVideoThumb(index) {
  if (!window.wp || !window.wp.media) {
    alert('WordPress media library is not available on this page.')
    return
  }

  const frame = window.wp.media({
    title: 'Select or Upload Image',
    library: { type: 'image' },
    button: { text: 'Use this image' },
    multiple: false,
  })

  frame.on('select', () => {
    const attachment = frame.state().get('selection').first().toJSON()

    messages.value[index].thumbnail = attachment.url
    messages.value[index].width = attachment.width || ''
    messages.value[index].height = attachment.height || ''

  })

  frame.open()
}

function openMediaPickerVideo(index) {
  if (!window.wp || !window.wp.media) {
    alert('WordPress media library is not available on this page.')
    return
  }

  const frame = window.wp.media({
    title: 'Select or Upload Video',
    library: { type: ['video'] },
    button: { text: 'Use this Video' },
    multiple: false,
  })

  frame.on('select', () => {
    const attachment = frame.state().get('selection').first().toJSON()
    messages.value[index].media = attachment.url
  })

  frame.open()
}

function openMediaPickerFile(index) {
  if (!window.wp || !window.wp.media) {
    alert('WordPress media library is not available on this page.')
    return
  }

  const frame = window.wp.media({
    title: 'Select or Upload File',
    library: { type: ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'audio'] },
    button: { text: 'Use this File' },
    multiple: false,
  })

  frame.on('select', () => {
    const attachment = frame.state().get('selection').first().toJSON()
    messages.value[index].media = attachment.url
  })

  frame.open()
}

onMounted(() => {
  externalInputField = document.getElementById('direktt_mt_json')

  console.log(externalInputField.value)

  if (externalInputField) messages.value = JSON.parse(externalInputField.value)

  //if (externalInputField) externalInputField.value = formatted.value

  // Sync INCOMING change: Optionally, listen to manual changes of external field
  //externalInputField?.addEventListener('input', handleExternalInput)
})

</script>

<template>

  <div class="direktt-message-template-builder">
    <v-row>
      <v-col cols="6">
        <v-row class="pa-4">
          <v-btn variant="flat" class="text-none text-caption" color="info" @click="addMessage('text')">
            Add Text
          </v-btn>
          <v-btn variant="flat" class="text-none text-caption" color="info" @click="addMessage('image')">
            Add Image
          </v-btn>
          <v-btn variant="flat" class="text-none text-caption" color="info" @click="addMessage('video')">
            Add Video
          </v-btn>
          <v-btn variant="flat" class="text-none text-caption" color="info" @click="addMessage('file')">
            Add File
          </v-btn>
          <v-btn variant="flat" class="text-none text-caption" color="info" @click="addMessage('rich')">
            Add Interactive Message
          </v-btn>
        </v-row>
        <v-row justify="center">
          <draggable v-model="messages" handle=".drag-handle" item-key="id" class="msg-list" :animation="300"
            @end="onMessagesReordered">
            <template #item="{ element, index }">
              <div class="preview-item" :class="{ active: index === activeMessageIndex }"
                @click="activeMessageIndex = index">
                <v-row align="center" no-gutters>
                  <v-icon color="info" icon="mdi-arrow-up-down" size="22" class="drag-handle mr-2"></v-icon>
                  <ItemPreview :item="element" />
                </v-row>
              </div>
            </template>
          </draggable>
        </v-row>
      </v-col>
      <v-col cols="6">

        <div class="details-panel">
          <template v-if="activeMessage">
            <v-tabs v-model="editTab" bg-color="primary" class="mb-4">
              <v-tab value="properties">Properties</v-tab>
              <v-tab value="json">JSON</v-tab>
            </v-tabs>

            <v-tabs-window v-model="editTab">
              <!-- Properties EDIT TAB -->
              <v-tabs-window-item value="properties">
                <v-row class="pa-4">
                  <h2><strong>Message type: {{ activeMessage.type }}</strong></h2>
                  <v-spacer></v-spacer>
                  <v-btn variant="flat" color="info" class="text-none text-caption"
                    @click="removeMessage(activeMessageIndex)">
                    Remove Message
                  </v-btn>
                </v-row>
                <div class="msg-block-inner" style="padding:0.5em 0;">
                  <template v-if="activeMessage.type === 'text'">
                    <v-textarea label="Message content" variant="outlined" v-model="activeMessage.content"></v-textarea>
                  </template>
                  <template v-else-if="activeMessage.type === 'image'">
                    <v-btn variant="flat" color="info" class="text-none text-caption mb-4"
                      @click="openMediaPicker(activeMessageIndex)">
                      {{ activeMessage.media ? 'Change Image' : 'Select Image' }}</v-btn>
                    <v-spacer></v-spacer>
                    <img v-if="activeMessage.media" :src="activeMessage.media" style="height:150px;" class="mb-4">
                    <div class="mb-2"><strong>Image Url:</strong> <v-text-field v-model="activeMessage.media"
                        variant="outlined"></v-text-field></div>
                    <div class="mb-2"><strong>Thumbnail Width:</strong> <v-text-field v-model="activeMessage.width"
                        variant="outlined"></v-text-field></div>
                    <div class="mb-4"><strong>Thumbnail Height:</strong> <v-text-field v-model="activeMessage.height"
                        variant="outlined"></v-text-field></div>
                    <v-textarea label="Message content" variant="outlined" v-model="activeMessage.content"></v-textarea>
                  </template>
                  <template v-else-if="activeMessage.type === 'video'">
                    <v-row class="pl-4 pb-4 pr-4" align="end">
                      <div class="mr-4" style="width:75%"><strong>Video Url:</strong> <v-text-field v-model="activeMessage.media" 
                        variant="outlined"></v-text-field></div>
                      <v-btn variant="flat" color="info" class="text-none text-caption mb-0"
                        @click="openMediaPickerVideo(activeMessageIndex)">
                        {{ activeMessage.media ? 'Change Video' : 'Select Video' }}</v-btn>
                    </v-row>
                    <v-btn variant="flat" color="info" class="text-none text-caption mb-4 mt-4"
                      @click="openMediaPickerVideoThumb(activeMessageIndex)">
                      {{ activeMessage.thumbnail ? 'Change Thumbnail' : 'Select Thumbnail' }}</v-btn>
                    <v-spacer></v-spacer>
                    <img v-if="activeMessage.thumbnail" :src="activeMessage.thumbnail" style="height:150px;"
                      class="mb-4">
                    <div class="mb-2"><strong>Thumbnail Url:</strong> <v-text-field v-model="activeMessage.thumbnail"
                        variant="outlined"></v-text-field></div>
                    <div class="mb-2"><strong>Thumbnail Width:</strong> <v-text-field v-model="activeMessage.width"
                        variant="outlined"></v-text-field></div>
                    <div class="mb-4"><strong>Thumbnail Height:</strong> <v-text-field v-model="activeMessage.height"
                        variant="outlined"></v-text-field></div>
                    <v-textarea label="Message content" variant="outlined" v-model="activeMessage.content"></v-textarea>
                  </template>
                  <template v-else-if="activeMessage.type === 'file'">
                    <v-row class="pl-4 pb-4 pr-4" align="end">
                      <div class="mr-4" style="width:75%"><strong>File Url:</strong> <v-text-field
                          v-model="activeMessage.media" variant="outlined"></v-text-field></div>

                      <v-btn variant="flat" color="info" class="text-none text-caption mb-0"
                        @click="openMediaPickerFile(activeMessageIndex)">
                        {{ activeMessage.thumbnail ? 'Change File' : 'Select File' }}</v-btn>
                    </v-row>
                    <v-textarea class="mt-4" label="Message content" variant="outlined"
                      v-model="activeMessage.content"></v-textarea>
                  </template>
                  <!-- RICH MESSAGE -->
                  <template v-else-if="activeMessage.type === 'rich'">
                    <v-btn variant="flat" class="text-none text-caption mb-4" color="info"
                      @click="addRichButton(activeMessageIndex)">
                      Add Button
                    </v-btn>
                    <draggable v-model="activeMessage.content.msgObj" handle=".drag-btn" group="rich-buttons"
                      item-key="key" :animation="300">
                      <template #item="{ element: btn, index: bidx }">
                        <v-card width="100%" class="pt-4 pl-4 pr-4 mb-4 singleButton">
                          <v-row class="pt-4 pl-4 pr-4">
                            <v-icon color="info" icon="mdi-arrow-up-down" size="20px" class="drag-btn mr-2"></v-icon>
                            <v-spacer></v-spacer>
                            <v-btn variant="flat" class="text-none text-caption" color="info"
                              @click="removeRichButton(activeMessageIndex, bidx)">
                              Remove Button
                            </v-btn>
                          </v-row>
                          <v-row class="pa-4">
                            <SingleButton :btn="btn"></SingleButton>
                          </v-row>
                        </v-card>
                      </template>
                    </draggable>
                  </template>

                </div>
              </v-tabs-window-item>

              <!-- JSON TAB -->
              <v-tabs-window-item value="json">
                <pre class="msg-preview pa-4" style="width: 100%; max-width: 100%; overflow-x: auto;">{{
                  getMessageJSON }}</pre>
              </v-tabs-window-item>
            </v-tabs-window>

          </template>
          <template v-else>
            <div class="empty-state pa-10" style="text-align: center;">
              No message selected.<br>
              Click a message preview to view and edit its details.
            </div>
          </template>
        </div>

      </v-col>
    </v-row>

  </div>
</template>

<style scoped>
.preview-item {
  cursor: pointer;
  background: #f6f7fa;
  border: 1px solid #ccf;
  padding: 10px 8px;
  margin-bottom: 6px;
  transition: box-shadow 0.2s, background 0.2s;
}

.preview-item.active {
  background: #dff2fd;
  border-color: #42a5f5;
  box-shadow: 0 0 2px 1px #42a5f577;
}

.details-panel {
  flex: 1;
  min-width: 0;
}

.msg-preview {
  background: #eef5ff;
}


.direktt-message-template-builder {
  margin: 0 auto;
  font-family: Arial, sans-serif;
}

.msg-block {
  border: 1px solid #ccf;
  padding: 0.7em;
  margin: 1em 0;
  background: #f6f7fa;
  position: relative;
}

.drag-handle,
.drag-btn {
  cursor: move;
  margin-right: 0.4em;
  color: #7090c2;
  font-size: 1.2em;
  vertical-align: middle;
}

.msg-block-inner {
  margin: 0.5em 0;
}

.msg-list {
  margin: 0.5em 0 2em 0;
}

button {
  margin-right: 10px;
  margin-bottom: 5px;
}

.msg-preview {
  background: #eef5ff;
}
</style>
