<?php use function ANTHeader\create_head3;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
global $characters, $uniSlugName, $data, $unidata;
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');
$nightLightOverride = array_key_exists('night', $_GET) && "{$_GET['night']}";
$aiAlways = array_key_exists('withai', $_GET) && "{$_GET['withai']}";
require_once __DIR__ . '/get-gallery.php';
$iconSize = 'smaller';
if (array_key_exists('iconSize', $_GET)) {
    $iconSize = match ($_GET['iconSize']) {
        'toosmall' => 'toosmall',
        'smallest' => 'smallest',
        'normal' => 'smaller',
        'larger' => 'larger',
        default => $iconSize,
    };
}
if (isset($GLOBALS['all'])) {
    $title = 'All of Favicond\'s Character Gallery';
    create_head3($title, ['base' => '/gallery/',
            'desc' => 'Explore the character gallery of Favi Favicond at ANTRequest.nl!',
            'class' => array($iconSize), 'nightLightOverride' => $nightLightOverride, 'bread' => [
                    array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                    array('text' => 'All of Favicond\'s Character Gallery', 'href' => '/gallery/all.php'),
            ], 'stylelinks' => ['statics/cssx.css', 'statics/ddDL-table.css'],
            'borderColor' => ($aiAlways ? '#ff00ff' : '#00a8f3'),
            'backColor' => ($aiAlways ? '#a600a6' : '#0073a6'),
            'canonical' => "/gallery/all.php",
    ]);
    $uniSlugName = 'main';
} else {
    if ($uniSlugName === null) {
        http_response_code(403);
        require_once "{$_SERVER['DOCUMENT_ROOT']}/require/403document.php";
    }
    $key = $uniSlugName;
    $ai = $aiAlways ? "-ai" : '';
    $title = $uniSlugName === 'main' ? 'Favicond\'s Character Gallery' . ($aiAlways ? "\x20(Ai Mode)" : '') :
            matchUniverses($uniSlugName) . ($aiAlways ? "\x20(Ai Mode)" : '') . "\x20(Favicond's Character Gallery)";
    create_head3($title, ['base' => '/gallery/',
            'desc' => 'Explore the character gallery of Favi Favicond at ANTRequest.nl!',
            'class' => array($iconSize), 'nightLightOverride' => $nightLightOverride, 'bread' => [
                    array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                    $uniSlugName !== 'main' ? array('text' => matchUniverses($uniSlugName),
                            'href' => "universe/$uniSlugName/") : null,
            ], 'canonical' => $key === 'main' && !$ai ? '/gallery/' : "gallery/universe$ai/$key/",
            'stylelinks' => ['statics/cssx.css', 'statics/ddDL-table.css'],
            'borderColor' => ($aiAlways ? '#ff00ff' : '#00a8f3'),
            'backColor' => ($aiAlways ? '#a600a6' : '#0073a6'),
    ]);
} ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <script src=statics/index.js type=module></script>
    <details class='border alt-uni'>
        <summary>Alternate Universes</summary>
        <div><?= "<h2 id=Other-Universes class='altUniStyle h2-border'>Other Universes</h2>\n";
            //ob_start(function (string $string): string {
            //$string = preg_replace('/\\s+/', "\x20", $string);
            //$string = preg_replace('/>\\s+</', '><', $string);
            //return ltrim($string);});
            $newUniData = $unidata;
            unidata('main', $newUniData['unidata']['main']);
            unset($newUniData['unidata']['main']);
            unidata('Heroes', $newUniData['unidata']['Heroes']);
            unset($newUniData['unidata']['Heroes']);
            $recycleData = $newUniData['unidata']['RecycleReady'];
            $unknownData = $newUniData['unidata']['Favicond-Unknown'];
            unset($newUniData['unidata']['Favicond-Unknown']);
            unset($newUniData['unidata']['RecycleReady']);
            foreach ($newUniData['unidata'] as $key => $item) unidata($key, $item);
            unidata('Favicond-Unknown', $unknownData);
            unidata('RecycleReady', $recycleData);
            function unidata($key, $item): void
            {
                global $ai, $iconSize;
                if (!array_key_exists('noAi', $item)) return;
                $Universe = matchUniverses($key);
                $image = false;
                if ($ai) $image = imageTag($Universe, $item['wiAi'], ['png'],
                        "Ai Universe thumbnail for $Universe", array('store-img', 'em10'), null);
                if ($image === false) $image = imageTag($Universe, $item['noAi'],
                        ['webp'], "Universe thumbnail for $Universe", array('store-img', 'em10'));
                $univHref = $key === 'main' && !$ai ? '/' : "universe$ai/$key/";

                echo "<article class='store-div div' is=shadowboxed-hover>";
                echo "<h3 class=charname><a href='$univHref'>$Universe</a></h3>";
                echo "<a href='$univHref'>$image</a>";
                if ($iconSize !== 'toosmall' && $iconSize !== 'smallest') {
                    echo "<div class='charname chars'>Chars: {$item['chars']}</div>";
                }
                echo '</article>';
            }

            //ob_end_flush();
            global $Favi_verse ?></div>
        <HR>
        <div class=altUniStyle>
            <a href="<?= "universe-ai/$uniSlugName/" ?>">Enable Ai Mode</a>,
            <a href="<?= $uniSlugName === 'main' ? '/' : "universe/$uniSlugName/" ?>">Disable Ai Mode</a>,
            <a href="<?= 'other' ?>">See Miscellaneous Ai Images</a>.
        </div>
    </details>
    <nav class=border id=the-store><?= implode('', is_array($characters) ? $characters : array());
        global $ai;
        $image = false;
        $classes = ['introImage', 'uni'];
        $Universe = matchUniverses($uniSlugName);
        $item = $unidata['unidata'][$uniSlugName];
        if ($item && $uniSlugName !== 'main') {
            $alt = "Universe thumbnail for $Universe";
            if ($ai) $image = imageTag($Universe, $item['wiAi'],
                    ['png', 'jpg', 'jpeg'], "Ai $alt", $classes, null);
            if ($image === false) $image = imageTag($Universe, $item['noAi'],
                    ['webp'], $alt, $classes);
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            $Universe = \Helpers\htmlspecialchars12($Universe);
            $image = $image === false ? false : "<div class='divs center'><h2>$Universe</h2>$image";
        }
        if ($image === false) $image = '' ?></nav>
</div><?= $image . ($image ? '</div>' : '') ?><!-- Object -->
