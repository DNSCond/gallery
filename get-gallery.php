<?php use function Helpers\htmlspecialchars12;

function matchUniverses(string $universe): string
{
    global $unidata;
    $Favi_verse = $unidata;
    if (array_key_exists($universe, $Favi_verse["unidata"])) {
        return $Favi_verse["unidata"][$universe]["humanName"];
    } else return $universe;
}

$uniSlugName = null;
$characters = array();
require __DIR__ . '/require.php';
global $nightLightOverride, $aiAlways;
$data = ($unidata = readJSON(__DIR__ . '/imgdata/.assets.json'))['chardata'];
$withalt = array_key_exists('withalt', $_GET) && "{$_GET['withalt']}";
if ($withalt) $aiAlways = false;
$allOf = array_key_exists('all', $_GET) && "{$_GET['all']}";
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}")) {
        if (array_key_exists("{$_GET['uni']}", $data)) {
            $uniSlugName = "{$_GET['uni']}";
        }
    }
} else $uniSlugName = 'main';
$si = $aiAlways ? "-ai" : '';
$base = "universe$si/$uniSlugName/";
require __DIR__ . '/imageTag.php';
if (isset($GLOBALS['all'])) {
    foreach ($data as $uniSlugName => $every) {
        $base = "universe/$uniSlugName/";
        $uninameData = matchUniverses($uniSlugName);
        $unislugData = $uniSlugName === 'main' ? '/' : $base;
        $characters[] = "<h2 class=h2-border><a href='$unislugData'>$uninameData</a></h2>";
        foreach ($every as $item) appendMain($item, false, $characters, $withalt);
    }
    $uniSlugName = 'main';
} elseif ($uniSlugName) {
    $uninameData = matchUniverses($uniSlugName);
    $characters[] = "<h2 class=h2-border>Characters of &lt;$uninameData&gt;</h2>";
    foreach ($data[$uniSlugName] as $item) {
        appendMain($item, $aiAlways, $characters, $withalt);
    }
}

function appendMain(array $item, bool $aiAlways, array &$characters, bool $withalt): void
{
    global $base;
    $char = $item['main.json'];
    if (!is_array($char)) return;
    if (array_key_exists('private', $char)) if ($char['private']) return;
    if (array_key_exists('aichar', $char)) if ($char['aichar']) return;
    $_boxcolor = array_key_exists('primaryColor', $char) ? $char['primaryColor'] : '#00a8f3';
    if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
    $name = htmlspecialchars12($char['name'] ?? $char['charId']);
    $formats = $aiAlways ? ['webp', 'png'] : ['avif', 'webp'];
    $img = imageTag($item[$aiAlways ? 'main-ai' : "main-see"],
        $formats, "", array('store-img'), $aiAlways ? null : 'webp');
    if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
        "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
        ">$name</a></h3><a href=$base{$item['charId']}>$img</a></article>";
    if ($withalt) {
        foreach ($item['asset2'] as $asset) {
            $img = imageTag($asset, ['avif', 'webp'], "", array('store-img'));
            if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
                "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
                ">$name (Alt)</a></h3><a href=$base{$item['charId']}>$img</a></article>";
        }
        $img = imageTag($item['main-ai'], ['webp', 'png'], "", array('store-img'), null);
        if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
            "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
            ">$name (Ai) </a></h3><a href=$base{$item['charId']}>$img</a></article>";
        foreach ($item['assetAi'] as $asset) {
            $img = imageTag($asset, ['webp', 'png'], "", array('store-img'), null);
            if ($img) $characters[] = "<article class=store-div data-c=$_boxcolor is=shadowboxed-" .
                "hover id=sec-{$item['charId']}><h3 class=charname><a href=$base{$item['charId']}" .
                ">$name (Ai Alt)</a></h3><a href=$base{$item['charId']}>$img</a></article>";
        }
    }
}
