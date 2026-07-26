<?php

use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";

class Counter
{
    private int $index = 0;

    public function __construct()
    {
    }

    //public function countUp(): int {return ++$this->index;}

    public function currentUp(): int
    {
        return $this->index++;
    }

    public function current(): int
    {
        return $this->index;
    }

    //public function countUpFormatted(): string {return str_pad("{$this->countUp()}",3,'0',STR_PAD_LEFT);}

    public function currentUpFormatted(): string
    {
        return str_pad("{$this->currentUp()}", 3, '0', STR_PAD_LEFT);
    }
}

$matched = false;
$titleURL = null;
$episodeId = null;
$images = array();
$title = "ANT's Comics";
$item = "/{$_GET['titleURL']}/{$_GET['episodeId']}/edata.json";
if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\\d+)\\/edata\\.json$/D', $item, $matches)) {
    if (is_string($content = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/comic-images/$item"))) {
        if (is_array($json_content = json_decode($content, true))) {
            $title = "{$json_content['name']}";
            $index = new Counter;
            $matched = true;
            $titleURL = $matches[1];
            $episodeId = $matches[2];
            while (file_exists($file = "{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/"
                    . "comic-images/$matches[1]/$matches[2]/img{$index->currentUpFormatted()}.webp")) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\\d+)\\/img(\\d+)\\.webp$/D', $file, $matchedFile)) {
                    $filepath = "$matchedFile[1]/$matchedFile[2]/$matchedFile[3].webp";
                    $array = array('webp' => [$filepath, getimagesize($file)]);
                    if (file_exists($realpath = preg_replace('/\\.webp$/D', '.avif', $file))) {
                        $filepath = "$matchedFile[1]/$matchedFile[2]/$matchedFile[3].avif";
                        $array['avif'] = [$filepath, getimagesize($realpath)];
                    }
                    $images[] = $array;
                }
            }
        }
    }
}
if (!$matched) {
    http_response_code(404);
    exit('<!DOCTYPE html><h1>Not Found 404</h1>');
}
$primaryColor = '8e46db';
$secondaryColor = '6a35a6';
$navigator = new ANTNavOption($_SERVER['REQUEST_URI'],
        "/dollmaker2/icon/endpoint.php?bgcolor=%23$primaryColor&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1",
        htmlspecialchars12($title), new Color("#$secondaryColor"),
        new Color("#$primaryColor"), true);
create_head2($title, ['base' => '/comics/',
], [new ANTNavLinkTag('stylesheet', '/gallery/comics/index.css'),
        new ANTNavIStyle(array_key_exists('smaller', $_GET) ? match ($_GET['smaller']) {
            '2' => 'picture>img{width:600px}',
            '3' => 'picture>img{width:200px}',
            default => 'picture>img{width:450px}'
        } : ''), new ANTNavLinkTag('canonical', "https://localhost/comics/$titleURL/$episodeId/")
], [ANTNavFavicond('/', 'Home'),
        ANTNavBuzz('/comics/', $title),
        $navigator,
]);
$title = htmlspecialchars12($title) ?>
<div class=divs style=text-align:center><?= "<h1> $title</h1\n>";
    $baseURL = '/gallery/comic-images/';
    $index = new Counter;
    foreach ($images as $image) {
        echo "<picture>";
        if (array_key_exists('avif', $image)) {
            echo "<source srcset=\"$baseURL{$image['avif'][0]}\" {$image['avif'][1][3]} type=image/avif>";
        }
        echo "<img src=\"$baseURL{$image['webp'][0]}\" {$image['avif'][1][3]} alt=\"Comic Image\"></picture\n>";
    } ?></div>
