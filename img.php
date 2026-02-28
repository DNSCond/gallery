<?php use function Helpers\sha256;
use function JWT\validateToken;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
$http = array_key_exists('img', $_GET) ? "{$_GET['img']}" : '';
require_once "JWT.php";
$name = '404';
$file = null;

function readJSONFile(string $file)
{
    if ($content = file_get_contents($file)) {
        return json_decode($content, true);
    } else return null;
}

$intendedFormat = null;
if (preg_match('/^[a-zA-Z0-9\\-]+(?:\\.[a-zA-Z0-9\\-]+)?(?:\\.[a-zA-Z0-9\-]+)?(?:\\.[a-zA-Z0-9\\-]+)?(?:\\.[a-zA-Z0-9\\-]+)?(?:\\.(?:png|jpe?g|webp|avif))?$/D',
    "$http")) header("xhttp: $http");
if (preg_match('/^(ai\\.)?gallery\\.([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/D', "$http", $matches)) {
    $format = $matches[4] === 'jpeg' ? 'jpg' : $matches[4];
    if (file_exists($temp = "htignore/images/$matches[2]/$matches[1]gallery.$matches[3].$matches[4]")) {
        header("{$_SERVER['SERVER_PROTOCOL']} 200 Ok");
        $json = readJSONFile("htignore/images/$matches[1]/main.json") ?? array();
        $name = $json['name'] ?? $matches[1];
        $file = $temp;
        $intendedFormat = $format;
    }
} elseif (preg_match('/^ai\\.([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/D', "$http", $matches)) {
    $format = $matches[3] === 'jpeg' ? 'jpg' : $matches[3];
    if (file_exists($temp = "htignore/images/$matches[1]/ai.$matches[2].$matches[3]")) {
        header("{$_SERVER['SERVER_PROTOCOL']} 200 Ok");
        $json = readJSONFile("htignore/images/$matches[1]/main.json") ?? array();
        $name = $json['name'] ?? $matches[1];
        $file = $temp;
        $intendedFormat = $format;
    } else {
        $http = "$matches[1].$matches[2].$matches[3]";
    }
} elseif (preg_match('/^comics\\.([a-zA-Z0-9\\-]+)\\.(\\d+)\\.(\\d+)\\.(png|jpe?g|webp|avif)$/D', "$http", $matches)) {
    $format = $matches[4] === 'jpeg' ? 'jpg' : $matches[4];
    if (file_exists($temp = "htignore/comic-images/$matches[1]/$matches[2]/img$matches[3].$format")) {
        $name = "Comia ImageViewer";
        $file = $temp;
    } else {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    }
    $intendedFormat = $format;
}
if ($intendedFormat === null) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/iD', "$http", $matches))
        $http = "$matches[1].main.$matches[2]";
    if (preg_match('/^([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/iD', "$http", $matches)) {
        $format = $matches[3] === 'jpeg' ? 'jpg' : $matches[3];
        if (file_exists($temp = "htignore/images/$matches[1]/$matches[2].$format")) {
            header("{$_SERVER['SERVER_PROTOCOL']} 200 Ok");
            $json = readJSONFile("htignore/images/$matches[1]/main.json") ?? array();
            $name = $json['name'] ?? $matches[1];
            $file = $temp;
        } else {
            header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        }
        $intendedFormat = $format;
    } else {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        $intendedFormat = 'png';
    }
}

if (is_null($intendedFormat)) $intendedFormat = 'png';
$http404File = "htignore/404placeholder.$intendedFormat";
$http404FilePng = "htignore/404placeholder.png";
if (is_null($file)) $file = $http404File;

/**
 * Resolves the best supported image format based on Imagick availability.
 * * @param string $requestedFormat The primary format (e.g., "jpeg", "avif")
 * @return string|null The requested format if supported, otherwise the best fallback.
 */
function getSupportedImageFormat(string $requestedFormat): ?string
{
    // 1. Check if the Imagick extension is actually loaded
    if (!extension_loaded('imagick')) {
        return null; // Hard fallback if Imagick is missing
    }

    $im = new Imagick;
    $supported = $im->queryFormats();

    // Define fallbacks in order of preference
    $fallbacks = ["avif", "webp", "png32"];

    // Normalize requested format for comparison (Imagick uses uppercase)
    $upperRequested = strtoupper($requestedFormat);

    // 2. Check if the first param is supported
    if (in_array($upperRequested, $supported)) {
        return $requestedFormat;
    }

    // 3. Loop through fallbacks and return the first one supported
    foreach ($fallbacks as $fallback) {
        if (in_array(strtoupper($fallback), $supported)) {
            return $fallback;
        }
    }

    // 4. Ultimate fallback if even the fallbacks aren't supported
    return "png32";
}

