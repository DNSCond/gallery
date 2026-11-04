<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

$found = false;
$data = array();
require_once __DIR__ . '/../require.php';
require_once __DIR__ . '/../imageTag.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$datacar = readJSON(__DIR__ . '/../imgdata/.comic-assets.json');
if (array_key_exists('titleURL', $_GET) &&
    array_key_exists('episodeId', $_GET)) {
    $data = fetchThingOrNull($datacar, ['data', $_GET['titleURL'], 'eps', $_GET['episodeId']]);
    $found = (bool)$data;
}
if (!$found) {
    http_response_code(404);
    require_once "{$_SERVER['DOCUMENT_ROOT']}/require/404document.php";
    exit;
}
$series = $datacar['data'][$_GET['titleURL']]['datatoon']['name'];
$withPNGButton = array_key_exists('withpng', $_GET) && "{$_GET['withpng']}";
create_head3($title = $data['chdata']['name'], [
    'base' => '/gallery/', 'canonical' => "/gallery/comics/_{$_GET['titleURL']}/{$_GET['episodeId']}", 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
        array('text' => "$series", 'href' => "/gallery/comics/_{$_GET['titleURL']}/"),
        array('text' => "$title (Ep {$_GET['episodeId']})", 'href' =>
            "/gallery/comics/_{$_GET['titleURL']}/{$_GET['episodeId']}"),
    ], 'stylelinks' => ['statics/cssx.css', 'statics/webtoon-img.css'],
]);
$episode = 0;
$htmlName = htmlspecialchars12($title);
echo "<div class=divs><h1>$htmlName</h1></div><script src=statics/png-conv.js type=module></script>\n";
foreach ($data['imgs'] as $img) {
    $echo = imageTag(null, $img, ['avif', 'webp'],
        "Episode Image\x20" . (++$episode), ['webtoon-img']);
    if ($episode === 1 || $episode === 2 || $episode === 3 || $episode === 4 || $episode === 5 || $episode === 6)
        $echo = str_replace('fetchpriority=auto loading=lazy', 'fetchpriority=high', $echo);
    if ($withPNGButton) $echo = "$echo<div class=divs><div class=lh><button class=comic-png>create png</button></div></div>";
    echo str_replace("data-file-name=''", "data-file-name='Episode Image $episode'", "$echo\n");
}
