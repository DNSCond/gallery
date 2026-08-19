<?php // ANTHeader
use function ANTHeader\create_head3;
use function readCharacterJSON\matchColor;
use function readCharacterJSON\readCharacterJSON;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$homeIconBase64 = htmlspecialchars(base64_encode(file_get_contents('home.svg')), ENT_HTML5 | ENT_QUOTES);
if (!preg_match('/^[a-zA-Z0-9\\-]+$/D', $char = $_GET['char'])) on404();

$uniPName = 'main';
$baseDirectory = 'images';
$imageDirector = 'images';
$canonicalPath = '/gallery/char/';
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^[a-zA-Z0-9\\-]+$/D', "{$_GET['uni']}")) {
        if (file_exists(__DIR__ . '/htignore/universe-images/' . ($uni = $_GET['uni']))) {
            $canonicalPath = "/gallery/universe/$uni/";
            $baseDirectory = "universe-images/$uni";
            $imageDirector = $uniPName = "$uni";
        }
    }
}

$path = "htignore/$baseDirectory/$char/main.json";
if (!file_exists($path)) on404();
require_once "readCharacterJSON.php";
$characterData = json_decode(file_get_contents($path) ?? '{}', true);
$title = "{$characterData['name']} (ANT's Character Gallery)";
//$navigator = ANTNavFavicond("$canonicalPath$char", $title, true);
$borderColor = null;
$backColor = null;
if (array_key_exists('primaryColor', $characterData) || array_key_exists('secondaryColor', $characterData)) {
    if (!array_key_exists('primaryColor', $characterData)) {
        $characterData['primaryColor'] = '#00a8f3';
    }
    if (!array_key_exists('secondaryColor', $characterData)) {
        $characterData['secondaryColor'] = '#0073a6';
    }
    $primaryColor = matchColor($characterData['primaryColor']);
    $secondaryColor = matchColor($characterData['secondaryColor'], false);
    if (preg_match('/^#?([a-f0-9]{6});#?([a-f0-9]{6});$/iD', "$primaryColor;$secondaryColor;", $matches)) {
//$navigator = new ANTNavOption("$canonicalPath$char",
//"/dollmaker2/icon/endpoint.php?bgcolor=%23$matches[1]&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
//htmlspecialchars12($title), new Color("#$matches[2]"),new Color("#$matches[1]"), true);
        $borderColor = "#$matches[1]";
        $backColor = "#$matches[2]";
    }
}

$array = readCharacterJSON($path, true);
if (empty($array)) {
    on404();
} elseif (array_key_exists('location', $array)) {
    http_response_code(308);
    if (preg_match("/^([a-zA-Z0-9\\-]+)\\/([a-zA-Z0-9\\-]+)$/D",
            "{$array['location']}", $matches)) {
        $linkto = null;
        if ($matches[1] === 'main') $linkto = 'char';
        elseif ($matches[1] === 'images') $linkto = 'char';
        else $linkto = "universe/$matches[1]";
        header("Location: /gallery/$linkto/$matches[2]");
    } else on404();
}
$json = $array['json'];
$array = $array['data'];
function on404(): never
{
    http_response_code(404); ?>
    <main class=divs>
        <h1>Character Not Found</h1>
        <p>That character is not on here.
    </main><?= '<!-- hello -->';
    exit();
}

$uniName = $array['UniverseId'] = matchUniverses($uniname = $array['UniverseId']);
$name = htmlspecialchars12($characterData['name'] ?? $char);

$desc = $GLOBALS['defaultDesc'] = "$name\x20is a character of the $uniName Universe on ANTRequest.nl.";
if (!array__get_key_as_boolean('noOpener', $json)) {
    ob_start();
    if (!include_once "htignore/$baseDirectory/$char/main.php")
        echo "<p>" . htmlspecialchars12($desc);
    $characterInfo = ob_get_clean();
} else $characterInfo = "<p>" . htmlspecialchars12($desc);
if (isset($GLOBALS['desc'])) $desc = "{$GLOBALS['desc']}";

