<?php use function Helpers\htmlspecialchars12;

$uniSlugName = null;
$characters = array();
global $nightLightOverride, $aiAlways;
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}")) {
        if (file_exists(__DIR__ . "/htignore/universe-images/{$_GET['uni']}/")) {
            $uniSlugName = "{$_GET['uni']}";
        }
    }
} else $uniSlugName = 'main';
$base = "universe/$uniSlugName/";
require __DIR__ . '/require.php';
require __DIR__ . '/imageTag.php';
if ($uniSlugName) foreach (glob(__DIR__ . "/htignore/universe-images/$uniSlugName/*/main.json") as $item) {
    $char = readCharacterJSON($item);
    if (!is_array($char)) continue;
    if (array_key_exists('private', $char)) if ($char['private']) continue;
    $_boxcolor = array_key_exists('primaryColor', $char) ? $char['primaryColor'] : '#00a8f3';
    if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
    $name = htmlspecialchars12($char['name'] ?? $char['charId']);
    $img = imageTag($char['charId'], 'main', "$name's Main Appearance",
        $aiAlways, $nightLightOverride, $uniSlugName, array('store-img'),
        true,
    );
    if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-hover " .
        "id=sec-{$char['charId']}><h3 class=charname><a href=$base{$char['charId']}" .
        ">$name</a></h3><a href=$base{$char['charId']}>$img</a></article>";
}
