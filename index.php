<?php // date_default_timezone_set('UTC');
use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavOption;
use ANTHeader\ANTNavLinkTag;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavBinary;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\ANTNavFavicond;
use function Helpers\htmlspecialchars12;

date_default_timezone_set('UTC');
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
$baseURL = __DIR__ . '/';
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}JWT.php";
$width = '/*normal*/';
$smaller = '/*smaller*/.store-img{width:10em}.store-div{margin:0.5em 0 0 0.5em;}';
if (array_key_exists('iconSize', $_GET)) {
    $width = match ("{$_GET['iconSize']}") {
        'smallest' => '/*smallest*/.store-img{width:7em}.store-div{margin:0.5em 0 0 0.5em;}',
        'toosmall' => '/*smallest*//*toosmall*/.store-img{width:5em}.store-div{margin:0.5em 0 0 0.5em;}',
        'expand' => "$width/*expanded*/",
        'dev' => "$width/*dev*/",
        'smaller' => "$smaller",
        default => "$width",
    };
} else $width = $smaller;
$overflox = 20;
if (preg_match('/\\.store-img\\{width:(\\d+)em;?}/', $width, $matches)) {
    $overflox = $matches[1];
} else $width = "$width.store-img{width:20em;}";
$inverted = isset($GLOBALS['inverted']);
$overflox = ".overflox>div,.charname{width:calc({$overflox}em - 2ch);" .
        "overflow-x:hidden;white-space:nowrap;text-overflow:ellipsis;}";
$baseDirectory = 'images';
$imageDirector = 'images';
$canonicalPath = '/';
$uniname = 'Main';
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^[a-zA-Z0-9\\-]+$/D', "{$_GET['uni']}")) {
        if (file_exists(__DIR__ . '/htignore/universe-images/' . ($uni = $_GET['uni']))) {
            $canonicalPath = "/gallery/universe/$uni/";
            $baseDirectory = "universe-images/$uni";
            $uniname = $imageDirector = "$uni";
        }
    }
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/gallery/matchUniverses.php";
create_head2($title = 'ANT\'s Character Gallery', ['base' => '/gallery/',
        'desc' => 'Explore the official character gallery of Favi Favicond at ANTRequest.nl!',
], [new ANTNavLinkTag('stylesheet', ["cssx.css", 'ddDL-table.css']),
        new ANTNavLinkTag('canonical', "https://antrequest.nl$canonicalPath"),
        new ANTNavIStyle($inverted ? 'main img {filter:invert(100%)}' : "/*\$inverted*/"),
        new ANTNavIStyle('.ShadowBoxedHover{transition:transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;}' .
                '.ShadowBoxedHover:hover{box-shadow: 5px 5px 4px var(--box-color);transform: translate(-4px, -4px);}'),
        new ANTNavIStyle("$width$overflox/**/.store-div{vertical-align: bottom;}"),
], array_merge([ANTNavFavicond('https://ANTRequest.nl', $title, $canonicalPath === '/')],
        $canonicalPath !== '/' ? [ANTNavReddcond($canonicalPath, matchUniverses($uniname), true)] : array(),
        [ANTNavBinary('/gallery/ascii-table.php', 'Ascii Table'), new ANTNavOption(
                '/dollmaker3/', '/dollmaker2/icon/endpoint.php?preset=Bee',
                'dollmakerV5 ANT', new Color('a68300'),
                new Color('fff100')),]));
require_once "{$_SERVER['DOCUMENT_ROOT']}/gallery/createSelectElement.php";
global $characters_total, $reversed, $characters;
global $width, $selectedFilter, $selectedBorder;
global $gallery, $universe, $AiArt, $sorted;
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}characters.php";

$unisort = array();
$universes = array();

