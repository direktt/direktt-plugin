document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.direktt-qr-paring-code');

    if (typeof QRCode === 'undefined' || !container) return;

    const pairCode = container.dataset.pairCode;
    const sizeInPx = container.dataset.sizeInPx || 200;

    const payload = {
        action: {
            type: "api",
            params: {
                actionType: "pair_code"
            },
            retVars: {
                pairCode: pairCode
            }
        }
    };

    const jsonString = JSON.stringify(payload);

    new QRCode(document.getElementById("qrcode"), {
        text: jsonString,
        width: sizeInPx,
        height: sizeInPx,
    });
});
