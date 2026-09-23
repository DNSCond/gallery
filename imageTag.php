<?php use function Helpers\htmlspecialchars12;

function imageTag(string $charId, string $variant, string $alt, bool|int $ai, bool $night,
                  string $universeKey, array $classes = array(), bool $noLightbox = false): string|false
{
    $prepath = '/htignore/universe-images';
    $prefixed = $variant !== 'main' ? "gallery/" : "";
    $basePath = __DIR__ . "$prepath/$universeKey/$charId/$prefixed$variant";
    $baseURL = "imgdata/$universeKey/drawn/$charId/$variant";
    $noLightbox = true;// no Lightbox support
    $suffix = '';
    $result = ($noLightbox ? '' : "<a target='_blank' href=$baseURL.lightbox~lightbox>") . "<picture>";
    $alt = htmlspecialchars12($alt);
    $classes = implode(' ', $classes);
    /*if ($night && file_exists("$basePath-night.avif")) {
        $filegc = file_get_contents("$basePath-night.avif");
        $hash = base64UrlEncode_temporary(sha256Bin($filegc));
    $result .= "<source srcset=\"$baseURL-night.avif~$hash$suffix\" type=image/avif>";}*/
    if ($ai) {
        $path = $variant === 'main' ? "ai." : "gallery/ai/";
        $basePathAi = __DIR__ . "$prepath/$universeKey/$charId/$path$variant";
        $baseURLAi = "imgdata/$universeKey/ai/$charId/$variant";
        $result = ($noLightbox ? '' : "<a target='_blank' href=$baseURLAi.lightbox~lightbox>") . "<picture>";
        // $basePathAi cannot be night
        if (file_exists("$basePathAi.webp")) {
            $ext = 'webp';
            $filegc = file_get_contents("$basePathAi.webp");
            $hash = base64UrlEncode_temporary(sha256Bin($filegc));
        } elseif (file_exists("$basePathAi.png")) {
            $filegc = file_get_contents("$basePathAi.png");
            $hash = base64UrlEncode_temporary(sha256Bin($filegc));
            $ext = 'png';
        } else return false;
        $size = getimagesize("$basePathAi.$ext")[3];
        $result .= "<img src=\"$baseURLAi.$ext~$hash\" $size alt=\""
            . "$alt\" class=\"$classes\" fetchpriority=auto loading=lazy>";
    } else {
        if (file_exists("$basePath.avif")) {
            $filegc = file_get_contents("$basePath.avif");
            $hash = base64UrlEncode_temporary(sha256Bin($filegc));
            $result .= "<source srcset=\"$baseURL.avif~$hash$suffix\" type=image/avif>";
        }
        if (file_exists("$basePath.webp")) {
            $filegc = file_get_contents("$basePath.webp");
            $hash = base64UrlEncode_temporary(sha256Bin($filegc));
            if (file_exists("$basePath.webp")) {
                $result .= "<source srcset=\"$baseURL.webp~$hash$suffix\" type=image/webp>";
            }
            $baseSuffix = $hash;
        } else return false;
        if (file_exists("$basePath.png")) {
            $filegc = file_get_contents("$basePath.png");
            $hash = base64UrlEncode_temporary(sha256Bin($filegc));
            $result .= "<source srcset=\"$baseURL.png~$hash$suffix\" type=image/png>";
        }

        $size = getimagesize("$basePath.webp")[3];
        $result .= "<img src=\"$baseURL.webp~$baseSuffix\" $size alt=\""
            . "$alt\" class=\"$classes\" fetchpriority=auto loading=lazy>";
    }
    return "$result</picture>" . (!$noLightbox ? "</a>" : '');
}

function sha256Bin(string $string): string
{
    return hash('sha256', $string, true);
}

function base64UrlEncode_temporary(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
