'use strict'

document.addEventListener('DOMContentLoaded', function () {

    const nonceEl = document.getElementById('templateNonce');
    const nonce = nonceEl ? nonceEl.value : null;
    const btn = document.getElementById('sendMessage');
    const inputEl = document.getElementById('autoComplete');

    let selectedTemplate = null;
    let autoCompleteJS = null;

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const typedText = inputEl ? inputEl.value.trim() : '';

            if (selectedTemplate) {
                console.log('Selected template:', selectedTemplate.value, selectedTemplate.title);
            } else {
                console.log('Autocomplete text:', typedText);
            }
        });
    }

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

            const templates = result.data; // [{ value: 192, title: 'First Message Template' }, ...]

            const autoCompleteJS = new autoComplete({
                selector: '#autoComplete',
                placeHolder: 'Search templates...',
                data: {
                    src: templates,
                    keys: ['title'], // search by title
                    cache: true
                },
                resultsList: {
                    noResults: true,
                    maxResults: 20
                },
                resultItem: {
                    highlight: true // highlights matched text in the title
                },
                events: {
                    input: {
                        selection: (event) => {
                            const selection = event.detail.selection.value;
                            selectedTemplate = selection;
                            autoCompleteJS.input.value = selection.title;
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Templates fetch error', err));
});