function createViaImagick($file): array
{
    global $http404File, $intendedFormat, $http404FilePng;
    /*{$sha256 = sha256($fileContent = file_get_contents("$http404File"));
    return [$sha256, $fileContent];}*/
    ob_start();
    //$color='#00a8f3'; // blue
    $color = '#ae782f'; // brown
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker2/PathSVG.php";
    require_once "{$_SERVER['DOCUMENT_ROOT']}/dollmaker2/watermark.svg.php";
    $content = "<rect width=\"440\" height=\"100\" fill=\"$color\"/>" . ob_get_clean();
    $content = '<svg width="800" height="1280" viewBox="0 0 800 1280" xmlns="http://www.w3.org/2000/svg">'
        . "$content</svg>";
    $format = getSupportedImageFormat($intendedFormat);
    header("debug-data: \$format=$format; \$intendedFormat=$intendedFormat");
    if (is_null($format)) {
        $file = $http404File;
        $sha256 = sha256($fileContent = file_get_contents("$file"));
        return [$sha256, $fileContent];
    }
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
        //header('content-type: application/xml');echo $content;exit;
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
    } catch (ImagickException) {
        if (file_exists($http404File)) {
            $sha256 = sha256($fileContent = file_get_contents("$http404File"));
        } else {
            $sha256 = sha256($fileContent = file_get_contents("$http404FilePng"));
        }
    }
    return [$sha256, $fileContent];
}

$watermarked = preg_replace('/\\.([a-z]+)$/D', '.watermarked.${1}', $file);
if (array_key_exists('token', $_GET)) {
    // jwt is used as a secure nonce, does not repeat, therefore immutable
    header("cache-control: private, max-age=3600");
    $token = array();
    $valid = ($token = validateToken($_GET['token']));
    $token = array_merge(array('nowatermark' => false, 'mustredraw' => false), (array)$token);
    if ($valid && array_key_exists('referermustmatch', $token)) {
        $valid = str_starts_with($_SERVER['HTTP_REFERER'], "https://{$token['referermustmatch']}");
    }
    if ($valid && $token['nowatermark']) {
        $sha256 = sha256($fileContent = file_get_contents("$file"));
    } else {
        if (file_exists($watermarked) && ($valid && !$token['mustredraw'])) {
            $sha256 = sha256($fileContent = file_get_contents("$watermarked"));
        } elseif ($valid && $token['mustredraw']) {
            [$sha256, $fileContent] = createViaImagick($file);
        } elseif (file_exists($http404File)) {
            $sha256 = sha256($fileContent = file_get_contents("$http404File"));
        } else {
            $sha256 = sha256($fileContent = file_get_contents("$http404FilePng"));
        }
    }
} else {
    //header("cache-control: max-age=3600");
    header("cache-control: max-age=0");
    if (str_starts_with($_SERVER['HTTP_REFERER'], "https://antrequest.nl")) {
        $sha256 = sha256($fileContent = file_get_contents("$file"));
    } else {
        if (file_exists($watermarked)) {
            $sha256 = sha256($fileContent = file_get_contents("$watermarked"));
        } elseif (file_exists($http404File)) {
            $sha256 = sha256($fileContent = file_get_contents("$http404File"));
        } else {
            $sha256 = sha256($fileContent = file_get_contents("$http404FilePng"));
        }
    }
}

header("vary: referer", false);

$ext = getimagesizefromstring("$fileContent");
header("Content-Disposition: inline; filename=\"$name\"");
header("content-length:" . strlen($fileContent));
header("content-type:{$ext['mime']}");
header("hash-tag: \"$sha256\"");
header("image-width: $ext[0]");
header("image-height:$ext[1]");
header("etag: \"$sha256\"");
//if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)) {
//if (hash_equals("\"$sha256\"", "{$_SERVER['HTTP_IF_NONE_MATCH']}"))
//{http_response_code(304);exit;}}
echo "$fileContent";
