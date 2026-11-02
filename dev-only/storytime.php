<?php use function ANTHeader\create_head3;

require_once '../jsonHTML.php';
$thing = "universe/{$_SERVER['HTTP_DATA_UNIV']}/{$_SERVER['HTTP_DATA_CHARID']}";
$bo = "{$_SERVER['HTTP_DATA_BO']}" === 'null' ? null : "{$_SERVER['HTTP_DATA_BO']}";
$bg = "{$_SERVER['HTTP_DATA_BG']}" === 'null' ? null : "{$_SERVER['HTTP_DATA_BG']}";
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$data = json_decode(file_get_contents('php://input'), true);
create_head3("{$_SERVER['HTTP_DATA_TITLE']}", [
    'base' => '/gallery/', 'bread' => [
        array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        array('text' => "{$_SERVER['HTTP_DATA_UNI_NAME']}",
            'href' => "universe/{$_SERVER['HTTP_DATA_UNIV']}"),
        array('text' => "{$_SERVER['HTTP_DATA_CHAR_TITLE']}", 'href' => $thing),
        array('text' => "{$_SERVER['HTTP_DATA_TITLE']}", 'href' => "$thing/text/{$_SERVER['HTTP_DATA_FILE_NAME']}"),
    ], 'stylelinks' => ["statics/characterPage.css"], 'borderColor' => $bo, 'backColor' => $bg,
    'canonical' => "/gallery/universe/{$_SERVER['HTTP_DATA_UNIV']}/{$_SERVER['HTTP_DATA_CHARID']}" .
        "/text/{$_SERVER['HTTP_DATA_FILE_NAME']}",
]);
//$datacar = array_filter($_SERVER, fn($name) => str_starts_with($name, 'HTTP_DATA_'), ARRAY_FILTER_USE_KEY);
//echo '<script type=application/json is=output-script>' . json_encode($datacar, JSON_PRETTY_PRINT) . '</script>';
echo mkHTMLFromJSON(['div', ['class' => 'divs'], $data['jsonml']], true);
echo "\n";
