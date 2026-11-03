<?php use function ANTHeader\create_head3;

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
create_head3($title = $data['chdata']['name'], [
    'base' => '/gallery/', 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
        array('text' => "$title", 'href' => "/gallery/comics/_{$_GET['titleURL']}/"),
        array('text' => "Episode {$_GET['episodeId']}", 'href' => "/gallery/comics/_{$_GET['titleURL']}/{$_GET['episodeId']}"),
    ], 'canonical' => "/gallery/comics/", 'stylelinks' => ['statics/cssx.css', 'statics/webtoon-img.css'],
]);
echo "<div class=divs><h1>$title</h1></div>\n";
$episode = 0;
foreach ($data['imgs'] as $img) {
    $echo = imageTag(null, $img, ['avif', 'webp'],
        "Episode Image\x20" . (++$episode), ['webtoon-img']);
    if ($episode === 1 || $episode === 2) $echo = str_replace(
        'fetchpriority=auto loading=lazy', 'fetchpriority=high', $echo);
    echo "$echo\n";
}
