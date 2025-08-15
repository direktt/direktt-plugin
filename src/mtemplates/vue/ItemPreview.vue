<script setup>
import {
    computed,
    onMounted,
} from "vue";

import { getFileExtension, getFilenameFromUrl, normalizeUrl } from "./utils.js"

const props = defineProps(["item"]);

let scrwidth = Math.round(500 * 0.575);
let scrheight = 0;

if (props.item.type == "picture" || props.item.type == "image" || props.item.type == "video") {
    scrheight = Math.round(scrwidth * props.item.height / props.item.width);
}

function tryParseJSONObject(jsonString) {
    try {
        var o = JSON.parse(jsonString);
        if (o && typeof o === "object") {
            return o;
        }
    }
    catch (e) { }

    return false;
};

onMounted(() => {
    scrwidth = 500 * 0.6;
});

const buttonClasses = computed(() => ({
    active: props.actionActive.includes(String(props.item.id))
}))

const contentType = computed(() => {

    if (props.item.type == "text") {
        return 'text';
    } else if (props.item.type == "picture" || props.item.type == "image") {
        return 'picture';
    } else if (props.item.type == "video") {
        return 'video';
    } else if (props.item.type == "file") {
        return 'file';
    } else if (props.item.type == "rich") {
        return 'rich';
    }
});

const parsedParts = computed(() => {

    const urlRegex = /https?:\/\/(?:www\.)?[^\s/$.?#].[^\s]*|www\.[^\s/$.?#].[^\s]*/ig;
    const parts = [];
    let lastIndex = 0;
    let match;

    const content = props.item.content;

    while ((match = urlRegex.exec(content)) !== null) {
        if (match.index > lastIndex) {
            // Non-URL text
            parts.push({
                type: 'text',
                value: content.slice(lastIndex, match.index)
            });
        }
        // URL part
        parts.push({
            type: 'url',
            value: match[0],
            normalized: normalizeUrl(match[0])
        });
        lastIndex = match.index + match[0].length;
    }
    if (lastIndex < content.length) {
        // Remaining text
        parts.push({
            type: 'text',
            value: content.slice(lastIndex)
        });
    }
    return parts;
})

function extractFileName(url) {
    try {
        if (!url || typeof url !== 'string') {
            throw new Error('Invalid URL: URL is empty or not a string.');
        }
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        if (!pathname) {
            throw new Error('Invalid URL: Pathname is empty.');
        }
        const fileName = pathname.split('/').pop();
        if (!fileName) {
            throw new Error('Invalid URL: File name could not be extracted.');
        }
        return fileName;
    } catch (error) {
        console.error('Error extracting file name:', error.message);
        return 'File';
    }
}

</script>

<template>
    <div class="ItemPreview">
        <div class="itemContent">
            <div class="item">
                <div class="contentPieces">
                    <div>
                        <template v-if="contentType == 'text'">
                            <div class="text">
                                <span v-for="(part, idx) in parsedParts" :key="idx">
                                    <template v-if="part.type === 'url'">
                                        <span class="url">
                                            {{ part.value }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <template v-for="(line, lineIdx) in part.value.split(/\r?\n/)" :key="lineIdx">
                                            {{ line }}<br v-if="lineIdx !== part.value.split(/\r?\n/).length - 1" />
                                        </template>
                                    </template>
                                </span>
                            </div>
                        </template>

                        <template v-else-if="contentType == 'rich'">
                            <div v-if="props.item.content.subtype == 'buttons'" class="buttons">
                                <div class="buttonWrapper" v-for="msgit in props.item.content.msgObj">
                                    <div class="buttonText">
                                        <template v-for="(line, lineIdx) in msgit.txt.split(/\r?\n/)" :key="lineIdx">
                                            {{ line }}<br v-if="lineIdx !== msgit.txt.split(/\r?\n/).length - 1" />
                                        </template>
                                    </div>
                                    <button>
                                        {{ msgit.label }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-else-if="contentType == 'picture'">
                            <div class="imageWrapper">
                                <img v-if="props.item.media != null && props.item.media != ''" :src="props.item.media"
                                    :alt="props.item.content"
                                    :height="Math.round(scrwidth * props.item.height / props.item.width)"
                                    :width="scrwidth" />
                                <div v-else class='skeleton'
                                    :style="{ height: scrheight + 'px', width: scrwidth + 'px' }">
                                    <div class="loader"></div>
                                </div>
                            </div>
                            <div class="text">
                                <span v-for="(part, idx) in parsedParts" :key="idx">
                                    <template v-if="part.type === 'url'">
                                        <span class="url" @click.stop="openUrlFromText(part.normalized)">
                                            {{ part.value }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <template v-for="(line, lineIdx) in part.value.split(/\r?\n/)" :key="lineIdx">
                                            {{ line }}<br v-if="lineIdx !== part.value.split(/\r?\n/).length - 1" />
                                        </template>
                                    </template>
                                </span>
                            </div>
                        </template>

                        <template v-else-if="contentType == 'video'">
                            <div class="videoWrapper">
                                <img v-if="props.item.thumbnail != null && props.item.thumbnail != ''" :src="props.item.thumbnail"
                                    :height="Math.round(scrwidth * props.item.height / props.item.width)"
                                    :width="scrwidth" />
                                <div v-else class='skeleton'
                                    :style="{ height: scrheight + 'px', width: scrwidth + 'px' }">
                                    <div class="loader"></div>
                                </div>
                            </div>
                            <div class="text">
                                <span v-for="(part, idx) in parsedParts" :key="idx">
                                    <template v-if="part.type === 'url'">
                                        <span class="url" @click.stop="openUrlFromText(part.normalized)">
                                            {{ part.value }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <template v-for="(line, lineIdx) in part.value.split(/\r?\n/)" :key="lineIdx">
                                            {{ line }}<br v-if="lineIdx !== part.value.split(/\r?\n/).length - 1" />
                                        </template>
                                    </template>
                                </span>
                            </div>
                        </template>

                        <template v-else-if="contentType == 'file'">
                            <div class="fileWrapper">
                                <div v-if="props.item.media == null || props.item.media == ''"
                                    class='skeleton' :style="{ height: scrheight + 'px', width: scrwidth + 'px' }">
                                    <div class="loader"></div>
                                </div>
                                <template v-else class="fileWraper">
                                    <div :class="'icoFile' + ' ' + getFileExtension(props.item.media)"></div>
                                    <div class="fileMedia" v-if="props.item.media && props.item.media != ''">
                                        {{ getFilenameFromUrl(props.item.media) }}
                                    </div>
                                </template>
                            </div>
                             <div class="text">
                                <span v-for="(part, idx) in parsedParts" :key="idx">
                                    <template v-if="part.type === 'url'">
                                        <span class="url" @click.stop="openUrlFromText(part.normalized)">
                                            {{ part.value }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <template v-for="(line, lineIdx) in part.value.split(/\r?\n/)" :key="lineIdx">
                                            {{ line }}<br v-if="lineIdx !== part.value.split(/\r?\n/).length - 1" />
                                        </template>
                                    </template>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style></style>
