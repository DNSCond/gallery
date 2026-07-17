<?php

use function readCharacterJSON\matchColor;
use function readCharacterJSON\readCharacterJSON;

require_once __DIR__ . "/readCharacterJSON.php";
require_once __DIR__ . "/dataDescriptionList.php";
require_once __DIR__ . "/imageTag.php";
require_once __DIR__ . "/sorters.php";

$reversed = !!(match ($_GET['reversed']) {
    '1', 'true' => '1',
    default => '0',
});
$unisort = array();
$universes = array();
$characters = array();
$characters_total = 0;
global $baseDirectory, $imageDirector;
global $width, $selectedFilter, $selectedBorder;
global $gallery, $universe, $AiArt, $sorted;
require_once __DIR__ . "/settings.php";
if (!isset($width)) $width = '/*smaller*/.store-img{width:10em}.store-div{margin:0.5em 0 0 0.5em;}';
$base = $imageDirector !== 'images' ? "universe/$imageDirector/" : 'char/';

foreach (glob(__DIR__ . "/htignore/$baseDirectory/*/main.json") as $item) {
    if ($char = readCharacterJSON($item)) {
        if (array_key_exists('private', $char['json']))
            if ($char['json']['private']) continue;
        $characters_total++;
        if (array_key_exists('aichar', $char['json']))
            if (!($char['json']['aichar'] && $AiArt)) continue;
        $_boxcolor = array_key_exists('primaryColor', $char['json'])
            ? matchColor($char['json']['primaryColor']) : '#00a8f3';
        if (!str_starts_with($_boxcolor, '#')) $_boxcolor = "#$_boxcolor";
        $json = $char['json'];
        $char = $char['data'];
        if ($selectedFilter === 'with' || $selectedFilter === 'no') {
            $file_exists = file_exists(__DIR__ . "/htignore/$baseDirectory/{$char['charId']}/main.php");
            if (array_key_exists('noOpener', $json)) $file_exists = !$json['noOpener'];
            if (($selectedFilter === 'with' && !$file_exists) || ($selectedFilter === 'no' && $file_exists))
                continue;
        }
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
        if ($universe !== 'Favicond-All') if ($universe !== $array['UniverseId']) continue;
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
                //$dataDescriptionList = "<div class=FId>F-ID: {$array['FavicondId']}</div>";
                if ($gallery) createAlternates($charId, $char, $name, $AiArt, 'smaller', $_boxcolor);
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
}
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
function createAlternates(string $charId, array &$char, string $name, int $AiArt,
                          string $type, string $_boxcolor = '#00a8f3'): void
{
    global $baseDirectory, $base;
    foreach (glob(__DIR__ . "/htignore/$baseDirectory/$charId/*gallery.*.png") as $alternate) {
        if (str_contains($alternate, 'watermarked')) continue;
        if (preg_match('/(ai\\.)?gallery\\.([^.]+)\\.png$/D', $alternate, $variant)) {
            if ($variant[1] && !$AiArt) continue;
            if ($AiArt === 2 && !$variant[1]) continue;
            $newchar = imageTag($charId, $variant[2], "Alternate of $name",
                'gallery', $variant[1], ['store-img'], $baseDirectory);
            if ($type === 'smallest') {
                $char['subchars'][] = "<div class=store-div id=sec-$charId style="
                    . "border-top:none><a href=$base$charId>$newchar</a></div>";
            } else {
                $char['subchars'][] = "<article class=store-div style=--box-color:$_boxcolor; is"
                    . "=shadowboxed-hover><h3 class=charname><a href=$charId#gallery>"
                    . "$name (Alt)</a></h3><a href=$base$charId>$newchar</a></article>";
            }
        }
    }
}
