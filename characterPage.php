<?php use function Helpers\htmlspecialchars12;
use function ANTHeader\create_head3;

$uniSlugName = null;
global $nightLightOverride;
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

$mjson = ($cbase = __DIR__ . "/htignore/universe-images/$uniSlugName/$charId/") . "main.json";
if ($charId === null || $uniSlugName === null || !file_exists($mjson)) on404();

function on404(): never
{
    http_response_code(404);
    echo '<!DOCTYPE html><META CHARSET=UTF-8>' ?>
    <main class=divs>
        <h1>Character Not Found</h1>
        <p>That character is not on here.
    </main><?= '<!-- hello -->';
    exit;
}

$characters = array();
require_once __DIR__ . '/require.php';
require_once __DIR__ . '/imageTag.php';
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
header('cache-control: public, max-age=600, stale-while-revalidate=86400, stale-if-error=432000');
$datachar = readCharacterJSON($mjson);
require_once __DIR__ . '/matchUniverses.php';
$uniName = matchUniverses($uniname = $datachar['UniverseId']);
$name = htmlspecialchars12($datachar['name'] ?? $charId);
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

$altTexts = array();
if ($altContent = file_get_contents("$cbase/altText.txt")) {
    require_once 'customFormat.php';
    try {
        $altTexts = array_merge($altTexts, parseNamedBlocks($altContent));
    } catch (Exception) {
        $altTexts = array();
    }
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
    <div class=divs>
        <h1><?= "Character &quot;$name&quot;" ?></h1>
        <div><?= imageTag($charId, 'main', "$name's Main appearance", false,
                    $nightLightOverride, $uniSlugName, ['introImage border']);
            require_once 'dataDescriptionList.php';
            $datachar['charId'] = "$uniSlugName/$charId";
            $datachar['UniverseId'] = new HTMLSafeEscaped(
                    "<data value=$uniname>{$datachar['UniverseId']}</data>");
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
    <div class=divs><?= '<h2 id=gallery>Gallery</h2><div class=border>';
        $altText1 = array_key_exists("main", $altTexts) ?
                $altTexts["main"] : "$name's Main appearance";
        $altText2 = array_key_exists("ai.main", $altTexts) ?
                $altTexts["ai.main"] : "Them as anime";
        echo galleryListing('main', $altText1, false) .
                galleryListing('main', $altText2, true);

        $cache = array();
        foreach (glob("{$cbase}gallery/*.*") as $item) {
            if (preg_match('/\\/gallery\\/([a-zA-Z0-9\\-]+)\\.(?:png|webp|avif)$/D',
                    $item, $matches)) {
                if (array_key_exists("no-ai/$matches[1]", $cache) && $cache["no-ai/$matches[1]"]) {
                    continue;
                } else $cache["no-ai/$matches[1]"] = true;
                echo galleryListing($matches[1], "An Appearance", false);
            }
        }
        foreach (glob("{$cbase}gallery/ai/*.*") as $item) {
            if (preg_match('/\\/gallery\\/ai\\/([a-zA-Z0-9\\-]+)\\.(?:png|webp|avif)$/D',
                    $item, $matches)) {
                if (array_key_exists("with-ai/$matches[1]", $cache) && $cache["with-ai/$matches[1]"]) {
                    continue;
                } else $cache["with-ai/$matches[1]"] = true;
                echo galleryListing($matches[1], "An Ai Appearance", true);
            }
        }
        echo '</div>' ?></div>
</main>
