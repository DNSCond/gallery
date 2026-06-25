<?php use function ANTHeader\sha256Base64;

// deprecated, use servimg.php instead.

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

function sha256(string $string): string
{
    return sha256Base64($string);
}

header("vary: referer", false);
//handleCORS();
$intendedFormat = null;
if (preg_match('/^[a-zA-Z0-9\\-]+(?:\\.[a-zA-Z0-9\\-]+)?(?:\\.[a-zA-Z0-9\-]+)?(?:\\.[a-zA-Z0-9\\-]+)?' .
    '(?:\\.[a-zA-Z0-9\\-]+)?(?:\\.(?:png|jpe?g|webp|avif))?$/D',
    "$http")) header("xhttp: $http");
if (preg_match('/^(ai\\.)?gallery\\.([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/D',
    "$http", $matches)) {
    $format = $matches[4] === 'jpeg' ? 'jpg' : $matches[4];
    if (file_exists($temp = "htignore/images/$matches[2]/$matches[1]gallery.$matches[3].$matches[4]")) {
        // header("{$_SERVER['SERVER_PROTOCOL']} 200 Ok");
        $json = readJSONFile("htignore/images/$matches[2]/main.json") ?? array();
        $name = $json['name'] ?? $matches[2];
        $file = $temp;
        $intendedFormat = $format;
    }
} elseif (preg_match('/^ai\\.([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/D',
    "$http", $matches)) {
    $format = $matches[3] === 'jpeg' ? 'jpg' : $matches[3];
    if (file_exists($temp = "htignore/images/$matches[1]/ai.$matches[2].$matches[3]")) {
        $json = readJSONFile("htignore/images/$matches[2]/main.json") ?? array();
        $name = $json['name'] ?? $matches[2];
        $file = $temp;
        $intendedFormat = $format;
    } else {
        $http = "$matches[1].$matches[2].$matches[3]";
    }
} elseif (preg_match('/^comics\\.([a-zA-Z0-9\\-]+)\\.(\\d+)\\.(\\d+)\\.(png|jpe?g|webp|avif)$/D',
    "$http", $matches)) {
    $format = $matches[4] === 'jpeg' ? 'jpg' : $matches[4];
    if (file_exists($temp = "htignore/comic-images/$matches[1]/$matches[2]/img$matches[3].$format")) {
        $name = "Comia ImageViewer";
        $file = $temp;
    }
    $intendedFormat = $format;
}
if ($intendedFormat === null) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/iD', "$http", $matches))
        $http = "$matches[1].main.$matches[2]";
    if (preg_match('/^([a-zA-Z0-9\\-]+)\\.([a-zA-Z0-9\\-]+)\\.(png|jpe?g|webp|avif)$/iD',
        "$http", $matches)) {
        $format = $matches[3] === 'jpeg' ? 'jpg' : $matches[3];
        if (file_exists($temp = "htignore/images/$matches[1]/$matches[2].$format")) {
            $json = readJSONFile("htignore/images/$matches[1]/main.json") ?? array();
            $name = $json['name'] ?? $matches[1];
            $file = $temp;
        }
        $intendedFormat = $format;
    } else {
        $intendedFormat = 'png';
    }
}

if (is_null($intendedFormat)) $intendedFormat = 'png';
$http404File = "htignore/404placeholder.$intendedFormat";
$http404FilePng = "htignore/404placeholder.png";
$status = 200;
if (is_null($file)) {
    $file = $http404File;
    $status = 404;
}

$chosen_file = null;
$watermarked = preg_replace('/\\.([a-z]+)$/D', '.watermarked.${1}', $file);
header("cache-control: public, max-age=" . (3600 * 24 * 2));
if (str_starts_with("{$_SERVER['HTTP_REFERER']}", "https://antrequest.nl")) {
    $sha256 = sha256($fileContent = file_get_contents("$file"));
    $chosen_file = $file;
} elseif (file_exists("{$_SERVER['DOCUMENT_ROOT']}/../auth.json")) {
    $sha256 = sha256($fileContent = file_get_contents("$file"));
    $chosen_file = $file;
} elseif (file_exists($watermarked)) {
    $sha256 = sha256($fileContent = file_get_contents("$watermarked"));
    $chosen_file = $watermarked;
} elseif (file_exists($http404File)) {
    $sha256 = sha256($fileContent = file_get_contents("$http404File"));
} else {
    $sha256 = sha256($fileContent = file_get_contents("$http404FilePng"));
}

$ext = getimagesizefromstring("$fileContent");
header("Content-Disposition: inline; filename=\"$name\"");
//header("content-length:" . strlen($fileContent));
header("content-type:{$ext['mime']}");
header("etag: \"sha256b64-$sha256\"");
header("image-width: $ext[0]");
header("image-height:$ext[1]");
$checked = 0;
if ($status === 404) {
    http_response_code($status);
    echo "$fileContent";
    exit;
}
if (is_string($chosen_file)) {
    $filemtime = filemtime($chosen_file);
    header("Last-Modified:" . gmdate(DATE_RFC7231, $filemtime));
    header("FX-filemtime:" . date('D M Y-m-d \\TH:i:s \\U\\T\\CO (e)', $filemtime));
}
/*if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)) {
if (trim("{$_SERVER['HTTP_IF_NONE_MATCH']}") === '*') {
http_response_code(304);exit;}
if (preg_match_all('/"([^"]+)"/', "{$_SERVER['HTTP_IF_NONE_MATCH']}",
$matches, PREG_SET_ORDER)) {foreach ($matches as $match) {
if (preg_match('/^sha256b64-(.+)$/D', $match[1], $matched)) {
if (hash_equals("$sha256", $matched[1])) {
http_response_code(304);exit;}}}}}*/
http_response_code($status);
echo "$fileContent";
