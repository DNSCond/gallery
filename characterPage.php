<?php use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function readCharacterJSON\createJWT;
use function readCharacterJSON\readCharacterJSON;
use function Helpers\htmlspecialchars12;
use function JWT\generateToken;

require_once "{$_SERVER['DOCUMENT_ROOT']}/gallery/JWT.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
$homeIconBase64 = htmlspecialchars(base64_encode(file_get_contents('home.svg')), ENT_HTML5 | ENT_QUOTES);
if (!preg_match('/^[a-zA-Z0-9\\-]+$/D', $char = $_GET['char'])) {
    header("{$_SERVER['SERVER_PROTOCOL']} 307");
    header('Location: ..');
    exit;
}
$path = "htignore/images/$char/main.json";
if (!file_exists($path)) {
    header("{$_SERVER['SERVER_PROTOCOL']} 307");
    header('Location: ..');
    exit;
}
$characterData = json_decode(file_get_contents($path) ?? '{}', true);
$title = "{$characterData['name']} (ANT's Gallery)";

$navigator = ANTNavFavicond("char/$char", $title, true);
if (array_key_exists('primaryColor', $characterData) && array_key_exists('secondaryColor', $characterData)) {
    $primaryColor = matchColor($characterData['primaryColor'], true);
    $secondaryColor = matchColor($characterData['secondaryColor'], false);
    if (preg_match('/^#?([a-f0-9]{6});#?([a-f0-9]{6});$/iD', "$primaryColor;$secondaryColor;", $matches)) {
        $navigator = new ANTNavOption("char/$char",
                "/dollmaker2/icon/endpoint.php?bgcolor=%23$matches[1]&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
                htmlspecialchars12($title), new Color("#$matches[2]"),
                new Color("#$matches[1]"), true);
    }
}
function matchColor(string $color, bool $border): string
{
    return match ($color) {
        'Reddcond' => ($border ? '#ff4500' : '#a62c00'),
        'Binary' => ($border ? '#00ff00' : '#00a600'),
        'Magnata' => ($border ? '#ff00ff' : '#a600a6'),
        'Cian' => (!$border ? '#00a6a6' : '#00ffff'),
        'Buzz' => ($border ? '#fff100' : '#a68300'),
        default => $color,
    };
}

$token = generateToken(['nowatermark' => true], 7000);
["bg_Regular" => $bg_Regular, "bg_BG" => $bg_BG, "bg_EYES" => $bg_EYES,
        "bg_Pants" => $bg_Pants, "bg_Dark" => $bg_Dark] = new_style_shades('bg', $navigator->borderColor);
$bgURL = "/dollmaker2/endpoint.svg.php?bgcolor=$bg_Regular&eye=$bg_EYES&pants=$bg_Pants&shoes=$bg_Dark&token=$token";
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

create_head2($title, [
        'base' => '/gallery/'], [
        new ANTNavLinkTag('stylesheet', [
                "cssx.css", "characterPage.css", 'ddDL-table.css',
        ]), new ANTNavIStyle("body{background-size: 100vw auto;background-image:url(\"$bgURL\")}"),
        new ANTNavIStyle('h1{margin-bottom:0.5em}.store-img{border:none;border-bottom:3px solid gray}'),
        new ANTNavIStyle(".store-img,.store-div{width:20em;}.overflox>div,.charname{width:calc(20em - 2ch);" .
                "overflow-x:hidden;white-space:nowrap;text-overflow:ellipsis;}"),
], [ANTNavFavicond('/', 'Home'), $navigator]);
require_once "dataDescriptionList.php";
require_once "readCharacterJSON.php";
require_once "imageTag.php";
$name = htmlspecialchars12($characterData['name'] ?? $char);
$imgsrc = "images/$char.png";
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
    <h1 style=text-align:center><?= "Character &quot;$name&quot;" ?></h1>
    <div style=text-align:center;margin-bottom:1em><?= imageTag($char, 'main',
                "$name's introductory appearance", null,
                false, ['introImage border']) ?></div>
    <!--<?= "hello";
    $array = readCharacterJSON($path, true);
    if (empty($array)) {
        http_response_code(307);
        header("Location: /");
        exit;
    } else {
        $array = $array['data'];
    }
    $array['UniverseId'] = matchUniverses($array['UniverseId']);
    foreach (['creationDate-epoch', 'LastModified-epoch', 'registerDate-epoch'] as $rm) {
        unset($array[$rm]);
    }
    $array['Section'] = new HTMLSafeEscaped("<a href=#sec-$char>$name</a>") ?>-->
    <div style="border-left: 2px solid gray;border-right: 2px solid gray;border-bottom: 2px solid gray;"><?= dataDescriptionList($array, array(), [
                'registerDate' => '/#what-is-registerDate',
                'creationDate' => '/#what-is-creationDate',
                'LastModified' => '/#what-is-LastModified',
                'FavicondId' => '/#what-is-FavicondId',
                'UniverseId' => '/#what-is-UniverseId',
        ]);
        $styleLink = '/dollmaker2/ddDL-table.css' ?></div>
    <div class=character-profile><?= "<!-- Character Insertion -->\n";
        if (!include_once "htignore/images/$char/main.php")
            echo "<p>no information found";
        echo "\n<!-- Character Insertion END -->";
        function galleryListing(string $charId, string $variant, string $alt, bool $ai, $prefixed = null, bool $mustsourced = true): string
        {
            $token = createJWT();
            $classArray = ['store-img', 'listing'];
            if ($mustsourced) $classArray[] = 'mustsourced';
            $imageTag = imageTag($charId, $variant, $alt, $prefixed, $ai, $classArray, $token);
            if ($imageTag === false) return '';
            $alt = htmlspecialchars12($alt);
            return "<div class=store-div>$imageTag<div class=altText>$alt</div></div>\n";
        } ?></div>
    <h2 id=gallery>Gallery</h2>
    <div style=margin-left:0;padding-bottom:1em class=border><?= (function () use ($char, $name) {
            return galleryListing($char, 'main', "$name's introductory appearance", false) .
                    galleryListing($char, 'main', 'Them as Anime', true);
        })();
        $array = array();
        foreach (glob("htignore/images/$char/*") as $item) {
            if (preg_match('/\\/(ai\\.)?gallery\\.([a-zA-Z0-9\\-]+)\\.(?:png|jpe?g|webp|avif)$/D', $item, $matches)) {
                $needle = "$matches[1]$matches[2]";
                if (in_array($needle, $array)) {
                    continue;
                }
                $array[] = $needle;
                echo galleryListing($char, $matches[2], 'Alt Text',
                        $matches[1] === 'ai.', 'gallery', false);
            }
        }
        //echo phpArrayToHTML($array);
        //function phpArrayToHTML(array $array): string
        //{return "<ul><li>" . implode('<li>', $array) . "</ul>";}
        ?></div>
</main>
