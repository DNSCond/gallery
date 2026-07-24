<?php

use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;
use function Helpers\json_fromArray;
use function JWT\generateToken;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/gallery/JWT.php";

class Counter
{
    private int $index = 0;

    public function __construct()
    {
    }

    public function countUp(): int
    {
        return ++$this->index;
    }

    public function currentUp(): int
    {
        return $this->index++;
    }

    public function current(): int
    {
        return $this->index;
    }

    public function countUpFormatted(): string
    {
        return str_pad("{$this->countUp()}", 3, '0', STR_PAD_LEFT);
    }

    public function currentUpFormatted(): string
    {
        return str_pad("{$this->currentUp()}", 3, '0', STR_PAD_LEFT);
    }
}

$images = array();
$comicData = null;
$name = 'Unknown';
$title = "ANT's Comics";
$item = "/{$_GET['titleURL']}/{$_GET['episodeId']}/edata.json";
if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\\d+)\\/edata\\.json$/D', $item, $matches)) {
    if (is_string($content = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/comic-images/$item"))) {
        $name = $matches[1];
        if (is_array($json_content = json_decode($content, true))) {
            $title = "{$json_content['name']}";
            $comicData = $json_content;
            $index = new Counter;
            while (file_exists($file = "{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/"
                    . "comic-images/$matches[1]/$matches[2]/img{$index->currentUpFormatted()}.webp")) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\\d+)\\/img(\\d+)\\.webp$/D', $file, $matchedFile)) {
                    $array = array('webp' => "$matchedFile[1]/$matchedFile[2]/$matchedFile[3].webp");
                    if (file_exists(preg_replace('/\\.webp$/D', '.avif', $file))) {
                        $array['avif'] = "$matchedFile[1]/$matchedFile[2]/$matchedFile[3].avif";
                    }
                    $images[] = $array;
                }
            }
        }
    }
}

$primaryColor = '8e46db';
$secondaryColor = '6a35a6';
$navigator = new ANTNavOption($_SERVER['REQUEST_URI'],
        "/dollmaker2/icon/endpoint.php?bgcolor=%23$primaryColor&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
        htmlspecialchars12($title), new Color("#$secondaryColor"),
        new Color("#$primaryColor"), true);
create_head2($title, ['base' => '/comics/',
], [new ANTNavLinkTag('stylesheet', '/gallery/comics/index.css'),
    //new ANTNavLinkTag('canonical', "https://localhost/comics/{$_GET['titleURL']}/{$_GET['episodeId']}")
], [
        ANTNavFavicond('/', 'Home'),
        ANTNavBuzz('/comics/', $title),
        $navigator,
]);
global $JWT;
$watermarkBypass = '';
require_once "../loginService.php";
$title = htmlspecialchars12($title) ?>
<div class=divs style=text-align:center><?= "<h1> $title</h1>\n";
    $baseURL = '/gallery/comic-images/';
    $index = new Counter;
    foreach ($images as $image) {
        echo "<picture>";
        if (array_key_exists('avif', $image)) {
            echo "<source srcset=\"$baseURL{$image['avif']}$watermarkBypass\" type=image/avif>";
        }
        echo "<img src=\"$baseURL{$image['webp']}$watermarkBypass\" id=\"img-$name-{$index->countUpFormatted()}"
                . "\" width=800 height=1280 alt=\"Comic Image\"></picture\n>";
    } ?></div>
<script type=application/json is=output-script><?= json_encode([$images]) ?></script>
