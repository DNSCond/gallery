<?php

use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;
use function Helpers\json_fromArray;

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

    public function current(): int
    {
        return $this->index;
    }

    public function countUpFormatted(): string
    {
        return str_pad("{$this->countUp()}", 3, '0', STR_PAD_LEFT);
    }
}

$comicData = null;
$images = array();
$item = "/{$_GET['titleURL']}/{$_GET['episodeId']}/edata.json";
if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\d+)\\/edata\\.json$/D', $item, $matches)) {
    if (is_string($content = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/comic-images/$item"))) {
        if (is_array($json_content = json_decode($content, true))) {
            $comicData = $json_content;
            $index = new Counter;
            while (file_exists($file = "{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/"
                    . "comic-images/$matches[1]/$matches[2]/img{$index->countUpFormatted()}.png")) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\d+)\\/img(\\d+)\\.png$/D', $file, $matchedFile)) {
                    $array = array('png' => "comics.$matchedFile[1].$matchedFile[2].$matchedFile[3].png");
                    if (file_exists(preg_replace('/\\.png$/D', '.webp', $file))) {
                        $array['webp'] = "comics.$matchedFile[1].$matchedFile[2].$matchedFile[3].webp";
                    }
                    /*if (file_exists(preg_replace('/\\.png$/D', '.jpg', $file))) {
                        $array['jpeg'] = "comics.$matchedFile[1].$matchedFile[2].$matchedFile[3].jpeg";
                    } elseif (file_exists(preg_replace('/\\.png$/D', '.jpeg', $file))) {
                        $array['jpeg'] = "comics.$matchedFile[1].$matchedFile[2].$matchedFile[3].jpeg";
                    }*/
                    if (file_exists(preg_replace('/\\.png$/D', '.avif', $file))) {
                        $array['avif'] = "comics.$matchedFile[1].$matchedFile[2].$matchedFile[3].avif";
                    }
                    $images[] = $array;
                }
            }
        }
    }
}
$primaryColor = '8e46db';
$secondaryColor = '6a35a6';
$title = 'ANT\'s Comics';
$navigator = new ANTNavOption($_SERVER['REQUEST_URI'],
        "/dollmaker2/icon/endpoint.php?bgcolor=%23$primaryColor&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
        htmlspecialchars12($title), new Color("#$secondaryColor"),
        new Color("#$primaryColor"), true);
create_head2($title, ['base' => '/gallery/comics/',
], [new ANTNavLinkTag('stylesheet', 'index.css')], [
        ANTNavFavicond('/', 'Home'),
        ANTNavBuzz('/gallery/comics/', $title),
        $navigator,
]) ?>
<div class=divs style=text-align:center><?= "<h1> $title</h1>\n";
    $baseURL = '/gallery/images/';
    foreach ($images as $image) {
        echo "<picture>";
        if (array_key_exists('avif', $image)) {
            echo "<source srcset=\"$baseURL{$image['avif']}\" type=image/avif>";
        }
        if (array_key_exists('webp', $image)) {
            echo "<source srcset=\"$baseURL{$image['webp']}\" type=image/webp>";
        }
        //if(array_key_exists('jpeg',$image)){echo"<source src='{$image['jpeg']}' type=image/jpeg>";}
        echo "<img src=\"$baseURL{$image['png']}\" width=800 height=1280 alt=\"Comic Image\"></picture\n>";
    } ?></div>
<!--<div class=divs>
    <pre><code>&lt;?= htmlspecialchars12(json_fromArray([
    '$comicData' => $comicData, '$images' => $images])) ?></code></pre>
</div>-->
