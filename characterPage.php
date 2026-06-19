<?php // ANTHeader
use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavOption;
use ANTHeader\ANTNavLinkTag;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\ANTNavFavicond;
use function readCharacterJSON\matchColor;
use function readCharacterJSON\readCharacterJSON;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
$homeIconBase64 = htmlspecialchars(base64_encode(file_get_contents('home.svg')), ENT_HTML5 | ENT_QUOTES);
if (!preg_match('/^[a-zA-Z0-9\\-]+$/D', $char = $_GET['char'])) on404();

$canonicalPath = '/gallery/';
$baseDirectory = 'images';
$imageDirector = 'images';
if (array_key_exists('uni', $_GET)) {
    if (preg_match('/^[a-zA-Z0-9\\-]+$/D', "{$_GET['uni']}")) {
        if (file_exists(__DIR__ . '/htignore/universe-images/' . ($uni = $_GET['uni']))) {
            $canonicalPath = "/gallery/universe/$uni/";
            $baseDirectory = "universe-images/$uni";
            $imageDirector = "$uni";
        }
    }
}

$path = "htignore/$baseDirectory/$char/main.json";
if (!file_exists($path)) on404();
require_once "readCharacterJSON.php";
$characterData = json_decode(file_get_contents($path) ?? '{}', true);
$title = "{$characterData['name']} (ANT's Character Gallery)";
$navigator = ANTNavFavicond("{$canonicalPath}char/$char", $title, true);
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
        $navigator = new ANTNavOption("{$canonicalPath}char/$char",
                "/dollmaker2/icon/endpoint.php?bgcolor=%23$matches[1]&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
                htmlspecialchars12($title), new Color("#$matches[2]"),
                new Color("#$matches[1]"), true);
    }
}

["bg_Regular" => $bg_Regular, "bg_BG" => $bg_BG, "bg_EYES" => $bg_EYES,
        "bg_Pants" => $bg_Pants, "bg_Dark" => $bg_Dark] = new_style_shades('bg', $navigator->borderColor);
$bgURL = "/dollmaker2/endpoint.svg.php?bgcolor=$bg_Regular&eye=$bg_EYES&pants=$bg_Pants&shoes=$bg_Dark";
$bgURL = str_replace('#', '%23', $bgURL);
function new_style_shades(string $name, Color $color): array
{
    $glitched = array();
    $glitched["{$name}_Regular"] = ($color)->toString();
    $glitched["{$name}_BG"] = ($color)->set_darkness(65.10)->toString();
    $glitched["{$name}_EYES"] = ($color)->set_darkness(49.80)->toString();
    $glitched["{$name}_Pants"] = ($color)->set_darkness(39.61)->toString();
    $glitched["{$name}_Dark"] = ($color)->set_darkness(25.10)->toString();
    return $glitched;
}

$array = readCharacterJSON($path, true);
if (empty($array)) {
    on404();
} else {
    $json = $array['json'];
    $array = $array['data'];
}
function on404(): never
{
    http_response_code(404);
    $navigator = ANTNavFavicond("/404", '404 Found Not', true);
    create_head2('404 Found Not', ['base' => '/gallery/'], [
            new ANTNavLinkTag('stylesheet', ["cssx.css", "characterPage.css", 'ddDL-table.css']),
    ], [ANTNavFavicond('/', 'Home'), $navigator]) ?>
    <main class=divs>
        <h1>Character Not Found</h1>
        <p>That character is not on here.
    </main><?= '<!-- hello -->';
    exit();
}

