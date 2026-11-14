<?php use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
function printCharacters($chdata, $key, $title, ?string $h2Border = null): void
{
    if (is_string($h2Border)) {
        $h2Border = "class='$h2Border'";
    } else $h2Border = "class=h2-border";
    if (is_array($chdata[$key])) {
        echo "<h2 $h2Border>$title</h2>";
        foreach ($chdata[$key] as $character) {
            $img = imageTag($character['n'], $character['img'], [
                'avif', 'webp'], "{$character['n']}", array('store-img'));
            $html = htmlspecialchars12($character['n']);
            if ($img) echo "<article class=store-div data-c={$character['b']} is=shadow" .
                "boxed-hover><h3 class=charname><a href=universe/{$character['u']}>$html" .
                "</a></h3><a href=universe/{$character['u']}>$img</a></article>";
        }
    }
}
