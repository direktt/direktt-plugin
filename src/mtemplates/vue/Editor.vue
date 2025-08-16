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

function addMessage(type) {
  const newMsg = { id: uuidv4(), type };
  if (type === "text") {
    newMsg.content = "";
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

    /*newMsg.content = JSON.stringify({
      subtype: "buttons",
      msgObj: [this.emptyRichButton()],
    });*/
  }
  messages.value.push(newMsg);
}

function removeMessage(idx) {
  messages.value.splice(idx, 1);
}

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

function getMessageJSON(msg) {
  // returns pretty-printed JSON for the UI
  let base = JSON.parse(JSON.stringify(msg));
  delete base.id;
  // Patch rich content as JSON

  if (base.content.msgObj) {

    base.content.msgObj.forEach(function (obj) {
      if (obj.action.type === "api") {
        keepOnlyProperties(obj.action.params, ["actionType"])
      }
      if (obj.action.type === "link") {
        keepOnlyProperties(obj.action.params, ["url", "target"])
      }
      if (obj.action.type === "chat") {
        keepOnlyProperties(obj.action.params, ["subscriptionId"])
      }
      if (obj.action.type === "profile") {
        keepOnlyProperties(obj.action.params, ["subscriptionId"])
      }
    });
  }

  return JSON.stringify(base, null, 2);
}

function getFinalTemplate() {
  // Returns JSON for all messages (array). This is what should be sent/saved.
  return JSON.stringify(
    this.messages.map((msg) => {
      let base = { ...msg };
      delete base.id;
      // Rich type content must be stringified JSON
      if (base.type === "rich") {
        // TODO Uraditi delete svih propertija u zavisnosti od tipa koji ne trebaju;
      }
      return base;
    }),
    null,
    2
  );
}

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

function openMediaPickerVideo(index) {
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

</script>

<template>

  <div class="direktt-message-template-builder">
    <div>
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
    </div>

    <draggable v-model="messages" handle=".drag-handle" class="msg-list" item-key="id" :animation="300">
      <template #item="{ element, index }">
        <div class="msg-block">
          <v-row class="pa-4" align="center">
            <v-icon color="info" icon="mdi-arrow-up-down" size="32px" class="drag-handle"></v-icon>
            <strong>{{ element.type.toUpperCase() }}</strong>
            <v-spacer></v-spacer>
            <v-btn variant="flat" class="text-none text-caption" color="info" @click="removeMessage(index)">
              Remove
            </v-btn>
          </v-row>
          <div class="msg-block-inner">
            <!-- Message Fields by Type -->

            <template v-if="element.type === 'text'">
              <div style="width: 100%" class="mb-4">
                <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>
              </div>
            </template>

            <template v-else-if="element.type === 'image'">
              <div style="width: 100%" class="mb-4">
                <v-btn variant="flat" class="text-none text-caption mb-4" @click="openMediaPicker(index)">
                  {{ element.media ? 'Change Image' : 'Select Image' }}</v-btn>
                <v-spacer></v-spacer>
                <img v-if="element.media" :src="element.media" style="height:150px;" class="mb-4">
                <div class="mb-2"><strong>Image Url:</strong> <v-text-field v-model="element.media"
                    variant="outlined"></v-text-field></div>
                <div class="mb-2"><strong>Thumbnail Width:</strong> <v-text-field v-model="element.width"
                    variant="outlined" max-width="200"></v-text-field></div>
                <div class="mb-4"><strong>Thumbnail Height:</strong> <v-text-field v-model="element.height"
                    variant="outlined" max-width="200"></v-text-field></div>
                <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>
              </div>
            </template>

            <template v-else-if="element.type === 'video'">
              <div style="width: 100%" class="mb-4">
                <div class="mb-2"><strong>Video Url:</strong> <v-text-field v-model="element.media"
                    variant="outlined"></v-text-field></div>
                <v-btn variant="flat" class="text-none text-caption mb-4" @click="openMediaPickerVideo(index)">
                  {{ element.thumbnail ? 'Change Thumbnail' : 'Select Thumbnail' }}</v-btn>
                <v-spacer></v-spacer>
                <img v-if="element.thumbnail" :src="element.thumbnail" style="height:150px;" class="mb-4">
                <div class="mb-2"><strong>Thumbnail Url:</strong> <v-text-field v-model="element.thumbnail"
                    variant="outlined"></v-text-field></div>
                <div class="mb-2"><strong>Thumbnail Width:</strong> <v-text-field v-model="element.width"
                    variant="outlined" max-width="200"></v-text-field></div>
                <div class="mb-4"><strong>Thumbnail Height:</strong> <v-text-field v-model="element.height"
                    variant="outlined" max-width="200"></v-text-field></div>
                <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>
              </div>
            </template>

            <template v-else-if="element.type === 'file'">
              <div style="width: 100%" class="mb-4">
                <v-btn variant="flat" class="text-none text-caption mb-4" @click="openMediaPickerFile(index)">
                  {{ element.thumbnail ? 'Change File' : 'Select File' }}</v-btn>
                <v-spacer></v-spacer>
                <div class="mb-2"><strong>File Url:</strong> <v-text-field v-model="element.media"
                    variant="outlined"></v-text-field></div>
                <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>
              </div>
            </template>

            <template v-else-if="element.type === 'rich'">
              <!-- Only 'buttons' rich message supported in this v1. Expandable. -->
              <div style="width: 100%" class="mb-4">
                <v-btn variant="flat" class="text-none text-caption mb-4" color="info" @click="addRichButton(index)">
                  Add Button
                </v-btn>

                <draggable v-model="element.content.msgObj" handle=".drag-btn" group="rich-buttons" item-key="key"
                  :animation="300">

                  <template #item="{ element: btn, index: bidx }">

                    <v-card width="100%" class="pt-4 pl-4 pr-4 mb-4">
                      <v-row class="pt-4 pl-4 pr-4">
                        <v-icon color="info" icon="mdi-arrow-up-down" size="20px" class="drag-btn mr-2"></v-icon>
                        <v-spacer></v-spacer>
                        <v-btn variant="flat" class="text-none text-caption" color="info"
                            @click="removeRichButton(index, bidx)">
                            Remove Button
                          </v-btn>
                      </v-row>
                      <v-row class="pa-4">
                          <SingleButton :btn="btn"></SingleButton>
                      </v-row>
                    </v-card>
                  </template>

                </draggable>

              </div>
            </template>
          </div>
          <v-row>
            <v-col col="6">
              <ItemPreview :item="element"></ItemPreview>
            </v-col>
            <v-col col="6" style="max-width: 50%;">
              <pre class="msg-preview pa-4" style="width: 100%; overflow-x: auto;">{{
                getMessageJSON(element) }}</pre>
            </v-col>
          </v-row>
        </div>
      </template>
    </draggable>

    <h3>Message Template JSON</h3>
    <textarea readonly rows="12" style="width:100%">{{ getFinalTemplate() }}</textarea>
  </div>


</template>

<style scoped>
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