$uniName = $array['UniverseId'] = matchUniverses($array['UniverseId']);
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
create_head2($title, [
        'base' => '/gallery/', 'desc' => $desc,
        'ventHref' => match ($char) {
            'veloxcity' => 'https://www.roblox.com/games/1537690962/Bee-Swarm-Simulator',
            '19-G' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status',
            'moon' => '/dollmaker3/v1u._3AZGf_tzan_0eD9__j9jP_9wIX_JSgs_wDx_QQC0AcB0QcB',
            'sun' => '/dollmaker3/v1u._1WU_f9VvP3_ZZW9_1W8_f80JNH_JSgs_wAAgAQC0AcB0QcB',
            default => array_key_exists('ventHref',$characterData)?$characterData['ventHref']:null,
        },
], [
        new ANTNavLinkTag('stylesheet', [
                "cssx.css", "characterPage.css", 'ddDL-table.css',
        ]), new ANTNavIStyle(".divs>.character-profile{&>:first-child{margin-top:0}&>:last-child{margin-bottom:0}}"),
        new ANTNavIStyle('h1{margin-bottom:0.5em}.store-img{border:none;border-bottom:3px solid gray}'),
        new ANTNavIStyle(".store-img,.store-div{width:20em;}.overflox>div,.charname{width:calc(20em - 2ch);" .
                "overflow-x:hidden;white-space:nowrap;text-overflow:ellipsis;}"),
        new ANTNavLinkTag('canonical', "https://antrequest.nl$canonicalPath$char"),
    //new ANTNavArbitraryHTML('open-graph',
    //"<meta property=og:description content=\"$htmlDescription\">" .
    //"<meta property=og:title content=\"" . htmlspecialchars12($title) . '">' .
    //"<meta property=og:url content=https://antrequest.nl/gallery/char/$char>" .
    //"<meta property=og:image content=https://antrequest.nl/gallery/images/$char.main.png>" .
    //"<meta property=og:image:width content=800><meta property=og:image:height content=1280>" .
    //"<meta property=og:image:type content=image/png>"),
], [ANTNavFavicond('/', 'Home'),
        ANTNavReddcond("$canonicalPath", 'Universe Home'),
        $navigator
]);
require_once "dataDescriptionList.php";
require_once "loginService.php";
require_once "imageTag.php";
$imgsrc = "$baseDirectory/$char.png";
global $JWT;
if (is_array($token = $JWT->validate("{$_COOKIE['htpasswd']}"))) {
    $currentUsername = htmlspecialchars12("{$token['username']}");
    echo <<<ACCOUNT
    <div style="height:3em;background-color:white;border-bottom: 4px solid #e689bf;">
    <div style="width:88%;max-width:88%;margin:auto">ANT//$currentUsername</div></div>
    ACCOUNT. "\n\n";
}

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
<script type=application/json is=output-script>"<-?= json_encode() ?>"</script>
<script type=module src=JSONScript.js></script>
<main>
    <div class=divs>
        <h1 style=text-align:center><?= "Character &quot;$name&quot;" ?></h1>
        <div style=text-align:center;margin-bottom:1em><?= imageTag($char, 'main',
                    "$name's Main appearance", null, $aichar = !!
                    $json['aichar'], ['introImage border'], $baseDirectory);
            foreach (['creationDate-epoch', 'LastModified-epoch', 'registerDate-epoch'] as $rm) {
                unset($array[$rm]);
            }
            /*$array['Section'] = new HTMLSafeEscaped("<a href=#sec-$char>$name</a>")*/ ?></div>
    </div>
    <div class=divs>
        <div style="border-left:2px solid gray;border-right:2px solid gray;border-bottom:2px solid gray"
             data-data><?= dataDescriptionList($array, array(), [
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
                $classArray = ['store-img', 'listing'];
                if ($mustsourced) $classArray[] = 'mustsourced';
                $imageTag = imageTag($charId, $variant, $alt, $prefixed,
                        $ai, $classArray, $baseDirectory);
                if ($imageTag === false) return '';
                $alt = htmlspecialchars12($alt);
                return "<div class=store-div>$imageTag<div class=altText>$alt</div></div>";
            } ?></div>
    </div>
    <div class=divs>
        <h2 id=gallery>Gallery</h2>
        <div style=margin-left:0;padding-bottom:1em
             class=border><?= (function () use ($char, $name, $aichar, $altTexts) {
                $altText1 = array_key_exists("main", $altTexts) ?
                        $altTexts["main"] : "$name's Main appearance";
                $altText2 = array_key_exists("ai.main", $altTexts) ?
                        $altTexts["ai.main"] : "Them as anime";
                return (!$aichar ? galleryListing($char, 'main', $altText1, false) : '')
                        . galleryListing($char, 'main', $altText2, true);
            })();
            $array = array();
            foreach (glob("htignore/$baseDirectory/$char/*") as $item) {
                if (preg_match('/\\/(ai\\.)?gallery\\.([a-zA-Z0-9\\-]+)\\.(?:png|jpe?g|webp|avif)$/D', $item, $matches)) {
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
