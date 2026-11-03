//<script type=application/json is=output-script><\\?= json_encode($data) ?\\></script>
Array.from(document.getElementsByClassName('png')).forEach(each => {
    const img = each.parentElement.parentElement.querySelector('img'),
        pic = each.parentElement.parentElement.querySelector('picture');
    each.addEventListener('click', () => convertImageSourceToPNG(img).then(blob => {
        const blobHref = URL.createObjectURL(blob),
            a = document.createElement('a');
        a.textContent = 'Download PNG';
        a.download = pic.dataset.fileName;
        a.href = blobHref;
        each.replaceWith(a);
    }), {once: true});
});

export function convertImageSourceToPNG(img) {
    return createImageBitmap(img).then(imageBitmap => {
        const offscreenCanvas = new OffscreenCanvas(imageBitmap.width, imageBitmap.height),
            ctx = offscreenCanvas.getContext("2d");
        ctx.drawImage(imageBitmap, 0, 0, imageBitmap.width, imageBitmap.height);
        return offscreenCanvas.convertToBlob();
    });
}
