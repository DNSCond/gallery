<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;
use function Helpers\Getters\readJSON;

$found = false;
$data = array();
require_once __DIR__ . '/../require.php';
require_once __DIR__ . '/../imageTag.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$datacar = readJSON(__DIR__ . '/../imgdata/.comic-assets.json');
if (array_key_exists('titleURL', $_GET)) {
    $data = fetchThingOrNull($datacar, ['data', $_GET['titleURL']]);
    $found = (bool)$data;
}
if (!$found) {
    http_response_code(404);
    require_once "{$_SERVER['DOCUMENT_ROOT']}/require/404document.php";
    exit;
}
create_head3($title = "Multireader of {$data['datatoon']['name']}", [
    'base' => '/gallery/', 'canonical' => "/gallery/comics/__{$_GET['titleURL']}/", 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
        array('text' => "{$data['datatoon']['name']} (Multireader)",
            'href' => "/gallery/comics/_{$_GET['titleURL']}/"),
    ], 'stylelinks' => ['statics/cssx.css', 'statics/webtoon-img.css'],
    'borderColor' => '#fff100', 'backColor' => '#87cefa',
    'metatags' => [['robots', 'noindex']],
]);
$episode = 0;
$htmlName = htmlspecialchars12($title);
echo "<div class=divs><h1>$htmlName</h1></div><main>\n";
foreach ($data['eps'] as $episodeKey => $ep) {
    $htmlName = htmlspecialchars12($ep['chdata']['name']);
    echo "<div class=divs><h2 class=center id=epkey-$episodeKey>$htmlName</h2></div>";
    foreach ($ep['imgs'] as $key => $img) {
        if (!preg_match('/^img\\d{3}$/D', $key)) continue;
        $echo = imageTag(null, $img, ['avif', 'webp'],
            "Episode Image\x20" . (++$episode), ['webtoon-img'],
            highPriority: $episode === 1 || $episode === 2 || $episode === 3 || $episode === 4 || $episode === 5);
        echo str_replace("data-file-name=''", "data-file-name=Ep-Img-$episode", "$echo\n");
    }
}
echo "</main>\n";
