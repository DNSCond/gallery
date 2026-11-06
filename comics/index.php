<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

$dataURL = '';
$humanTitle = '';
require_once __DIR__ . '/../require.php';
require_once __DIR__ . '/../imageTag.php';
date_default_timezone_set('UTC');
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$datacar = readJSON(__DIR__ . '/../imgdata/.comic-assets.json');
if (array_key_exists('titleURL', $_GET)) {
    if (array_key_exists($_GET['titleURL'], $datacar['data'])) {
        $humanTitle = $datacar['data'][$_GET['titleURL']]['datatoon']['name'];
        $dataURL = "{$_GET['titleURL']}";
    } else http_response_code(404);
}
create_head3($title = $humanTitle ? "$humanTitle (Comics)" : 'Comics', [
    'base' => '/gallery/', 'class' => array('smaller'), 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
        $dataURL ? array('text' => $humanTitle, 'href' => "/gallery/comics/_$dataURL/") : null,
    ], 'canonical' => "/gallery/comics/" . ($dataURL ? "_$dataURL/" : ''),
    'stylelinks' => ["statics/cssx.css", 'statics/webtoon-home.css'],
]);
echo "<div class=divs><h1>$title</h1><div><script src=statics/index.js type=module></script>";
if ($dataURL !== '') {
    $is = 'is=relative-time';
    echo "<h2>Episodes</h2><ol class=episode-list>";
    foreach ($datacar['data'][$_GET['titleURL']]['eps'] as $chapterId => $chapter) {
        $htmlName = htmlspecialchars12("{$chapter['chdata']['name']}");
        $img = imageTag(null, $chapter['imgs']['ep-thumb'], ['png'],
            "{$chapter['chdata']['name']}'s Thumbnail", array('thumb202'), 'png');
        $href = "href=comics/_$dataURL/$chapterId";
        echo "<li><a $href>$img</a>\x20<a $href>$htmlName</a>\x20";
        if (is_string($chapter['chdata']['released']))
            if ($time = strtotime($chapter['chdata']['released'])) {
                $htmlTime = gmdate('D, Y-M-d H:i:s', $time) . "\x20UTC";
                $dt = gmdate('Y-m-d\\TH:i:s', $time);
                echo "(<time $is datetime={$dt}Z>$htmlTime</time>)";
            }
    }
    echo '</ol><h2>Other Webtoons!</h2>';
}
foreach ($datacar['data'] as $name => $webtoon) {
    if ($dataURL === $name) continue;
    $htmlName = htmlspecialchars12("{$webtoon['datatoon']['name']}");
    $img = imageTag(null, $webtoon['thumb']['webtoon'], ['avif', 'webp'],
        "{$webtoon['datatoon']['name']}'s Thumbnail", array('thumb1000', 'store-img'));
    echo "<article class=store-div data-c=#00a8f3 is=shadowboxed-hover>" .
        "<h3 class=charname><a href=comics/_$name/>$htmlName</a></h3>" .
        "<a href=comics/_$name/>$img</a></article>";
}
echo "</div></div>\n";
