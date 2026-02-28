<?php use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavLinkTag;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;
use function readCharacterJSON\readCharacterJSON;

// Evanthia
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
} else {
    $width = $smaller;
}
$overflox = 20;
if (preg_match('/\\.store-img\\{width:(\\d+)em;?}/', $width, $matches)) {
    $overflox = $matches[1];
} else {
    $width = "$width.store-img{width:20em;}";
}
$overflox = ".overflox>div,.charname{width:calc({$overflox}em - 2ch);overflow-x:hidden;" .
        "white-space:nowrap;text-overflow:ellipsis;}";
create_head2($title = 'ANT\'s Gallery', ['base' => '/gallery/',
], [new ANTNavLinkTag('stylesheet', ["cssx.css", 'ddDL-table.css']),
        new ANTNavIStyle("$width$overflox"),
], [ANTNavFavicond('https://ANTRequest.nl', $title, true)]);

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
    '1' => '1',
    '2' => '2',
    default => '0',
};
foreach (glob("{$baseURL}htignore/images/*/main.json") as $item) {
    if ($char = readCharacterJSON($item)) {
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
        $universes[] = $char['UniverseId'];
        if (!array_key_exists($array['UniverseId'], $unisort)) $unisort[$array['UniverseId']] = 0;
        $unisort[$array['UniverseId']]++;
        if ($universe !== 'Favicond-All') if ($universe !== $array['UniverseId']) continue;
        $char['UniverseName'] = $array['UniverseId'] = matchUniverses($array['UniverseId']);
        unset($array['charId']);
        $img = imageTag($charId, 'main', $altText, null, $AiArt, ['store-img']);
        if ($img === false) continue;
        if (str_starts_with($width, '/*smallest*/')) {
            $echo = "<div class=store-div id=sec-$charId style=border-top:none><a href=char/$charId>$img</a></div>";
        } else {
            if (str_starts_with($width, '/*smaller*/')) {
                if (array_key_exists('FavicondId', $array))
                    $dataDescriptionList = "<div class=FId>F-ID: {$array['FavicondId']}</div>";
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
            $echo = <<<HTML
            <div class=store-div id=sec-$charId><div class=charname><a href="char/$charId"
            >$name</a></div><a href="char/$charId">$img</a><div>$dataDescriptionList</div></div>
            HTML;
        }
        $char['html'] = "-->" . preg_replace('/[\\r\\n]+/', ' ', $echo) . "<!--\n";
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
<div class=divs>
    <h1><?= $title ?></h1>
    <p>WELCOME to ANTRequest.nl. a hobby site of the Fictional Character Favi Favicond!
        there are a total of <span><?= $characters_total ?></span> characters on the site, and
        <span><?= count($characters) ?></span> of them are displayed below due to the filters.
    <form method=get style=padding:0.5em;border-bottom:none class=border>
        <label><?= 'Icon Size: ' . createSelectElement("iconSize", [
                    'smallest' => 'Smallest',
                    'smaller' => 'Smaller',
                    'normal' => 'Normal',
                    'expand' => 'Expanded',
            ], function ($key) use ($width) {
                echo "<!--\$width=$width; \$key=$key-->";
                return ((str_starts_with($width, '/*smallest*/') && $key === 'smallest') ||
                        (str_starts_with($width, '/*smaller*/') && $key === 'smaller') ||
                        (str_starts_with($width, '/*normal*//*expanded*/') && $key === 'expand')
                        || (str_starts_with($width, '/*normal*/.') && $key === 'normal'));
            }) ?></label>.
        <label><?= 'With Description: ' . createSelectElement("with-desc", [
                    'either' => 'Both', 'with' => 'Yes', 'no' => 'No',
            ], $selectedFilter) ?></label>.
        <label><?= 'With Borders: ' . createSelectElement("with-bord", [
                    'n' => 'Named', 's' => 'Sorted', '1' => 'Yes', '0' => 'No',
            ], $selectedBorder) ?></label>.
        <label><?= 'AiArt: ' . createSelectElement("AiArt", [
                    '2' => 'Only', '1' => 'Show', '0' => 'Hide',
            ], $AiArt) ?></label>.<br>
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
            ], $sorted = (string)($_GET['sorted'] ?? 'UniverseName')) ?></label>.<br>
        <!-- Array.from(document.querySelectorAll('html body div.divs form.border label select[name=\'universe\'] option'), str=>str.value).join(); -->
        <label><?= 'Universe: ' . createSelectElement("universe",
                    (function () use ($universes, $unisort): array {
                        $result = array();
                        foreach ($universes as $universe) {
                            $result[$universe] = matchUniverses($universe) . " ($unisort[$universe])";
                        }
                        return $result;
                    })(), $universe) ?></label>.<br>
        <label><?= 'Sort Order: ' . createSelectElement("reversed", [
                    '0' => 'Normal (A-z, Oldest First)', '1' => 'Reversed (z-A, Newest First)',
            ], ($reversed = !!(match ($_GET['reversed']) {
                '1', 'true' => '1',
                default => '0',
            })) ? '1' : '0') ?></label>.<br>
        <button type=submit>apply filters</button>
    </form>
    <div style=margin-left:0;padding-bottom:1em class=border id=the-store><?= "<!--\n";
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

        foreach ($characters as $character) {
            //if ($character['charId'] !== '17-R') continue;
            if ($selectedBorder) if ($universe !== $character['UniverseId']) {
                $was_null = !is_null($universe);
                $universe = $character['UniverseId'];
                if ($selectedBorder === 'n' || $selectedBorder === 's') {
                    $universeName = htmlspecialchars12(matchUniverses($universe));
                    echo "[--><h2 class=h2-border id=\"secuni-$universe\">";
                    echo "<a href=#secuni-$universe>$universeName</a></h2>";
                    echo "<!--]\n";
                } elseif ($was_null) {
                    echo "[--><hr class=hr-border><!--]\n";
                }
            }
            echo "{$character['html']}";
        }
        echo "-->" ?></div>
</div>
<div class=divs><h2>Definitions</h2>
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
            <dd>A number made of the Legacy Listing and their JoinId. It's not really an id, but the name is kept for
                consistency.
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
