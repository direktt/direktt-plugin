document.addEventListener('DOMContentLoaded', function () {

    var icons = Quill.import("ui/icons");
    icons["undo"] = '<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon><path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path></svg>'

    const quillUndo = function(){
        quill.history.undo()
    }

    const quill = new Quill('#editor', {
        modules: {
            history: {
                delay: 1000,
                maxStack: 100,
                userOnly: true
            },
            toolbar:{
                container: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline', 'image', 'undo'],
                ],
                handlers: {
                    'undo': quillUndo
                }
            },
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

    const newBtn = document.createElement('button');
    newBtn.id = 'notesSave';
    newBtn.className = 'button button-primary button-large'; // For WP and Quill styling
    newBtn.innerHTML = 'Save';
    newBtn.title = 'Save'; // Optional, for tooltip

    toolbar.before(newBtn);



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

jQuery(document).ready(function ($) {
    $('#notesSave').off('click').on('click', function () {
        $('.direktt-loader-overlay').fadeIn();
        setTimeout(function () {
            $('form').submit();
        }, 500);
    });

});
