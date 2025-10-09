document.addEventListener('DOMContentLoaded', function () {

    const quill = new Quill('#editor', {
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                ['image', 'code-block'],
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

    function imageHandler(dataUrl, type, imageData) {
        imageData
            .minify({
                maxWidth: 1000,
                maxHeight: 1000,
                quality: 0.7,
            })
            .then((miniImageData) => {
                var blob = miniImageData.toBlob()
                var file = miniImageData.toFile('my_cool_image.png')

                console.log(`type: ${type}`)
                console.log(`dataUrl: ${dataUrl}`)
                console.log(`blob: ${blob}`)
                console.log(`file: ${file}`)

                let index = (quill.getSelection() || {}).index
                if (index === undefined || index < 0) index = quill.getLength()
                //quill.insertEmbed(index, 'image', res.data.image_url, 'user')
                quill.insertEmbed(index, 'image', URL.createObjectURL(blob), 'user')
            })
    }

});
