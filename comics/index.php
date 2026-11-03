<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

require_once __DIR__ . '/../require.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
create_head3($title = 'Comics', [
    'base' => '/gallery/', 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => 'Comics', 'href' => '/gallery/comics/'),
    ], 'canonical' => "/gallery/comics/",
    'stylelinks' => ["statics/cssx.css"],
]);
$datacar = readJSON(__DIR__ . '/../imgdata/.comic-assets.json');
echo '<script type=application/json is=output-script>' . json_encode($datacar, JSON_PRETTY_PRINT) . '</script>';
if ($datacar) {
    $data = [];
    foreach ($datacar['data'] as $name => $webtoon) {
        $htmlName = htmlspecialchars12("{$webtoon['datatoon']['name']}");
        $data[] = "<h2>$htmlName</h2><ol>";
        foreach ($webtoon['eps'] as $chapterId => $chapter) {
            $htmlName = htmlspecialchars12("{$chapter['chdata']['name']}");
            $data[] = "<li><a href=comics/_$name/$chapterId>$htmlName</a>";
        }
        echo '</ol>';
    }
    $data = implode('', $data);
} else $data = '<p>No Information Found';

echo "<div class=divs><h1>$title</h1><div>$data</div></div>\n";
