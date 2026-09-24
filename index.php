<?php $width = 'smaller';

use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
require_once __DIR__ . '/matchUniverses.php';

global $characters, $uniSlugName, $data, $unidata;
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');
$nightLightOverride = array_key_exists('night', $_GET) && "{$_GET['night']}";
$aiAlways = array_key_exists('withai', $_GET) && "{$_GET['withai']}";
require_once __DIR__ . '/get-gallery.php';
if ($uniSlugName === null) {
    http_response_code(403);
    exit('<!DOCTYPE html><meta CHARSET=UTF-8>$uniSlugName doesnt exist');
}

$title = $uniSlugName === 'main' ? 'Favicond\'s Character Gallery' . ($aiAlways ? "\x20(Ai Mode)" : '') :
        matchUniverses($uniSlugName) . ($aiAlways ? "\x20(Ai Mode)" : '') . "\x20(Favicond's Character Gallery)";
create_head3($title, ['base' => '/gallery/',
        'desc' => 'Explore the character gallery of Favi Favicond at ANTRequest.nl!',
        'class' => array('smaller'), $nightLightOverride, 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        ], 'canonical' => $uniSlugName === 'main' ? '/' : "gallery/universe/$uniSlugName/",
        'stylelinks' => ['statics/cssx.css', 'statics/ddDL-table.css'],
        'borderColor' => ($aiAlways ? '#ff00ff' : '#00a8f3'),
        'backColor' => ($aiAlways ? '#a600a6' : '#0073a6'),
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <details class='border alt-uni'>
        <summary>Alternate Universes</summary>
        <div><?= "<h2 id=Other-Universes class=altUniStyle>Other Universes</h2>\n";
            ob_start(fn(string $string): string => preg_replace('/>\\s+</',
                    '><', preg_replace('/\\s+/', "\x20", $string)));
            unidata('main', $unidata['unidata']['main']);
            unset($unidata['unidata']['main']);
            foreach ($unidata['unidata'] as $key => $item) unidata($key, $item);
            function unidata($key, $item)
            {
                $Universe = matchUniverses($key);
                $univHref = "universe/$key/" ?>
                <article class='store-div div' is=shadowboxed-hover>
                <h3 class=charname><a href="<?= $univHref ?>"><?= $Universe ?></a></h3>
                <a href="<?= $univHref ?>"><img
                            class='store-img em10' width=800 height=1280
                            alt="<?= "Universe thumbnail for $Universe" ?>"
                            src="<?= "imgdata/{$item['hash']}" ?>"></a>
                </article><?= "<!-- $Universe -->";
            }

            ob_end_flush() ?></div>
        <HR>
        <div class=altUniStyle>
            <a href="<?= "universe-ai/$uniSlugName/" ?>">Enable Ai Mode</a>
            <a href="<?= $uniSlugName === 'main' ? '/' : "universe/$uniSlugName/" ?>">Disable Ai Mode</a>
        </div>
    </details>
    <div class=border id=the-store><?= implode('', is_array($characters) ? $characters : array()) ?></div>
</div>
