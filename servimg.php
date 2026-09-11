<?php header('vary: sec-fetch-site');
if (array_key_exists("HTTP_SEC_FETCH_SITE", $_SERVER)) {
    if (strtolower($_SERVER["HTTP_SEC_FETCH_SITE"]) === "cross-site") {
        http_response_code(403);
        exit;
    }
}

$name = '404 error';
require_once 'matchUniverses.php';
//if(array_key_exists('asjson',$_GET)){header('content-type:application/json');echo json_encode($_GET);exit;}
$original = $http = "htignore/404placeholder.webp";
if (array_key_exists("univ", $_GET) &&
    array_key_exists("format", $_GET)) {
    $univ = "{$_GET['univ']}";
    $name = matchUniverses($univ);
    if (!preg_match('/^[a-z \\-\']+$/iD',
        $name)) $name = 'Universe Representation';
    if (preg_match('/^(png|jpe?g|webp|avif)$/iD', "{$_GET['format']}")) {
        if ($univ === 'Main') $http = "htignore/images/universe-img.{$_GET['format']}";
        else $http = "htignore/universe-images/$univ/universe-img.{$_GET['format']}";
        if (!file_exists($http)) $http = $original;
    }
} elseif (array_key_exists("uni", $_GET) &&
    array_key_exists("var", $_GET) &&
    array_key_exists("char", $_GET) &&
    array_key_exists("format", $_GET) &&
    array_key_exists("withai", $_GET)) {
    $withai = $_GET['withai'] ? 'ai.' : '';
    $prefix = '';
    if (array_key_exists("prefix", $_GET)) if (preg_match(
        '/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['prefix']}")) $prefix = "{$_GET['prefix']}.";
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['var']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['char']}")) {
        $univ = $_GET['uni'] === 'main' ? 'images' : "universe-images/{$_GET['uni']}";
        if ("{$_GET['format']}" === 'lightbox') {
            $json = readJSONFile("htignore/$univ/{$_GET['char']}/main.json") ?? array();
            $name = ($json['name'] ?? "{$_GET['char']}") ?? 'unknown';
            $name = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
            echo "<!DOCTYPE html><meta charset=UTF-8><style>body{margin:0;display:grid;place-items:center;height:"
                . "100vh;background-color:black}img{max-width:100vw;max-height:100vh;display:block}</style><title>";
            echo "Character &quot;$name&quot; (ANTRequest.nl)</title><meta name=robots content=noindex,nofollow>";
            echo "<meta name=viewport content='width=device-width,initial-scale=1'><picture>";
            $baseURL = '/gallery/';
            $webphash = null;
            $withai_ = $_GET['withai'] ? 'ai/' : '';
            $basePath = "htignore/$univ/{$_GET['char']}/$withai_$prefix{$_GET['var']}";
            foreach (["avif", "webp", "png"] as $format)
                if (file_exists($p = "htignore/$univ/{$_GET['char']}/$withai$prefix{$_GET['var']}.$format")) {
                    $h = base64UrlEncode_temporary(sha256Bin(file_get_contents($p)));
                    echo "<source srcset=$baseURL$univ/$withai_$prefix{$_GET['char']}.{$_GET['var']}.$format~$h>";
                    if ($format === 'webp') $webphash = $h;
                }
            $f = ".webp~$webphash";
            echo "<img alt='The Image in a lightbox' src=$baseURL$univ/$withai_$prefix{$_GET['char']}.{$_GET['var']}$f>";
            exit("</picture>");
        } elseif (preg_match('/^(png|jpe?g|webp|avif)$/iD', "{$_GET['format']}")) {
            $http = "htignore/$univ/{$_GET['char']}/$withai$prefix{$_GET['var']}.{$_GET['format']}";
            if (!file_exists($http)) $http = $original; else {
                $json = readJSONFile("htignore/$univ/{$_GET['char']}/main.json") ?? array();
                $name = $json['name'] ?? "{$_GET['char']}";
            }
        }
    }
} elseif (array_key_exists('type', $_GET)) /** @noinspection PhpSwitchStatementWitSingleBranchInspection */
    switch ("{$_GET['type']}") {
        case "comic":
            if (($titleURL = getArrayValue($_GET, 'titleURL', '/^[a-zA-Z0-9\\-]+$/D'))
                && ($episodeId = getArrayValue($_GET, 'episodeId', '/^\\d+$/D'))
                && ($imageN = getArrayValue($_GET, 'imageN', '/^\\d\\d\\d$/D'))
                && ($format = getArrayValue($_GET, 'format', '/^(?:webp|avif)$/D'))) {
                $http = "htignore/comic-images/$titleURL/$episodeId/img$imageN.$format";
                if (!file_exists($http)) $http = $original;
            }
    }
function getArrayValue(array $array, string $key, ?string $validateRegex = null): mixed
{
    if (array_key_exists($key, $array)) {
        if (is_string($validateRegex)) {
            if (preg_match($validateRegex, $array[$key])) {
                return $array[$key];
            }
        } else return $array[$key];
    }
    return null;
}

if ($http === $original) http_response_code(404);
$sha256 = base64UrlEncode_temporary(sha256Bin($fileContent = file_get_contents("$http")));
$ext = getimagesizefromstring("$fileContent");
$hashMatched = false;
$hash = '';
if (array_key_exists('hash', $_GET)) {
    $hash = "{$_GET['hash']}";
    $hashMatched = $sha256 === $hash;
}
if ($hashMatched) {
    header("cache-control: public, max-age=" . (3600 * 24 * 2));
} else {
    http_response_code(404);
    $sha256 = base64UrlEncode_temporary(sha256Bin($fileContent = file_get_contents("$original")));
    $ext = getimagesizefromstring("$fileContent");
}

header("Content-Disposition: inline; filename=\"$name\"");
if ($http !== $original && $hashMatched) header("etag: \"sha256b64-$sha256\"");
header("content-type:{$ext['mime']}");
header("image-width: $ext[0]");
header("image-height:$ext[1]");
echo $fileContent;
function readJSONFile(string $file)
{
    if ($content = file_get_contents($file)) {
        return json_decode($content, true);
    } else return null;
}

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
