<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import QRCodeStyling, {
    DrawType,
    TypeNumber,
    Mode,
    ErrorCorrectionLevel,
    DotType,
    CornerSquareType,
    CornerDotType,
    Extension
} from 'qr-code-styling';

import tinycolor from "tinycolor2";

const props = defineProps(["qrCodeData", "qrCodeLogoUrl", "qrCodeColor", "qrCodeBckgColor"]);

const qrCodeRef = ref(null);

const options = reactive({
    width: 300,
    height: 300,
    type: 'svg',
    data: props.qrCodeData,
    image: props.qrCodeLogoUrl,
    margin: 10,
    qrOptions: {
        typeNumber: 0,
        mode: 'Byte',
        errorCorrectionLevel: 'Q'
    },
    imageOptions: {
        hideBackgroundDots: true,
        imageSize: 0.5,
        margin: 10,
        crossOrigin: 'anonymous',
    },
    dotsOptions: {
        color: props.qrCodeColor,
        // gradient: {
        //   type: 'linear',
        //   rotation: 0,
        //   colorStops: [{ offset: 0, color: '#8688B2' }, { offset: 1, color: '#77779C' }]
        // },
        type: 'rounded'
    },
    backgroundOptions: {
        color: props.qrCodeBckgColor,
        // gradient: {
        //   type: 'linear',
        //   rotation: 0,
        //   colorStops: [{ offset: 0, color: '#ededff' }, { offset: 1, color: '#e6e7ff' }]
        // },
    },
    cornersSquareOptions: {
        color: shiftColors(props.qrCodeColor),
        type: 'extra-rounded',
        // gradient: {
        //   type: 'linear',
        //   rotation: 180,
        //   colorStops: [{ offset: 0, color: '#25456e' }, { offset: 1, color: '#4267b2' }]
        // },
    },
    cornersDotOptions: {
        color: shiftColors(props.qrCodeColor),
        type: 'dot',
        // gradient: {
        //   type: 'linear',
        //   rotation: 180,
        //   colorStops: [{ offset: 0, color: '#00266e' }, { offset: 1, color: '#4060b3' }]
        // },
    }
});
const extension = ref('svg');

// --- QR Instance ---
const qrCode = new QRCodeStyling(options);

// --- Watch for Data Change ---
watch(
    [
        () => props.qrCodeLogoUrl,
        () => props.qrCodeColor,
        () => props.qrCodeBckgColor,
    ],
    (newValue) => {
        options.image = props.qrCodeLogoUrl
        options.dotsOptions.color = props.qrCodeColor
        options.backgroundOptions.color = props.qrCodeBckgColor;
        options.cornersSquareOptions.color = shiftColors(props.qrCodeColor);
        options.cornersDotOptions.color = shiftColors(props.qrCodeColor); 
        qrCode.update(options);
    }
);

// --- Mount QR Code to DOM ---
onMounted(() => {
    if (qrCodeRef.value) {
        qrCode.append(qrCodeRef.value);
    }
});

// --- Download Function ---
function download() {
    qrCode.download({ extension: extension.value });
}

function shiftColors(baseColor) {
    let inputColor = baseColor;
    if (!inputColor) {
        inputColor = "#000000"
    }

    const tc = tinycolor(inputColor);
    if (tc.isDark()) {
        // If dark, lighten by 10%
        return tc.lighten(30).toHexString();
    } else {
        // If light, darken by 10%
        return tc.darken(30).toHexString();
    }
}

</script>

<template>
    <div class="hello">
        <div id="qr-code-view" ref="qrCodeRef"></div>
        <div id="qr-code-download">
            <select v-model="extension">
                <option value="svg">SVG</option>
                <option value="png">PNG</option>
                <option value="jpeg">JPEG</option>
                <option value="webp">WEBP</option>
            </select>
            <button @click="download" class="button">Download</button>
        </div>
    </div>
</template>