$htmlDescription = htmlspecialchars12($desc);
create_head3($title, [
        'base' => '/gallery/', 'desc' => $desc,
        'ventHref' => match ($char) {
            'veloxcity' => 'https://www.roblox.com/games/1537690962/Bee-Swarm-Simulator',
            '19-G' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status',
            'moon' => '/dollmaker3/v1u._3AZGf_tzan_0eD9__j9jP_9wIX_JSgs_wDx_QQC0AcB0QcB',
            'sun' => '/dollmaker3/v1u._1WU_f9VvP3_ZZW9_1W8_f80JNH_JSgs_wAAgAQC0AcB0QcB',
            default => array_key_exists('ventHref', $characterData) ? $characterData['ventHref'] : null,
        }, 'class' => ['larger'], 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                array('text' => $uniPName, 'href' => $canonicalPath),
                array('text' => "{$characterData['name']}", 'href' => "$canonicalPath$char"),
        ], 'borderColor' => $borderColor, 'backColor' => $backColor, 'stylelinks' => [
                "cssx.css", "characterPage.css", 'ddDL-table.css',
        ], 'canonical' => "https://antrequest.nl$canonicalPath$char"
]);
require_once "dataDescriptionList.php";
require_once "imageTag.php";
$imgsrc = "$baseDirectory/$char.png";

$altTexts = array();
if ($altContent = file_get_contents("htignore/$baseDirectory/$char/altText.txt")) {
    require_once 'customFormat.php';
    try {
        $altTexts = array_merge($altTexts, parseNamedBlocks($altContent));
    } catch (Exception) {
    }
}

function array__get_key_as_boolean(string $key, array $array): bool
{
    if (array_key_exists($key, $array)) {
        return (bool)$array[$key];
    } else return false;
} ?>
<script type=application/json is=output-script><?= json_encode($json) ?></script>
<main>
    <div class=divs>
        <h1><?= "Character &quot;$name&quot;" ?></h1>
        <div><?= imageTag($char,
                    'main', "$name's Main appearance", null, false,
                    ['introImage border'], $baseDirectory, true);
            foreach (['creationDate-epoch', 'LastModified-epoch', 'registerDate-epoch'] as $rm) {
                unset($array[$rm]);
            }
            $array['charId'] = "$uniPName/{$array['charId']}";
            $array['UniverseId'] = new HTMLSafeEscaped(
                    "<data value=$uniname>{$array['UniverseId']}</data>") ?></div>
    </div>
    <div class=divs>
        <div class=border-set2><?= dataDescriptionList($array, array(), [
                    'registerDate' => '/#what-is-registerDate',
                    'creationDate' => '/#what-is-creationDate',
                    'LastModified' => '/#what-is-LastModified',
                    'FavicondId' => '/#what-is-FavicondId',
                    'UniverseId' => '/#what-is-UniverseId',
            ]);
            $styleLink = '/dollmaker2/ddDL-table.css' ?></div>
    </div>
    <div class=divs>
        <div class=character-profile><?= "<!-- Character Insertion -->\n$characterInfo\n<!-- Character Insertion END -->";
            function galleryListing(string $charId, string $variant, string $alt, bool $ai,
                                           $prefixed = null, bool $mustsourced = true): string
            {
                global $baseDirectory;
                $classArray = ['store-img', 'store-big', 'listing'];
                if ($mustsourced) $classArray[] = 'mustsourced';
                $imageTag = imageTag($charId, $variant, $alt, $prefixed,
                        $ai * 2, $classArray, $baseDirectory, true);
                if ($imageTag === false) return '';
                $alt = htmlspecialchars12($alt);
                return "<div class=store-div>$imageTag<div class=altText>$alt</div></div>";
            } ?></div>
    </div>
    <div class=divs>
        <h2 id=gallery>Gallery</h2>
        <div class=border><?= (function () use ($char, $name, $altTexts) {
                $altText1 = array_key_exists("main", $altTexts) ?
                        $altTexts["main"] : "$name's Main appearance";
                $altText2 = array_key_exists("ai.main", $altTexts) ?
                        $altTexts["ai.main"] : "Them as anime";
                return galleryListing($char, 'main', $altText1, false) .
                        galleryListing($char, 'main', $altText2, true);
            })();
            $array = array();
            foreach (glob("htignore/$baseDirectory/$char/*") as $item) {
                if (preg_match('/\\/(ai\\.)?gallery\\.([a-zA-Z0-9\\-]+)\\.(?:png|jpe?g|webp|avif)$/D',
                        $item, $matches)) {
                    $needle = "$matches[1]$matches[2]";
                    if (in_array($needle, $array)) continue; else $array[] = $needle;
                    $altText = array_key_exists("$matches[1]gallery.$matches[2]", $altTexts)
                            ? $altTexts["$matches[1]gallery.$matches[2]"] : "$matches[1]gallery.$matches[2]";
                    echo galleryListing($char, $matches[2], $altText,
                            $matches[1] === 'ai.', 'gallery', false);
                }
            } ?></div>
    </div>
</main>
