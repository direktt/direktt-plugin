document.addEventListener('DOMContentLoaded', function () {

    const quill = new Quill('#editor', {
        modules: {
            toolbar:
                [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    ['image'],
                    ['save']
                ],
            imageDropAndPaste: {
                // add an custom image handler
                handler: imageHandler,
            },
        },
        placeholder: 'Add user note...',
        theme: 'snow',
    });

    var Image = Quill.import('formats/image');
    Image.sanitize = function (url) {
        return url; // You can modify the URL here
    };

    const textAreaElement = document.getElementById("direkttNotes")

    textAreaElement.value = quill.root.innerHTML

    const QuillImageData = QuillImageDropAndPaste.ImageData

    quill.on("text-change", function () {
        textAreaElement.value = quill.root.innerHTML
    })

    const toolbar = quill.getModule('toolbar').container;

    // Find where Quill placed <button class="ql-save">
    let saveBtn = toolbar.querySelector('button.ql-save');
    if (saveBtn) {
        const newBtn = document.createElement('button');
        newBtn.id = 'notesSave';
        newBtn.type = 'button';
        newBtn.className = 'button button-primary'; // For WP and Quill styling
        newBtn.innerHTML = 'Save';
        newBtn.title = 'Save'; // Optional, for tooltip

        // Replace old with new
        saveBtn.parentNode.replaceChild(newBtn, saveBtn);

        // Event handler for saving
        newBtn.addEventListener('click', function (e) {
            e.preventDefault();
            jQuery('.direktt-loader-overlay').fadeIn();
            document.getElementById('direktt-notes-edit-form').submit();
        });
    }


    function imageHandler(dataUrl, type, imageData) {
        imageData
            .minify({
                maxWidth: 1000,
                maxHeight: 1000,
                quality: 0.7,
            })
            .then((miniImageData) => {
                var blob = miniImageData.toBlob()
                var file = miniImageData.toFile()

                // generate a form data
                const formData = new FormData()

                // or just append the file
                formData.append('file', file)

                // Append required params for WP AJAX
                formData.append('action', 'direktt_quill_upload_image');
                formData.append('nonce', document.getElementById('direktt_user_notes_nonce').value);
                formData.append('direktt_notes_post_id', document.getElementById('direktt_notes_post_id').value);
                formData.append('post_id', direktt_public.direktt_post_id);

                // Upload via Fetch API
                fetch(direktt_public.direktt_ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.json())
                    .then(res => {
                        if (!res.success) {
                            // Handle error if needed: show error to user, etc.
                            alert(res.data && res.data.message ? res.data.message : 'Upload failed');
                            return;
                        }
                        // Insert uploaded image URL into Quill editor
                        let index = (quill.getSelection() || {}).index
                        if (index === undefined || index < 0) index = quill.getLength()
                        quill.insertEmbed(index, 'image', res.data.image_url, 'user')
                    })
                    .catch(err => {
                        alert('Image upload failed.');
                    });
            })
    }

    quill.getModule('toolbar').addHandler('image', function (clicked) {
        if (clicked) {
            let fileInput = this.container.querySelector('input.ql-image[type=file]')
            if (fileInput == null) {
                fileInput = document.createElement('input')
                fileInput.setAttribute('type', 'file')
                fileInput.setAttribute(
                    'accept',
                    'image/png, image/gif, image/jpeg, image/bmp, image/x-icon'
                )
                fileInput.classList.add('ql-image')
                fileInput.addEventListener('change', function (e) {
                    const files = e.target.files
                    let file
                    if (files.length > 0) {
                        file = files[0]
                        const type = file.type
                        const reader = new FileReader()
                        reader.onload = (e) => {
                            // handle the inserted image
                            const dataUrl = e.target.result
                            console.log(dataUrl)
                            console.log(type)
                            console.log(file.name)
                            imageHandler(dataUrl, type, new QuillImageData(dataUrl, type, file.name))
                            fileInput.value = ''
                        }
                        reader.readAsDataURL(file)
                    }
                })
            }
            fileInput.click()
        }
    })

});
