<?php use function ANTHeader\sha256Base64;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
$http = array_key_exists('img-path', $_GET) ? "{$_GET['img-path']}" : '';
$format = array_key_exists('format', $_GET) ? "{$_GET['format']}" : '';
foreach ($_GET as $key => $val) {
    if (preg_match('/^[a-zA-Z\\-0-9_]+:[\\x21-\\x7e]+$/D', "$key:$val")) {
        header("_GET-$key:$val");
    }
}
if (!str_starts_with($format, 'res.')) {
    http_response_code(500);
    exit;
} else $format = match ($format) {
    'res.jpeg', 'res.jpg' => 'jpeg',
    'res.webp' => 'webp',
    'res.avif' => 'avif',
    default => 'png32',
};
[$sha256, $fileContent, $status] = createViaImagick($http);
http_response_code($status);
header("xhttp-status:$status");
if ($status === 200) {
    if ($format === 'png32')
        header('content-type: image/png');
    else header("content-type: image/$format");

    header("hashtag:sha256-$sha256");
    echo $fileContent;
}
function sha256(string $string): string
{
    return sha256Base64($string);
}

///**
// * Resolves the best supported image format based on Imagick availability.
// * * @param string $requestedFormat The primary format (e.g., "jpeg", "avif")
// * @return string|null The requested format if supported, otherwise the best fallback.
// */
//function getSupportedImageFormat(string $requestedFormat): ?string
//{
//    // 1. Check if the Imagick extension is actually loaded
//    if (!extension_loaded('imagick')) {
//        return null; // Hard fallback if Imagick is missing
//    }
//
//    $im = new Imagick;
//    $supported = $im->queryFormats();
//
//    // Define fallbacks in order of preference
//    $fallbacks = ["avif", "webp", "png32"];
//
//    // Normalize requested format for comparison (Imagick uses uppercase)
//    $upperRequested = strtoupper($requestedFormat);
//
//    // 2. Check if the first param is supported
//    if (in_array($upperRequested, $supported)) {
//        return $requestedFormat;
//    }
//
//    // 3. Loop through fallbacks and return the first one supported
//    foreach ($fallbacks as $fallback) {
//        if (in_array(strtoupper($fallback), $supported)) {
//            return $fallback;
//        }
//    }
//
//    // 4. Ultimate fallback if even the fallbacks aren't supported
//    return "png32";
//}

function createViaImagick($file): array
{
    global $format;
    ob_start();
    //$color='#00a8f3'; // blue
    $color = '#ae782f'; // brown
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker2/PathSVG.php";
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker2/watermark.svg.php";
    $content = "<rect width=\"440\" height=\"100\" fill=\"$color\"/>" . ob_get_clean();
    $content = '<svg width="800" height="1280" viewBox="0 0 800 1280" xmlns="http://www.w3.org/2000/svg">'
        . "$content</svg>";
    try {
        $svg = new Imagick;
        $png = new Imagick(realpath($file));
        $svg->setBackgroundColor(new ImagickPixel('transparent'));
        [$rect, $content] = explode('<!-- keepIntact -->', $content);
        $content = preg_replace('/(\\s)(fill|stroke(?:-width)?)=/',
            '${1}data-${2}=', $content);
        $content = preg_replace('/<(path|circle)/',
            '<${1} fill="none" stroke-width="4" stroke="#000000"',
            "$rect$content");
        $content = base64_encode("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n$content");
        $svg->readImage("data:image/svg+xml;charset=UTF-8;base64,$content");
        // $svg->setResolution(300, 300);
        $png->compositeImage(
            $svg,
            Imagick::COMPOSITE_OVER,
            0, 0,
        );
        $png->setImageFormat($format);
        $sha256 = sha256($fileContent = $png->getImageBlob());
        $png->clear();
        $svg->clear();
    } catch (ImagickException$imagickException) {
        error_log((string)$imagickException);
        return [null, null, 500];
    }
    return [$sha256, $fileContent, 200];
}
