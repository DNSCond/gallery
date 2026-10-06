<?php use function Helpers\htmlspecialchars12;
use function ANTHeader\create_head3;

$charId = null;
$uniSlugName = null;
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}")) {
        $uniSlugName = "{$_GET['uni']}";
    }
} else $uniSlugName = 'main';
if (array_key_exists('char', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['char']}")) {
        $charId = "{$_GET['char']}";
    }
}
require_once __DIR__ . '/require.php';
require_once __DIR__ . '/imageTag.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$data = ($unidata = readJSON(__DIR__ . '/imgdata/.assets.json'))['chardata'];
if (!isPresent($data, [$uniSlugName, $charId, 'main.json'])) {
    http_response_code(404);
    exit;
}

function matchUniverses(string $universe): string
{
    global $unidata;
    $Favi_verse = $unidata;
    if (array_key_exists($universe, $Favi_verse["unidata"])) {
        return $Favi_verse["unidata"][$universe]["humanName"];
    } else return $universe;
}

$chardata = $data[$uniSlugName][$charId];
$datachar = $chardata['main.json'];
$uniName = matchUniverses($uniname = $datachar['UniverseId']);
$name = htmlspecialchars12($datachar['name'] ?? $charId);
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');

$desc = "$name\x20is a character of the $uniName Universe on ANTRequest.nl.";
$aiAlways = array_key_exists('withai', $_GET) && "{$_GET['withai']}";
$si = $aiAlways ? "-ai" : '';
$unicanonical = "/gallery/universe$si/$uniSlugName/";

$backColor = null;
$borderColor = null;
$characterData = $datachar;
if (array_key_exists('primaryColor', $characterData) || array_key_exists('secondaryColor', $characterData)) {
    if (!array_key_exists('primaryColor', $characterData)) {
        $characterData['primaryColor'] = '#00a8f3';
    }
    if (!array_key_exists('secondaryColor', $characterData)) {
        $characterData['secondaryColor'] = '#0073a6';
    }
    $primaryColor = $characterData['primaryColor'];
    $secondaryColor = $characterData['secondaryColor'];
    if (preg_match('/^#?([a-f0-9]{6});#?([a-f0-9]{6});$/iD', "$primaryColor;$secondaryColor;", $matches)) {
        $borderColor = "#$matches[1]";
        $backColor = "#$matches[2]";
    }
}

