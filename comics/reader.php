<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;
use function Helpers\Getters\readJSON;

$found = false;
$data = array();
require_once __DIR__ . '/require.php';
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
$PNGBtn = array_key_exists('withpng', $_GET) && "{$_GET['withpng']}";
create_head3($title = $data['chdata']['name'], [
    'stylelinks' => ['statics/cssx.css', 'statics/webtoon-img.css', 'statics/ShadowBoxedHover.css'],
    'base' => '/gallery/', 'canonical' => "/gallery/comics/_{$_GET['titleURL']}/{$_GET['episodeId']}", 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
        array('text' => "$series", 'href' => "/gallery/comics/_{$_GET['titleURL']}/"),
        array('text' => "$title (Ep {$_GET['episodeId']})", 'href' =>
            "/gallery/comics/_{$_GET['titleURL']}/{$_GET['episodeId']}"),
    ], 'borderColor' => '#fff100', 'backColor' => '#87cefa',
]);
$episode = 0;
$source = '';
$htmlName = htmlspecialchars12($title);
echo "\x3cscript type=module src=statics/index.js>\x3c/script>";
echo "\x3cscript type=module src=statics/png-conv.js>\x3c/script>";
echo "<div class=divs><h1>$htmlName</h1></div><main>\n";
foreach ($data['imgs'] as $key => $img) {
    if (!preg_match('/^img\\d{3}$/D', $key)) continue;
    $echo = imageTag(null, $img,
        ['avif', 'webp'], "Episode Image\x20" . (++$episode), ['webtoon-img'],
        highPriority: $episode === 1 || $episode === 2, dataset: ['file-name' => "Ep-Img-$episode"]);
    if ($PNGBtn) $echo = "$echo<div class=divs><div class=lh><button class=comic-png>create png</button></div></div>";
    echo "$echo\n";
}
echo "</main><div class=divs>";
$noBottom = array_key_exists('noBottom', $GLOBALS) && $GLOBALS['noBottom'];
if (!$noBottom) {
    printCharacters($data['chdata'], 'with', 'Characters Featured');
    printCharacters($data['chdata'], 'bgCharacters', 'Background Characters Present');

    echo "</div>\n";
    $baseURL = "comics/_{$_GET['titleURL']}/";
    if (is_int($data['chdata']['next'])) {
        echo "<DIV class=divs><A is=button-hovera href=$baseURL{$data['chdata']['next']}>Next -&gt;</A></DIv>";
    }
} else echo "</div>\n";
