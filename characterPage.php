<?php use function Helpers\htmlspecialchars12;
use function ANTHeader\create_head3;

$uniSlugName = null;
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['uni']}")) {
        $uniSlugName = "{$_GET['uni']}";
    }
} else $uniSlugName = 'main';
$charId = null;
if (array_key_exists('char', $_GET)) {
    if (preg_match('/^([a-zA-Z0-9\\-]+)$/iD', "{$_GET['char']}")) {
        $charId = "{$_GET['char']}";
    }
}
require_once __DIR__ . '/require.php';
require_once __DIR__ . '/imageTag.php';
require_once __DIR__ . '/matchUniverses.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$data = readJSON(__DIR__ . '/imgdata/.assets.json')['chardata'];
if (!isPresent($data, [$uniSlugName, $charId, 'main.json'])) {
    http_response_code(404);
    exit;
}
$chardata = $data[$uniSlugName][$charId];
$datachar = $chardata['main.json'];
$uniName = matchUniverses($uniname = $datachar['UniverseId']);
$name = htmlspecialchars12($datachar['name'] ?? $charId);
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');

$desc = $GLOBALS['defaultDesc'] = "$name\x20is a character of the $uniName Universe on ANTRequest.nl.";
$unicanonical = "/gallery/universe/$uniSlugName/";

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
if (!array__get_key_as_boolean('noOpener', $datachar)) {
    ob_start();
    if (!include_once "$cbase/main.php")
        echo "<p>" . htmlspecialchars12($desc);
    $characterInfo = ob_get_clean();
} else $characterInfo = "<p>" . htmlspecialchars12($desc);
if (isset($GLOBALS['desc'])) $desc = "{$GLOBALS['desc']}";
function array__get_key_as_boolean(string $key, array $array): bool
{
    if (array_key_exists($key, $array)) {
        return (bool)$array[$key];
    } else return false;
}

$nightLightOverride = array_key_exists('night', $_GET) && "{$_GET['night']}";
create_head3($title = "{$datachar['name']} (ANT's Character Gallery)", [
        'base' => '/gallery/', 'desc' => $desc, 'class' => ['larger'], 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                array('text' => $uniSlugName, 'href' => $unicanonical),
                array('text' => $datachar['name'], 'href' => "$unicanonical$charId"),
        ], 'borderColor' => $borderColor, 'backColor' => $backColor, 'stylelinks' => [
                'statics/cssx.css', 'statics/ddDL-table.css', 'statics/characterPage.css'],
        'canonical' => "$unicanonical$charId", 'nightLightOverride' => $nightLightOverride,
]) ?>
<main>
    <script type=application/json is=output-script><?= json_encode($chardata) ?></script>
    <div class=divs>
        <h1><?= "Character &quot;$name&quot;" ?></h1>
        <div><?= str_replace('fetchpriority=auto loading=lazy', 'fetchpriority=high', $main = imageTag(
                    $chardata['main-see'], ['avif', 'webp', 'png'], ['introImage border']));
            require_once 'dataDescriptionList.php';
            $datachar['charId'] = "$uniSlugName/$charId";
            $datachar['UniverseId'] = new HTMLSafeEscaped("<data value=$uniname>{$datachar['UniverseId']}</data>");
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
            function galleryListing(string $variant, string $alt, bool $ai): string
            {
                global $uniSlugName, $nightLightOverride, $charId;
                $classArray = ['store-img', 'store-big'];
                $imageTag = imageTag($charId, $variant, $alt, $ai,
                        $nightLightOverride, $uniSlugName, $classArray);
                if ($imageTag === false) return "<!--$charId, $variant-->";
                $alt = htmlspecialchars12($alt);
                return "<div class=store-div>$imageTag<div class=altText>$alt</div></div>";
            } ?></div>
    </div>
    <div class=divs><?= "<div class=character-profile>\n$characterInfo\n</div>" ?></div>
    <div class=divs><?= "<h2 id=gallery>Gallery</h2><p>closed for the time being<div class=border hidden>";

        echo '</div>' ?></div>
</main>
