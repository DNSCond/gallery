<?php header('vary: sec-fetch-site');
if (array_key_exists("HTTP_SEC_FETCH_SITE", $_SERVER)) {
    if (strtolower($_SERVER["HTTP_SEC_FETCH_SITE"]) === "cross-site") {
        http_response_code(403);
        exit;
    }
}
$debug = false;
//if(array_key_exists('asjson',$_GET)){http_response_code(501);header('content-type:application/json');echo json_encode($_GET);exit;}
function trigger404(): never
{
    http_response_code(404);
    $e404 = "htignore/404placeholder.webp";
    $fileContent = file_get_contents($e404);
    $ext = getimagesizefromstring("$fileContent");
    header("Content-Disposition: inline");
    header("content-type:{$ext['mime']}");
    header("image-size: w=$ext[0], h=$ext[1]");
    echo $fileContent;
    exit;
}

$serv = $e404 = "htignore/404placeholder.webp";
if (array_key_exists("failure", $_GET) && $_GET['failure']) {
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    if ($debug) header('x-warning: other failure');
    trigger404();
} elseif (!in_array(array_key_exists("format", $_GET)
    ? "{$_GET['format']}" : '', ['png', 'webp', 'avif'])) {
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    if ($debug) header('x-warning: incorrect extension');
    trigger404();
}

if (array_key_exists('univ', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['univ']}")) {
        $serv = "htignore/universe-images/{$_GET['univ']}/universe-img.webp";
    }
} elseif (
    array_key_exists("uni", $_GET) &&
    array_key_exists("var", $_GET) &&
    array_key_exists("char", $_GET) &&
    array_key_exists("withai", $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['var']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['char']}")) {
        $serv = "htignore/universe-images/{$_GET['uni']}/{$_GET['char']}/";
        $night = array_key_exists('n', $_GET) && $_GET['n'];
        $withai = !!"{$_GET['withai']}";
        if ("{$_GET['var']}" === 'main' && !$night) {
            $serv .= ($withai ? 'ai.' : '') . "main";
        } elseif ("{$_GET['var']}" === 'main-night' && $night) {
            $serv .= "main-night";
        } else {
            $serv .= ($night ? 'night/' : '') . '/gallery/' . ($withai ? 'ai/' : '') . "{$_GET['var']}";
        }
        $serv .= ".{$_GET['format']}";
    }
}
if ($serv === $e404) {
    if ($debug) header('x-warning: no file selected');
    trigger404();
}
$fileContent = file_get_contents("$serv");
$sha256 = base64UrlEncode_temporary(sha256Bin($fileContent));
if (!hash_equals($sha256, "{$_GET['hash']}")) {
    if ($debug) {
        header('x-warning: hash no match');
        header("x-hash-UYser:{$_GET['hash']}");
        header("x-hash-known:$sha256");
        header("x-hash-file: $serv");
    }
    trigger404();
}
$ext = getimagesizefromstring("$fileContent");
header("Content-Disposition: inline"); // ; filename=\"$name\"
header("content-type:{$ext['mime']}");
header("image-size: w=$ext[0], h=$ext[1]");
echo $fileContent;

function base64UrlEncode_temporary(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode_temporary(string $data): false|string
{
    if (str_contains($data, '/') || str_contains($data, '+')) return false;
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4), true);
}

function sha256Bin(string $string): string
{
    return hash('sha256', $string, true);
}
