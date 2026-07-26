<?php use function readCharacterJSON\matchColor;
use function readCharacterJSON\readCharacterJSON;

require_once __DIR__ . "/imageTag.php";
require_once __DIR__ . "/settings.php";
require_once __DIR__ . "/readCharacterJSON.php";
$reversed = !!(match ($_GET['reversed']) {
    '1', 'true' => '1',
    default => '0',
});
$max = 25;
$unisort = array();
$universes = array();
$characters = array();
$characters_total = 0;
$customCharacters = array();
global $width, $gallery, $universe, $AiArt, $sorted;
if (!isset($width)) $width = '/*smaller*/.store-img{width:10em}.store-div{margin:0.5em 0 0 0.5em;}';
if (array_key_exists('chars', $_GET)) {
    $canonical = '';
    foreach (explode(',', "{$_GET['chars']}") as $item_input) {
        if ($max-- <= 0) break;
        if (preg_match('/^([a-zA-Z0-9\\-]+)\\/([a-zA-Z0-9\\-]+)$/D', $item_input, $matches)) {
            $item_type = __DIR__ . "/htignore/" . ($matches[1] === 'main' ? 'images' : "universe-images/$matches[1]") .
                "/$matches[2]/main.json";
            $imageDirector = $matches[1] === 'main' ? 'images' : $matches[1];
            $baseDirectory = $matches[1] === 'main' ? 'images' : "universe-images/$matches[1]";
            $base = $imageDirector !== 'images' ? "universe/$imageDirector/" : 'char/';
            // todo clean up
            if ($char = readCharacterJSON($item_type)) {
                if (array_key_exists('location', $char)) {
                    continue;
                }
                if (!is_array($char['json'])) continue;
                if (array_key_exists('private', $char['json']))
                    if ($char['json']['private']) continue;
                $characters_total++;
                $customCharacters[] = "$matches[1]/$matches[2]";
                if (array_key_exists('aichar', $char['json']))
                    if (!($char['json']['aichar'] && $AiArt)) continue;
                $_boxcolor = array_key_exists('primaryColor', $char['json'])
                    ? matchColor($char['json']['primaryColor']) : '#00a8f3';
                if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
                $json = $char['json'];
                $char = $char['data'];
                $name = $char['name'];
                $charId = $char['charId'];
                $dataDescriptionList = '';
                $altText = "$name's Main Appearance";
                $array = $char;
                $char['subchars'] = array();
                $universes[] = $char['UniverseId'];
                if (!array_key_exists($array['UniverseId'], $unisort)) {
                    $unisort[$array['UniverseId']] = 0;
                }
                $unisort[$array['UniverseId']]++;
                $char['UniverseName'] = $array['UniverseId'] = matchUniverses($array['UniverseId']);
                unset($array['charId']);
                $char['image'] = $img = imageTag($charId, 'main', $altText,
                    null, $AiArt, ['store-img'], $baseDirectory);
                if (!str_starts_with($width, '/*smaller*/')) if ($img === false) continue;
                if (str_starts_with($width, '/*smallest*/')) {
                    $echo = "<div class=store-div id=sec-$charId style=border-top:none><a href=$base$charId>$img</a></div>";
                    if ($gallery) createAlternates($charId, $char, $name,
                        $AiArt, 'smallest', $_boxcolor);
                } else {
                    if (str_starts_with($width, '/*smaller*/')) {
                        if ($gallery) createAlternates($charId, $char, $name, $AiArt,
                            'smaller', $_boxcolor);
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
                    $echo = "<article class=store-div style=--box-color:$_boxcolor; is=shadowboxed-hover id=sec-" .
                        "$charId><h3 class=charname><a href=$base$charId>$name</a></h3><a href=" .
                        "$base$charId>$img</a><div>$dataDescriptionList</div></article>";
                }
                if ($img === false && count($char['subchars']) === 0) continue;
                elseif ($img === false && count($char['subchars']) !== 0) $char['subonly'] = true;
                $char['html'] = preg_replace('/[\\r\\n]+/', ' ', $echo);
                if (!preg_match('/^\\d{2}$/D', $char['listing'])) $char['listing'] = '00';
                if (!preg_match('/^\\d{2}$/D', $char['join-Id'])) $char['join-Id'] = '00';
                $characters[] = $char;
            }
            // todo clean up end
        }
    }
} else {
    $customCharacters = null;
    $canonical = null;
}
