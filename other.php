<?php use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

require_once 'require.php';
$title = 'Favicond\'s Ai Image Gallery';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
create_head3($title, ['base' => '/gallery/',
    'stylelinks' => ['statics/cssx.css', 'statics/ddDL-table.css', 'statics/others.css'],
    'class' => array('smaller'), 'canonical' => '/gallery/other/', 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => $title, 'href' => '/gallery/other/'),
    ],
]);
$datas = readJSON(__DIR__ . '/imgdata/.misc-ai.json');
echo "<div class=divs><h1>$title</h1><div>";
foreach ($datas as $hash => $data) {
    $alt = htmlspecialchars12($data['alt']);
    echo "<div class=img-src><div><img src=imgdata/$hash width='{$data['w']}' height='{$data['h']}' alt="
        . "'$alt' data-type='{$data['t']}' fetchpriority=auto loading=lazy><div>$alt</div></div></div>";
}
echo '</div></div>';
