<?php use ANTHeader\ANTNavIStyle;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavFavicond;
use function Helpers\htmlspecialchars12;
use function readCharacterJSON\readCharacterJSON;
use function ANTHeader\ANTNavBinary;
use ANTHeader\ANTNavLinkTag;

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
        'expand' => "$width/*expanded*/",
        'dev' => "$width/*dev*/",
        'smaller' => "$smaller",
        default => "$width",
    };
} else $width = $smaller;
$overflox = 20;
if (preg_match('/\\.store-img\\{width:(\\d+)em;?}/', $width, $matches)) {
    $overflox = $matches[1];
} else {
    $width = "$width.store-img{width:20em;}";
}
$overflox = ".overflox>div,.charname{width:calc({$overflox}em - 2ch);overflow-x:hidden;" .
        "white-space:nowrap;text-overflow:ellipsis;}";
create_head2($title = 'ANT\'s Character Gallery', ['base' => '/gallery/',
        'desc' => 'Explore the official character gallery of Favi Favicond at ANTRequest.nl!',
], [
        new ANTNavLinkTag('stylesheet', ["cssx.css", 'ddDL-table.css']),
        new ANTNavLinkTag('canonical', 'https://antrequest.nl'),
        new ANTNavIStyle("$width$overflox"),
], [
        ANTNavFavicond('https://ANTRequest.nl', $title, true),
        ANTNavBinary('/gallery/ascii-table.php', 'Ascii Table'),
]);

function createSelectElement(string $name, array $options, null|string|callable|array $select = null): string
{
    $name = htmlspecialchars12($name);
    $result = array("<select name=\"$name\">");
    foreach ($options as $key => $val) {
        $selected = false;
        if (is_string($select)) {
            $selected = $select === "$key";
        } elseif (is_callable($select)) {
            $selected = !!$select("$key", $val);
        } elseif (is_array($select)) {
            $selected = in_array("$key", $select);
        }
        $key = htmlspecialchars12($key);
        $val = htmlspecialchars12($val);
        $selected = $selected ? 'selected' : '';
        $result[] = "<option $selected value=\"$key\">$val</option>";
    }
    return implode('', $result) . '</select>';
}

/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}sorters.php";
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}readCharacterJSON.php";
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}dataDescriptionList.php";
$selectedFilter = match (array_key_exists('with-desc', $_GET) ? ($_GET['with-desc']) : 'either') {
    'no' => 'no',
    'with' => 'with',
    default => 'either',
};
$selectedBorder = match (array_key_exists('with-bord', $_GET) ? ($_GET['with-bord']) : '0') {
    '1' => '1',
    'n' => 'n',
    's' => 's',
    default => '0',
};

$unisort = array();
$universes = array();
$characters_total = 0;
$characters = array();
$universe = $_GET['universe'] ?? 'Favicond-All';
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}imageTag.php";

