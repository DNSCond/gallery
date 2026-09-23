<?php $width = 'smaller';

use function ANTHeader\create_head3;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
require_once __DIR__ . '/matchUniverses.php';

global $characters, $uniSlugName;
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');
$nightLightOverride = array_key_exists('night', $_GET) && "{$_GET['night']}";
$aiAlways = array_key_exists('withai', $_GET) && "{$_GET['withai']}";
require_once __DIR__ . '/get-gallery.php';
if ($uniSlugName === null) {
    http_response_code(403);
    exit;
}

$title = $uniSlugName === 'main' ? 'Favicond\'s Character Gallery' . ($aiAlways ? "\x20(Ai Mode)" : '') :
        matchUniverses($uniSlugName) . ($aiAlways ? "\x20(Ai Mode)" : '') . "\x20(Favicond's Character Gallery)";

create_head3($title, ['base' => '/gallery/',
        'desc' => 'Explore the character gallery of Favi Favicond at ANTRequest.nl!',
        'class' => array('smaller'), $nightLightOverride, 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
        ], 'canonical' => $uniSlugName === 'main' ? '/' : "gallery/universe/$uniSlugName/",
        'stylelinks' => ['statics/cssx.css', 'statics/ddDL-table.css'],
        'borderColor' =>  ($aiAlways ? '#ff00ff' : '#0073a6'),
        'backColor' => ($aiAlways ? '#a600a6' : '#00a8f3'),
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <details class='border alt-uni'>
        <summary>Alternate Universes</summary>
        <div><?= "<h2 id=Other-Universes class=altUniStyle>Other Universes</h2>\n";
            ob_start(fn(string $string): string => preg_replace('/>\\s+</',
                    '><', preg_replace('/\\s+/', "\x20", $string)));
            function createUniverseIcon(string $universeSlug, $return = false): string
            {
                if ($return) ob_start();
                $matchUniverse = matchUniverses($universeSlug);
                $Universe = htmlspecialchars12($matchUniverse);
                $cont = file_get_contents(__DIR__ . "/htignore/universe-images/$universeSlug/universe-img.webp");
                if ($cont) {
                    $hash = Helpers\Base64Url\base64UrlEncode(hash('sha256', $cont, true));
                } else $hash = 'null';
                $univHref = "universe/$universeSlug/" ?>
                <article class='store-div div' is=shadowboxed-hover>
                <h3 class=charname><a href="<?= $univHref ?>"><?= $Universe ?></a></h3>
                <a href="<?= $univHref ?>"><img
                            class='store-img em10' width=800 height=1280
                            alt="<?= "Universe thumbnail for $Universe" ?>"
                            src="<?= "universe-img/$universeSlug.webp~$hash" ?>"></a>
                </article><?= "<!-- $Universe -->";
                if ($return) return ob_get_clean();
                return '';
            }

            echo '<p class=padleft>These other Universes contain more characters to meet!';
            $versesArray = array();
            createUniverseIcon('main');
            foreach (glob(__DIR__ . '/htignore/universe-images/*/') as $item) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/?$/D', $item, $matches)) {
                    if ($matches[1] === 'main') continue;
                    $versesArray[] = createUniverseIcon($matches[1]); //$matches[1] === 'Favicond-Unknown'
                }
            }
            ob_end_flush();
            echo '<TEMPLATE is=show-onload>' . implode("\n", $versesArray) . '</TEMPLATE>' ?></div>
        <HR>
        <div class=altUniStyle>
            <a href="<?= "universe-ai/$uniSlugName/" ?>">Enable Ai Mode</a>
            <a href="<?= $uniSlugName === 'main' ? '/' : "universe/$uniSlugName/" ?>">Disable Ai Mode</a>
        </div>
    </details>
    <div class=border id=the-store><?= implode('', is_array($characters) ? $characters : array()) ?></div>
</div>
