<?php header('vary: CONTENT-TYPE-TO');
$head = match (strtolower($_SERVER['HTTP_CONTENT_TYPE_TO'] ?? '')) {
    'image/jpeg' => 'image/jpeg',
    'image/avif' => 'image/avif',
    'image/webp' => 'image/webp',
    'image/png' => 'image/png',
    default => null,
};
$ctTo = match (strtolower($head ?? '')) {
    'image/jpeg' => 'jpeg',
    'image/avif' => 'avif',
    'image/webp' => 'webp',
    'image/png' => 'png32',
    default => null,
};
if ($ctTo) {
    if ($content = file_get_contents('php://input')) {
        if (strlen($content) > 10_000_000) { // 10MB limit
            http_response_code(413);
            exit;
        }
        try {
            ($imagick = new Imagick)->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 64 * 1024 * 1024);
            $imagick->readImageBlob($content);
            $imagick->setImageFormat($ctTo);
            $out = $imagick->getImagesBlob();
            http_response_code(200);
            header("Content-Type: $head");
            header("Display-Length: " . cbyte(strlen($out)));
            header("sha256b64: sha256b64-" . sha256Base64($out));
            header("sha384b64: sha384b64-" . sha384Base64($out));
            echo $out;
        } catch (ImagickException) {
            http_response_code(500);
            header('X-Error: ImagickException');
        }
    } else {
        http_response_code(400);
        header('X-Error: no content in body');
    }
} else {
    http_response_code(400);
    header('X-Error: Unsupported content type');
}

function sha256Base64(string $string): string
{
    return base64_encode(hash('sha256', $string, true));
}

function sha384Base64(string $string): string
{
    return base64_encode(hash('sha384', $string, true));
}

function cbyte($num): string
{
    $x = array("bytes", "KB", "MB", "GB", "TB");
    $i = 0;
    while ($num >= 1024) {
        $num = $num / 1024;
        if (is_null($x[++$i])) {
            $i--;
            break;
        }
    }
    return round($num, 4) . " $x[$i]";
}
