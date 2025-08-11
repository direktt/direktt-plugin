'use strict'

document.addEventListener('DOMContentLoaded', function () {

    const nonceEl = document.getElementById('templateNonce');
    let templateIDEl = document.getElementById('templateID');
    const nonce = nonceEl ? nonceEl.value : null;
    const inputEl = document.getElementById('autoComplete');

    const sendBtn = document.getElementById('sendMessageBtn');

    let selectedTemplate = null;
    let autoCompleteJS = null;

    let availableTemplates = [];

    var data = new FormData();
    data.append('action', 'direktt_get_mtemplates_profile_message');
    data.append('nonce', nonce);
    data.append('post_id', direktt_public.direktt_post_id);

    fetch(direktt_public.direktt_ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json())
        .then(result => {
            if (!result || !result.success || !Array.isArray(result.data)) {
                console.log('Failed to load templates', result);
                return;
            }

            const templates = result.data;
            availableTemplates = templates;

            autoCompleteJS = new autoComplete({
                selector: '#autoComplete',
                placeHolder: 'Search templates...',
                data: {
                    src: templates,
                    keys: ['title'], // search by title
                    cache: true
                },
                threshold: 0,
                resultsList: {
                    noResults: true,
                    maxResults: undefined
                },
                resultItem: {
                    highlight: true // highlights matched text in the title
                },
                events: {
                    input: {
                        selection: (event) => {
                            const selection = event.detail.selection.value;
                            selectedTemplate = selection;
                            templateIDEl.value = selection.value;
                            autoCompleteJS.input.value = selection.title;
                            updateSendButtonState();
                        },
                        focus: () => {
                            autoCompleteJS.start()
                        }
                    }
                }
            });

        })
        .catch(err => console.error('Templates fetch error', err));

    function updateSendButtonState() {
        sendBtn.disabled = (templateIDEl.value === '');
    }

    inputEl.addEventListener('input', function () {
        const inputVal = inputEl.value.trim();
        const match = availableTemplates.find(tpl => tpl.title === inputVal);
        templateIDEl.value = match ? match.value : "";
        updateSendButtonState();
            
    });

    inputEl.addEventListener('blur', function () {
        const inputVal = inputEl.value.trim();
        const match = availableTemplates.find(
            tpl => tpl.title === inputVal
        );
        if (match) {
            templateIDEl.value = match.value;
        } else {
            templateIDEl.value = "";
        }
        updateSendButtonState();
    });

    updateSendButtonState();
});