$unisort['Favicond-All'] = $characters_total;
array_unshift($universes, 'Favicond-All');
require_once "loginService.php";
global $JWT;
if (is_array($token = $JWT->validate("{$_COOKIE['htpasswd']}"))) {
    $currentUsername = htmlspecialchars12("{$token['username']}");
    echo '<div style="height:3em;background-color:white;border-bottom:4px solid #e689bf;">';
    echo "<div style=width:88%;max-width:88%;margin:auto>ANT//$currentUsername</div></div>";
}
echo '<!-- TEMPLATE ';
ob_start() ?>
<template id=MAMNode>
    <!--suppress CssUnresolvedCustomProperty -->
    <style>
        :host {
            display: block;
            position: relative;
            width: var(--width);
            height: var(--height);
        }
    </style>
</template>
<template id=MAMTree>
    <!--suppress CssUnresolvedCustomProperty -->
    <style>
        :host, picture {
            max-width: var(--width);
            max-height: var(--height);
        }

        :host {
            position: absolute;
        }
    </style>
    <slot></slot>
</template>
<!--<?= '-->' . preg_replace('/\\s+/', " ",
        ob_get_clean()) . ' /TEMPLATE ';
global $Favi_verse ?>-->
<!--<script type=module src=MAM.js></script>-->
<script type=module src=JSONScript.js>//gmdate('M d H:i:s Y \\G\\M\\T', +$_SERVER['REQUEST_TIME']),</script>
<script type=application/json is=output-script><?= json_encode(
            $Favi_verse, JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type=module><?= "class ShadowBoxedHover extends HTMLElement {connectedCallback() {this.classList.add('Shadow"
    . "BoxedHover');}} customElements.define('shadowboxed-hover', ShadowBoxedHover, {extends:'article'});" ?></script>
<script type=module>
    class ShowOnload extends HTMLTemplateElement {
        #emptied = false;

        connectedCallback() {
            if (this.#emptied) return;
            this.#emptied = true;
            while (this.content.firstElementChild) {
                this.before(this.content.firstElementChild);
            }
        }
    }

    customElements.define('show-onload', ShowOnload, {extends: 'template'});
</script>
<main class=divs>
    <h1><?= $title ?></h1>
    <p>Welcome to ANTRequest.nl. a hobby site of the Fictional Character Favi Favicond!
        there are a total of <span><?= "$characters_total\x20characters on the site";
            if ($characters_total !== ($integer = count($characters)))
                echo ", and $integer of them are displayed below due to the filters." ?></span></p>
    <!--<div hidden><mam-tree style="--width:50em;--height:50em;"><mam-node img-src=icon.png
    img-width=1024 img-height=1024 img-alt="Alt Text"></mam-node></mam-tree></div>-->
    <form method=get class=border style=padding:0.5em;border-bottom:none>
        <details>
            <summary>Filter Options</summary>
            <div class=grid-3x>
                <label><?= 'Icon Size: ' . createSelectElement("iconSize", [
                            'toosmall' => 'Too Small', 'smallest' => 'Smallest', 'smaller' => 'Smaller',
                    ], function ($key) use ($width) {
                        return ((str_starts_with($width, '/*smallest*//*toosmall*/') && $key === 'toosmall') ||
                                (str_starts_with($width, '/*smallest*/.') && $key === 'smallest') ||
                                (str_starts_with($width, '/*smaller*/') && $key === 'smaller') ||
                                (str_starts_with($width, '/*normal*//*expanded*/') && $key === 'expand')
                                || (str_starts_with($width, '/*normal*/.') && $key === 'normal'));
                    }) ?></label>
                <label><?= 'With Description: ' . createSelectElement("with-desc", [
                            'either' => 'Both', 'with' => 'Yes', 'no' => 'No',
                    ], $selectedFilter) ?></label>
                <label><?= 'With Borders: ' . createSelectElement("with-bord", [
                            'n' => 'Named', 's' => 'Sorted', '1' => 'Yes', '0' => 'No',
                    ], $selectedBorder) ?></label>
                <label><?= 'AiArt: ' . createSelectElement("AiArt", [
                            '2' => 'Only', '1' => 'Show', '0' => 'Hide',
                    ], $AiArt) ?></label>
                <label><?= 'Sorted: ' . createSelectElement("sorted", [
                            '0' => 'Internal Name',
                            'displayName' => 'Display Name',
                            'UniverseName' => 'Universe Name',
                            'creationDate' => 'Chronologically',
                            'LastModified' => 'Last Updated',
                            'registerDate' => 'Registration Date',
                            'listing' => 'Legacy Listing',
                            'joinId' => 'join Id',
                            'random' => 'Random',
                    ], $sorted = (string)($_GET['sorted'] ?? 'UniverseName')) ?></label>
                <label><?= 'Universe: ' . createSelectElement("universe",
                            (function () use ($universes, $unisort): array {
                                $result = array();
                                foreach ($universes as $universe) {
                                    $result[$universe] = matchUniverses($universe) . " ($unisort[$universe])";
                                }
                                return $result;
                            })(), $universe) ?></label>
                <label><?= 'Sort Order: ' . createSelectElement("reversed", [
                            '0' => 'Normal (A-z, Oldest First)', '1' => 'Reversed (z-A, Newest First)',
                    ], ($reversed ? '1' : '0')) ?></label>
                <label><?= 'Include alternate Depictions: ' . createSelectElement("gallery", [
                            '0' => 'No', '1' => 'Yes',
                    ], (int)$gallery) ?></label>
                <button type=submit>apply filters</button>
            </div>
        </details>
    </form>
    <details style='padding: 0.5em 0.5em 0.5em 0; border-bottom:none' class=border>
        <summary style=padding-left:0.5em>Alternate Universes</summary>
        <div><?= "<h2 id=Other-Universes style=margin-bottom:0;padding-left:0.5em>Other Universes</h2>\n";
            ob_start(fn(string $string): string => preg_replace('/>\\s+</', '><',
                    preg_replace('/\\s+/', "\x20", $string)));
            function createUniverseIcon(string $universeSlug, $return = false): string
            {
                if ($return) ob_start();
                $matchUniverse = matchUniverses($universeSlug);
                $Universe = htmlspecialchars12($matchUniverse);
                $univHref = "/gallery/universe/$universeSlug/" ?>
                <article class=store-div style=--box-color:#00a8f3; is=shadowboxed-hover>
                <h3 class=charname><a href="<?= $univHref ?>"><?= $Universe ?></a></h3>
                <a href="<?= $univHref ?>"><img
                            style=width:10em class=store-img width=800
                            alt="<?= "Universe thumbnail for $Universe" ?>"
                            height=1280 src="<?= "universe-img/$universeSlug.webp" ?>"></a>
                </article><?= "<!-- $Universe -->";
                if ($return) return ob_get_clean();
                return '';
            }

            $Universe = $matchUniverse = 'Main page';
            $universeSlug = 'Main';
            $univHref = "/" ?>
            <p style=padding-left:0.5em>These other Universes contain more characters to meet!
            <article class=store-div style=--box-color:#00a8f3; is=shadowboxed-hover>
                <h3 class=charname><a href="<?= $univHref ?>"><?= $Universe ?></a></h3>
                <a href="<?= $univHref ?>"><img
                            style=width:10em class=store-img width=800
                            alt="<?= "Universe thumbnail for $Universe" ?>"
                            height=1280 src="<?= "universe-img/$universeSlug.webp" ?>"></a>
            </article><?= "<!-- $Universe -->";
            $versesArray = array();
            foreach (glob(__DIR__ . '/htignore/universe-images/*/') as $item) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/?$/D', $item, $matches)) {
                    $versesArray[] = createUniverseIcon($matches[1], $matches[1] === 'Favicond-Unknown');
                }
            }
            ob_end_flush();
            echo '<TEMPLATE is=show-onload>' .
                    implode("\n", $versesArray);
            echo '</TEMPLATE>'; ?></div>
    </details>
    <div style=margin-left:0;padding-bottom:1em class=border id=the-store><?= '<!-- XHTTP -->';
        if ($selectedBorder) {
            $unisort = array();
            foreach ($characters as $character) {
                if (!array_key_exists($character['UniverseId'], $unisort)) {
                    $unisort[$character['UniverseId']] = array();
                }
                $unisort[$character['UniverseId']][] = $character;
            }
            $keys = array_keys($unisort);
            if ($selectedBorder === 's') {
                lexiosort($keys, null, true, function ($_, $__, $x, $y) {
                    $mustMatch = 'Favicond-Main';
                    if ($x === $mustMatch && $y === $mustMatch) return +0;
                    if ($x === $mustMatch) return -1;
                    if ($y === $mustMatch) return +1;
                    return +0;
                }, 'matchUniverses');
            }
            $characters = array();
            foreach ($keys as $key) {
                foreach ($unisort[$key] as $item) {
                    $characters[] = $item;
                }
            }
        }
        $universe = null;
        $queryString = htmlspecialchars12("?{$_SERVER['QUERY_STRING']}");
        if (!($selectedBorder === 'n' || $selectedBorder === 's')) {
            echo "<h2 class=h2-border id=Characters>Characters</h2>";
        }
        $index = 0;
        foreach ($characters as $character) {
            if ($selectedBorder) if ($universe !== $character['UniverseId']) {
                $was_null = !is_null($universe);
                $universe = $character['UniverseId'];
                if ($selectedBorder === 'n' || $selectedBorder === 's') {
                    $universeName = htmlspecialchars12(matchUniverses($universe));
                    echo "<h2 class=h2-border id=\"secuni-$universe\"><a href=\"" .
                            "$queryString#secuni-$universe\">$universeName</a>";
                    echo "\x3c/h2>";
                } elseif ($was_null) {
                    echo "\x3chr class=hr-border>";
                }
            }
            $settings = 'fetchpriority=' . (++$index <= 3 ? 'high' : 'auto');
            if ($index > 15) $settings = "$settings loading=lazy";
            if (!array__get_key_as_boolean('subonly', $character))
                echo str_replace('fetchpriority=auto loading=lazy',
                        $settings, "{$character['html']}");
            foreach ($character['subchars'] as $char) {
                $settings = 'fetchpriority=' . (++$index <= 3 ? 'high' : 'auto');
                if ($index > 15) $settings = "$settings loading=lazy";
                echo str_replace('fetchpriority=auto loading=lazy', $settings, $char);
            }
        }

        function array__get_key_as_boolean(string $key, array $array): bool
        {
            if (array_key_exists($key, $array)) {
                return (bool)$array[$key];
            } else return false;
        } ?></div>
</main>
<div class=divs>
    <h2>Definitions</h2>
    <dl class=descLi>
        <div>
            <dt id=what-is-registerDate><dfn>registerDate</dfn></dt>
            <dd>The date when the <a href=#what-is-FavicondId>FavicondId</a> is assigned to the character.
                If a character is repurposed, then <a href=#what-is-creationDate>creationDate</a>
                and <a href=#what-is-LastModified>LastModified</a> will reflect the new
                character, but the registerDate will not update.
            </dd>
        </div>
        <div>
            <dt id=what-is-FavicondId><dfn>FavicondId</dfn></dt>
            <dd>A number made of the Legacy Listing and their JoinId.
                It's not really an id, but the name is kept for consistency.
            </dd>
        </div>
        <div>
            <dt id=what-is-creationDate><dfn>creationDate</dfn></dt>
            <dd>The date when the character is created.</dd>
        </div>
        <div>
            <dt id=what-is-LastModified><dfn>LastModified</dfn></dt>
            <dd>The date when the character is publicly updated in lore or appearance.</dd>
        </div>
        <div>
            <dt id=what-is-UniverseId><dfn>UniverseId</dfn></dt>
            <dd>The Universe the character belongs to.</dd>
        </div>
    </dl>
</div>
