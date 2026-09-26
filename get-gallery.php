<?php use function Helpers\htmlspecialchars12;

$uniSlugName = null;
$characters = array();
require __DIR__ . '/require.php';
global $nightLightOverride, $aiAlways;
$data = ($unidata = readJSON(__DIR__ . '/imgdata/.assets.json'))['chardata'];
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}")) {
        if (array_key_exists("{$_GET['uni']}", $data)) {
            $uniSlugName = "{$_GET['uni']}";
        }
    }
} else $uniSlugName = 'main';
$uniSlugName = 'main';
$base = "universe/$uniSlugName/";
require __DIR__ . '/imageTag.php';
if (isset($GLOBALS['all'])) {
    foreach ($data as $every) foreach ($every as $item) {
        $char = $item['main.json'];
        if (!is_array($char)) continue;
        if (array_key_exists('private', $char)) if ($char['private']) continue;
        if (array_key_exists('aichar', $char)) if ($char['aichar']) continue;
        $_boxcolor = array_key_exists('primaryColor', $char) ? $char['primaryColor'] : '#00a8f3';
        if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
        $name = htmlspecialchars12($char['name'] ?? $char['charId']);
        $formats = $aiAlways ? ['webp', 'png'] : ['avif', 'webp'];
        $img = imageTag($item[$aiAlways ? 'main-ai' : "main-see"], $formats, array('store-img'), $aiAlways ? null : 'webp');
        if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
            "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
            ">$name (Alt)</a></h3><a href=$base{$item['charId']}>$img</a></article>";
        foreach ($item['asset2'] as $asset) {
            $img = imageTag($asset, ['avif', 'webp'], array('store-img'));
            if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
                "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
                ">$name</a></h3><a href=$base{$item['charId']}>$img</a></article>";
        }
        foreach ($item['assetAi'] as $asset) {
            $img = imageTag($asset, ['avif', 'webp'], array('store-img'));
            if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
                "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
                ">$name (Ai Alt)</a></h3><a href=$base{$item['charId']}>$img</a></article>";
        }
    }
} elseif ($uniSlugName) foreach ($data[$uniSlugName] as $item) {
    $char = $item['main.json'];
    if (!is_array($char)) continue;
    if (array_key_exists('private', $char)) if ($char['private']) continue;
    if (array_key_exists('aichar', $char)) if ($char['aichar']) continue;
    $_boxcolor = array_key_exists('primaryColor', $char) ? $char['primaryColor'] : '#00a8f3';
    if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
    $name = htmlspecialchars12($char['name'] ?? $char['charId']);
    $formats = $aiAlways ? ['webp', 'png'] : ['avif', 'webp'];
    $img = imageTag($item[$aiAlways ? 'main-ai' : "main-see"], $formats, array('store-img'), $aiAlways ? null : 'webp');
    if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
        "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
        ">$name</a></h3><a href=$base{$item['charId']}>$img</a></article>";
}
