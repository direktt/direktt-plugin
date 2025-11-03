'use strict'

document.addEventListener('DOMContentLoaded', function () {

    const nonceEl = document.getElementById('usersNonce');
    let templateIDEl = document.getElementById('userID');
    const nonce = nonceEl ? nonceEl.value : null;
    const inputEl = document.getElementById('autoComplete');

    const sendBtn = document.getElementById('addUserBtn');

    let selectedTemplate = null;
    let autoCompleteJS = null;

    let availableUsers = [];

    var data = new FormData();
    data.append('action', 'direktt_get_users_taxonomy_service');
    data.append('nonce', nonce);
    data.append('post_id', direktt_public.direktt_post_id);

    autoCompleteJS = new autoComplete({
        selector: '#autoComplete',
        placeHolder: 'Search users...',
        threshold: 0,
        data: {},
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

    fetch(direktt_public.direktt_ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json())
        .then(result => {
            if (!result || !result.success || !Array.isArray(result.data)) {
                console.log('Failed to load users', result);
                return;
            }

            availableUsers = result.data;
            const filtered = availableUsers.filter(obj => !usersInList.includes(obj.value));
            autoCompleteJS.data = {
                src: filtered,
                keys: ['title']
            }

        })
        .catch(err => console.error('Templates fetch error', err));

    function updateSendButtonState() {
        sendBtn.disabled = (templateIDEl.value === '');
    }

    inputEl.addEventListener('input', function () {
        const inputVal = inputEl.value.trim();
        const match = availableUsers.find(tpl => tpl.title === inputVal);
        templateIDEl.value = match ? match.value : "";
        updateSendButtonState();
    });

    inputEl.addEventListener('blur', function () {
        const inputVal = inputEl.value.trim();
        const match = availableUsers.find(
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


jQuery(document).ready(function ($) {

    $('#addUserBtn').off('click').on('click', function (e) {
        e.preventDefault();

        form = $(this).closest('form');

        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = actionInputName;
        actionInput.value = '1';

        form.append(actionInput);

        var idToAdd = document.createElement('input');
        idToAdd.type = 'hidden';
        idToAdd.name = idToAddName;
        idToAdd.value = $('#userID').val();
         form.append(idToAdd);

        $('.direktt-loader-overlay').fadeIn();
        setTimeout(function () {
            $('form').submit();
        }, 500);
    });

    $('.remove-user-btn').off('click').on('click', function (e) {
        e.preventDefault();
        idToRemoveId = $(this).data('id');
        form = $(this).closest('form');  
        $('#edit-taxonomies-service-confirm').addClass('direktt-popup-on');
    });

    $('#edit-taxonomies-service-confirm .direktt-popup-no').off('click').on('click', function () {
        $('#edit-taxonomies-service-confirm').removeClass('direktt-popup-on');
    });

    $('#edit-taxonomies-service-confirm .direktt-popup-yes').off('click').on('click', function () {
        $('#edit-taxonomies-service-confirm').removeClass('direktt-popup-on');

        var idToRemove = document.createElement('input');
        idToRemove.type = 'hidden';
        idToRemove.name = idToRemoveName
        idToRemove.value = idToRemoveId;

        form.append(idToRemove);

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = actionInputDeleteName;
        actionInput.value = '1';
        form.append(actionInput);

        $('.direktt-loader-overlay').fadeIn();
        setTimeout(function () {
            $('form').submit();
        }, 500);
    });

});