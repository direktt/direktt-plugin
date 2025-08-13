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
    newMsg.content = JSON.stringify({
      subtype: "buttons",
      msgObj: [this.emptyRichButton()],
    });
  }
  messages.value.push(newMsg);
  console.log(messages.value);
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
      },
      retVars: {},
    },
  };
}

function getRichButtons(msg) {
  // Returns array for 'msgObj' (buttons)
  try {
    let val = JSON.parse(msg.content);
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
    val = JSON.parse(msg.content);
  } catch {
    // fallback: init new
    val = { subtype: "buttons", msgObj: [] };
  }
  if (!Array.isArray(val.msgObj)) {
    val.msgObj = [val.msgObj];
  }
  val.msgObj.push(this.emptyRichButton());
  msg.content = JSON.stringify(val);
}

function removeRichButton(msgIdx, btnIdx) {
  const msg = this.messages[msgIdx];
  let val;
  try {
    val = JSON.parse(msg.content);
  } catch {
    return;
  }
  if (!Array.isArray(val.msgObj)) {
    val.msgObj = [val.msgObj];
  }
  val.msgObj.splice(btnIdx, 1);
  msg.content = JSON.stringify(val);
}

// Two-way-binding for rich buttons fields
function syncRichButton(msg, btnIdx, field, value) {
  let val = JSON.parse(msg.content);
  if (!Array.isArray(val.msgObj)) val.msgObj = [val.msgObj];
  val.msgObj[btnIdx][field] = value;
  msg.content = JSON.stringify(val);
}

// ----------- JSON BUILDERS -------------
function getMessageJSON(msg) {
  // returns pretty-printed JSON for the UI
  let base = { ...msg };
  delete base.id;
  // Patch rich content as JSON
  if (msg.type === "rich") {
    try {
      base.content = JSON.parse(base.content);
    } catch { }
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
      if (base.type === "rich" && typeof base.content !== "string") {
        base.content = JSON.stringify(base.content);
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
    if (attachment.sizes && attachment.sizes.medium) {
      messages.value[index].thumbnail = attachment.sizes.medium.url
      messages.value[index].width = attachment.sizes.medium.width
      messages.value[index].height = attachment.sizes.medium.height
      // thumbnailUrl.value = attachment.sizes.medium.url
    } else {
      messages.value[index].thumbnail = attachment.url
      //thumbnailUrl.value = attachment.url
    }
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

    <draggable v-model="messages" handle=".drag-handle" class="msg-list" item-key="id">
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
              <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>
            </template>

            <template v-else-if="element.type === 'image'">

              <v-btn variant="flat" class="text-none text-caption mb-4" @click="openMediaPicker(index)">
                {{ element.media ? 'Change Image' : 'Select Image' }}</v-btn>

              <v-row class="pa-4 mb-3" v-if="element.thumbnail">
                <div class="mr-4">
                  <img :src="element.thumbnail" style="height:100px;">
                </div>
                <div>
                  <div class="pa-1"><strong>Image Url:</strong> {{ element.media }}</div>
                  <div class="pa-1"><strong>Thumbnail Width:</strong> {{ element.width }}px</div>
                  <div class="pa-1"><strong>Thumbnail Height:</strong> {{ element.height }}px</div>
                  <div class="pa-1"><strong>Thumbnail Url:</strong> {{ element.thumbnail }}px</div>
                </div>
              </v-row>

              <v-textarea label="Message content" variant="outlined" v-model="element.content"></v-textarea>

            </template>

            <template v-else-if="element.type === 'video'">
              <input v-model="element.content" placeholder="Video description" />
              <input v-model="element.media" placeholder="Video URL" />
              <input v-model.number="element.width" placeholder="Width" type="number" />
              <input v-model.number="element.height" placeholder="Height" type="number" />
              <input v-model="element.thumbnail" placeholder="Thumbnail URL (optional)" />
            </template>
            <template v-else-if="element.type === 'file'">
              <input v-model="element.content" placeholder="File description" />
              <input v-model="element.media" placeholder="File URL" />
            </template>
            <template v-else-if="element.type === 'rich'">
              <!-- Only 'buttons' rich message supported in this v1. Expandable. -->
              <div>
                <strong>Buttons</strong>
                <button @click="addRichButton(index)">Add Button</button>
                <div v-for="(btn, bidx) in getRichButtons(element)" :key="bidx" class="rich-btn-editor">
                  <input v-model="btn.txt" placeholder="Button text (can use #displayName#, ...)" />
                  <input v-model="btn.label" placeholder="Button label" />
                  <input v-model="btn.action.params.url" placeholder="Action URL" />
                  <select v-model="btn.action.params.target">
                    <option value="app">app (in-app)</option>
                    <option value="_blank">Browser/tab (_blank)</option>
                  </select>
                  <button @click="removeRichButton(index, bidx)">Remove Button</button>
                </div>
              </div>
            </template>
          </div>
          <!-- Preview JSON for each message -->
          <pre class="msg-preview">{{ getMessageJSON(element) }}</pre>
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

.drag-handle {
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