$aiMode = $aiAlways ? "\x20(Ai Mode)" : '';
$nightLightOverride = array_key_exists('night', $_GET) && "{$_GET['night']}";
create_head3($title = "{$datachar['name']}$aiMode (ANT's Character Gallery)", [
        'base' => '/gallery/', 'nightLightOverride' => $nightLightOverride, 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                array('text' => matchUniverses($uniSlugName), 'href' => $unicanonical),
                array('text' => $datachar['name'], 'href' => "$unicanonical$charId"),
        ], 'borderColor' => $borderColor, 'backColor' => $backColor, 'stylelinks' => [
                'statics/cssx.css', 'statics/ddDL-table.css', 'statics/characterPage.css'],
        'canonical' => "$unicanonical$charId", 'class' => ['larger'],
]) ?>
<main>
    <div class=divs>
        <h1><?= "Character &quot;$name&quot;$aiMode";
            $main = $aiAlways ? imageTag($chardata['main-ai'], ['webp', 'png'], 'Main Appearance Ai', ['introImage border'], null)
                    : imageTag($chardata['main-see'], ['avif', 'webp'], 'Main Appearance', ['introImage border']) ?></h1>
        <div><?= str_replace('fetchpriority=auto loading=lazy', 'fetchpriority=high', $main);
            require_once 'dataDescriptionList.php';
            $datachar['charId'] = "$uniSlugName/$charId";
            $datachar['UniverseId'] = new HTMLSafeEscaped("<data value=$uniname>"
                    . matchUniverses($uniname) . "\x20($uniname)</data>");
            $registerDate =
            $LastModified =
            $creationDate = INF;
            if (array_key_exists('creationDate', $datachar)) {
                $creationDate = strtotime("{$datachar['creationDate']}");
                $datachar['creationDate'] = toHTMLDatetime($creationDate);
                if (array_key_exists('LastModified', $datachar)) {
                    $LastModified = strtotime("{$datachar['LastModified']}");
                    $datachar['LastModified'] = toHTMLDatetime($LastModified);
                } else {
                    $LastModified = $creationDate;
                    $datachar['LastModified'] = $datachar['creationDate'];
                }
                if (array_key_exists('registerDate', $datachar)) {
                    $registerDate = strtotime("{$datachar['registerDate']}");
                    $datachar['registerDate'] = toHTMLDatetime($registerDate);
                } else {
                    $registerDate = $creationDate;
                    $datachar['registerDate'] = $datachar['creationDate'];
                }
            }
            function toHTMLDatetime(int $time): HTMLSafeEscaped
            {
                if ($time === 0) return new HTMLSafeEscaped("<span>Unknown</span>");
                $date = date('D Y-M-d', $time);
                $datetime = gmdate('Y-m-d\\TH:i:s\\Z', $time);
                return new HTMLSafeEscaped("<time datetime="
                        . "'$datetime' is=relative-time>$date</time>");
            } ?></div>
    </div>
    <div class=divs>
        <div class=border-set2><?= dataDescriptionList($datachar, array(), [
                    'registerDate' => '/#what-is-registerDate',
                    'creationDate' => '/#what-is-creationDate',
                    'LastModified' => '/#what-is-LastModified',
                    'FavicondId' => '/#what-is-FavicondId',
                    'UniverseId' => '/#what-is-UniverseId',
            ]);
            $item = $chardata;
            if (!is_null($chardata['htdesc'])) {
                $htdesc = readJSON(__DIR__ . "/imgdata/{$chardata['htdesc']}");
                if (is_array($htdesc)) {
                    if (array_key_exists('class', $htdesc[1])) {
                        $htdesc[1]['class'] = "{$htdesc[1]['class']} character-profile";
                    } else $htdesc[1]['class'] = "character-profile";
                    $characterInfo = mkHTMLFromJSON($htdesc);
                } else $characterInfo = '';
            } else $characterInfo = '';
            function mkHTMLFromJSON(mixed $desc): string
            {
                $result = "<$desc[0]";
                foreach ($desc[1] as $name => $value) {
                    $result .= "\x20$name=\"$value\"";
                }
                $result .= ">";
                if (in_array(strtolower($desc[0]), ['br', 'hr', 'source', 'img', 'wbr'])) {
                    return $result;
                }
                foreach ($desc as $pos => $val) {
                    if ($pos == 0 || $pos == 1) continue;
                    $result .= is_string($val) ? $val : mkHTMLFromJSON($val);
                }
                return "$result</$desc[0]>";
            }

            function galleryListing(array $hashes, string $alt, bool $ai): string
            {
                $aiAlways = $ai;
                $classArray = ['store-img', 'store-big'];
                $formats = $aiAlways ? ['webp', 'png'] : ['avif', 'webp'];
                $img = imageTag($hashes, $formats, $alt, $classArray, $aiAlways ? null : 'webp');
                if ($img) return "<div class=store-div>$img<div class=altText>$alt</div></div>";
                return '';
            } ?></div>
    </div>
    <div class=divs><?= "\n$characterInfo\n" ?></div>
    <div class=divs><?= "<h2 id=gallery>Gallery</h2><div class=border>";
        echo galleryListing($item["main-see"], 'Main Appearance', false);
        echo galleryListing($item['main-ai'], 'Main Ai Appearance', true);
        foreach ($item['asset2'] as $asset) echo galleryListing($asset, 'An Appearance', false);
        foreach ($item['assetAi'] as $asset) echo galleryListing($asset, 'An Ai Appearance', true);
        echo '</div>' ?></div>
</main>
