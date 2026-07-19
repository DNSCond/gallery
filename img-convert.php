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

function createViaImagick($file): array
{
    global $format;
    ob_start();
    //$color='#00a8f3'; // blue
    $color = '#ae782f'; // brown
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker3/PathSVG.php";
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker3/watermark.svg.php";
    $content = "<rect width=\"440\" height=\"100\" fill=\"$color\"/>" . ob_get_clean();
    $content = '<svg width="800" height="1280" viewBox="0 0 800 1280" xmlns="http://www.w3.org/2000/svg">'
        . "$content</svg>";
    try {
        $svg = new Imagick;error_log($file);
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
