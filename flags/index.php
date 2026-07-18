<!DOCTYPE html>
<html lang=en>
<meta charset=UTF-8>
<title>SVG Flags</title>
<style><?= 'div{margin-bottom:1em;>*{vertical-align:center;}}' ?></style>
<body>
<div><?= (function () {
        $GLOBALS['no-ctype'] = true;
        $result = array_map(function ($country) {
            $GLOBALS['country'] = $country;
            require 'flags.svg.php';
            return ob_get_clean();
        }, explode(',', 'us,nl,fr,ng'));
        return preg_replace('/\\s+/', " ", implode(
                '<button class=download type=button>Download PNG</button> </div><div>', $result));
    })() . '<button class=download type=button>Download PNG</button>' ?></div>
<script type=module>
    document.querySelectorAll("button.download").forEach(button => {
        button.addEventListener('click', () => {
            convertSvgToPng(
                button.previousElementSibling.outerHTML, 300, 187,
                button.previousElementSibling.dataset.country);
        });
    });

    function convertSvgToPng(svgString, targetWidth, targetHeight, targetname) {
        // 1. Create a Blob from the SVG string
        const blob = new Blob([svgString], {type: 'image/svg+xml;charset=utf-8'});
        const url = URL.createObjectURL(blob);

        // 2. Load the SVG into an Image object
        const img = new Image;
        img.onload = function () {
            // 3. Setup the Canvas with your exact target dimensions
            const canvas = document.createElement('canvas');
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            const ctx = canvas.getContext('2d');

            // 4. Draw the SVG onto the canvas
            ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

            // 5. Trigger the PNG download
            const pngUrl = canvas.toDataURL('image/png');
            const downloadLink = document.createElement('a');
            downloadLink.href = pngUrl;
            downloadLink.download = targetname;
            downloadLink.click();

            // Clean up memory
            URL.revokeObjectURL(url);
        };
        img.onerror = () => URL.revokeObjectURL(url);

        img.src = url;
    }
</script>
</body>
</html>