$AiArt = match ($_GET['AiArt']) {
    '1' => '1', // show
    '2' => '2', // only
    default => '0', // hide
};
$gallery = !!$_GET['gallery'];
foreach (glob("{$baseURL}htignore/images/*/main.json") as $item) {
    if ($char = readCharacterJSON($item)) {
        if ($char['json']['private']) continue;
        $char = $char['data'];
        $characters_total++;
        if ($selectedFilter === 'with' || $selectedFilter === 'no') {
            $file_exists = file_exists("{$baseURL}htignore/images/{$char['charId']}/main.php");
            if (($selectedFilter === 'with' && !$file_exists) || ($selectedFilter === 'no' && $file_exists))
                continue;
        }
        $name = $char['name'];
        $charId = $char['charId'];
        $altText = "$name's Main Appearance";
        $dataDescriptionList = '';
        $array = $char;
        $char['subchars'] = array();
        $universes[] = $char['UniverseId'];
        if (!array_key_exists($array['UniverseId'], $unisort)) $unisort[$array['UniverseId']] = 0;
        $unisort[$array['UniverseId']]++;
        if ($universe !== 'Favicond-All') if ($universe !== $array['UniverseId']) continue;
        $char['UniverseName'] = $array['UniverseId'] = matchUniverses($array['UniverseId']);
        unset($array['charId']);
        $img = imageTag($charId, 'main', $altText, null, $AiArt, ['store-img']);
        if (!str_starts_with($width, '/*smaller*/')) if ($img === false) continue;
        if (str_starts_with($width, '/*smallest*/')) {
            $echo = "<div class=store-div id=sec-$charId style=border-top:none><a href=char/$charId>$img</a></div>";
        } else {
            if (str_starts_with($width, '/*smaller*/')) {
                //$dataDescriptionList = "<div class=FId>F-ID: {$array['FavicondId']}</div>";
                foreach (glob("{$baseURL}htignore/images/$charId/*gallery.*.png") as $alternate) {
                    if (str_contains($alternate, 'watermarked')) continue;
                    if (preg_match('/(ai\\.)?gallery\\.([^.]+)\\.png$/D', $alternate, $variant)) {
                        if ($variant[1] && !$AiArt) continue;
                        if ($AiArt === '2' && !$variant[1]) continue;
                        $newchar = imageTag($charId, $variant[2], "Alternate of $name",
                                'gallery', $variant[1], ['store-img']);
                        $char['subchars'][] = "<article class=store-div><h3 class=charname><a href=char/$charId"
                                . "#gallery>$name (Alt)</a></h3><a href=char/$charId>$newchar</a></article>";
                    }
                }
            } else if (str_starts_with($width, '/*normal*/')) {
                if (str_starts_with($width, '/*normal*//*dev*/')) {
                    $array['internalName'] = $charId;
                } elseif (!str_starts_with($width, '/*normal*//*expanded*/')) {
                    unset($array['LastModified']);
                    unset($array['registerDate']);
                    unset($array['listing']);
                    unset($array['join-Id']);
                }
                foreach (['creationDate-epoch', 'LastModified-epoch', 'registerDate-epoch'] as $rm) {
                    unset($array[$rm]);
                }
                $dataDescriptionList = dataDescriptionList(
                        $array, ['overflox'], [
                        'registerDate' => '#what-is-registerDate',
                        'creationDate' => '#what-is-creationDate',
                        'LastModified' => '#what-is-LastModified',
                        'FavicondId' => '#what-is-FavicondId',
                        'UniverseId' => '#what-is-UniverseId',
                ]);
            }
            $echo = "<article class=store-div id=sec-$charId><h3 class=charname><a href=char/$charId>" .
                    "$name</a></h3><a href=char/$charId>$img</a><div>$dataDescriptionList</div></article>";
        }
        if ($img === false && count($char['subchars']) === 0) continue;
        elseif ($img === false && count($char['subchars']) !== 0) $char['subonly'] = true;
        $char['html'] = preg_replace('/[\\r\\n]+/', ' ', $echo);
        if (!preg_match('/^\\d{2}$/D', $char['listing'])) $char['listing'] = '00';
        if (!preg_match('/^\\d{2}$/D', $char['join-Id'])) $char['join-Id'] = '00';
        $characters[] = $char;
    }
}
$unisort['Favicond-All'] = $characters_total;
array_unshift($universes, 'Favicond-All');
require_once "loginService.php";
global $JWT;
if (is_array($token = $JWT->validate("{$_COOKIE['htpasswd']}"))) {
    $currentUsername = htmlspecialchars12("{$token['username']}");
    echo <<<ACCOUNT
    <div style="height:3em;background-color:white;border-bottom: 4px solid #e689bf;">
    <div style="width:88%;max-width:88%;margin:auto">ANT//$currentUsername</div></div>
    ACCOUNT. "\n\n";
} ?>
<main class=divs>
    <h1><?= $title ?></h1>
    <p>Welcome to ANTRequest.nl. a hobby site of the Fictional Character Favi Favicond!
        there are a total of <span><?= $characters_total ?></span> characters on the site, and
        <span><?= count($characters) ?></span> of them are displayed below due to the filters.
    <form method=get style=padding:0.5em;border-bottom:none class=border>
        <details>
            <summary>Filter Options</summary>
            <div class=grid-3x>
                <label><?= 'Icon Size: ' . createSelectElement("iconSize", [
                            'smallest' => 'Smallest',
                            'smaller' => 'Smaller',
                            //'normal' => 'Normal',
                            //'expand' => 'Expanded',
                    ], function ($key) use ($width) {
                        echo "<!--\$width=$width; \$key=$key-->";
                        return ((str_starts_with($width, '/*smallest*/') && $key === 'smallest') ||
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
                    ], ($reversed = !!(match ($_GET['reversed']) {
                        '1', 'true' => '1',
                        default => '0',
                    })) ? '1' : '0') ?></label>
                <label><?= 'Include alternate Depictions: ' . createSelectElement("gallery", [
                            '0' => 'No', '1' => 'Yes',
                    ], (int)$gallery) ?></label>
                <button type=submit>apply filters</button>
            </div>
        </details>
    </form>
    <div style=margin-left:0;padding-bottom:1em class=border id=the-store><?php
        if ($type = match ($sorted) {
            'displayName' => 'displayName',
            'creationDate' => 'creationDate',
            'LastModified' => 'LastModified',
            'registerDate' => 'registerDate',
            'UniverseName' => 'UniverseName',
            'join-Id', 'joinId' => 'join-Id',
            'UniverseId' => 'UniverseId',
            'listing' => 'listing',
            'random' => 'random',
            default => '0',
        }) {
            if ($type === 'UniverseName' || $type === 'UniverseId') {
                $typeKey = "creationDate-epoch";
                usort($characters, fn($a, $b) => $a[$typeKey] <=> $b[$typeKey]);
                lexiosort($characters, $type, true, function ($_, $__, $x, $y) {
                    $typeKey = 'UniverseId';
                    $mustMatch = 'Favicond-Main';
                    if ($x[$typeKey] === $mustMatch && $y[$typeKey] === $mustMatch) return +0;
                    if ($x[$typeKey] === $mustMatch) return -1;
                    if ($y[$typeKey] === $mustMatch) return +1;
                    return +0;
                });
            } elseif ($type === 'displayName') {
                jssort($characters, 'name');
            } elseif ($type === 'join-Id' || $type === 'listing') {
                $typeKey = $type;
                usort($characters, function ($x, $y) use ($typeKey) {
                    $mustMatch = 0;
                    if (+$x[$typeKey] === $mustMatch && +$y[$typeKey] === $mustMatch) return +0;
                    if (+$x[$typeKey] === $mustMatch) return +1;
                    if (+$y[$typeKey] === $mustMatch) return -1;
                    return +$x[$typeKey] <=> +$y[$typeKey];
                });
            } elseif ($type === 'creationDate' || $type === 'LastModified' || $type === 'registerDate') {
                $typeKey = "$type-epoch";
                usort($characters, fn($a, $b) => $a[$typeKey] <=> $b[$typeKey]);
            } else {
                shuffle($characters);
            }
        }
        if ($reversed) $characters = array_reverse($characters);
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
            if (!$character['subonly']) echo str_replace('fetchpriority=auto loading=lazy',
                    $settings, "{$character['html']}");
            foreach ($character['subchars'] as $char) {
                $settings = 'fetchpriority=' . (++$index <= 3 ? 'high' : 'auto');
                if ($index > 15) $settings = "$settings loading=lazy";
                echo str_replace('fetchpriority=auto loading=lazy', $settings, $char);
            }
        } ?></div>
</main>
<div class=divs>
    <h2>Definitions</h2>
    <dl class=descLi>
        <div>
            <dt id=what-is-registerDate><dfn>registerDate</dfn></dt>
            <dd>The date when the <a href=#what-is-FavicondId>FavicondId</a> is assigned to the character. If a
                character is repurposed, then <a href=#what-is-creationDate>creationDate</a> and
                <a href=#what-is-LastModified>LastModified</a> will reflect the new
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
<div class=divs><?= '<h2 id=hrefs>Links</h2><ul class=margin-tb>';
    // base = /gallery/
    foreach (['/layerzip/' => 'LayerZip: a program independent way to store 2d image' .
            ' layers using zip deflate, png, and a json file.', 'admin.php' => 'Admin',
                     'comics' => 'Comics'] as $href => $name) {
        echo "<li><a href=$href>$name</a>";
    }
    echo "</ul>" ?></div>
