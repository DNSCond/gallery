<?php //http_response_code(404);header('content-type: application/json');echo json_encode($_GET);
$format = 'png';
function sha256(string $string): string
{
    return base64_encode(hash('sha256', $string, true));
}

$name = '404error';
$original = $http = 'htignore/404placeholder.png';
if (array_key_exists("uni", $_GET) ||
    array_key_exists("var", $_GET) ||
    array_key_exists("char", $_GET) ||
    array_key_exists("format", $_GET) ||
    array_key_exists("withai", $_GET)) {
    $withai = $_GET['withai'] ? 'ai.' : '';
    $prefix = '';
    if (array_key_exists("prefix", $_GET)) if (preg_match(
        '/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['prefix']}")) $prefix = "{$_GET['prefix']}.";
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['var']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}") ||
        preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['char']}") ||
        preg_match('/^(png|jpe?g|webp|avif)$/iD', "{$_GET['format']}")) {
        $http = "htignore/universe-images/{$_GET['uni']}/{$_GET['char']}/$withai$prefix{$_GET['var']}.{$_GET['format']}";
        if (!file_exists($http)) $http = $original;
        else {
            $json = readJSONFile("htignore/universe-images/{$_GET['uni']}/{$_GET['char']}/main.json") ?? array();
            $name = $json['name'] ?? "{$_GET['char']}";
        }
    }
}
if ($http === $original) http_response_code(404);
$sha256 = sha256($fileContent = file_get_contents("$http"));
$ext = getimagesizefromstring("$fileContent");
$filemtime = filemtime($http);
header("FX-filemtime:" . gmdate('D M Y-m-d \\TH:i:s \\U\\T\\CO (e)', $filemtime));
header("Content-Disposition: inline; filename=\"$name\"");
header("content-type:{$ext['mime']}");
header("etag: \"sha256b64-$sha256\"");
header("image-width: $ext[0]");
header("image-height:$ext[1]");
echo $fileContent;
function readJSONFile(string $file)
{
    if ($content = file_get_contents($file)) {
        return json_decode($content, true);
    } else return null;
}
