<?php use function Helpers\htmlspecialchars12;

function imageTag(string  $charId, string $variant, string $alt,
                  ?string $prefixed, bool|int $ai, array $classes,
                  string  $universe = 'images', bool $lnx = false): string|false
{
    $prefixed = is_string($prefixed) ? "$prefixed." : "";
    $baseURLAi = "$universe/ai/$prefixed$charId.$variant";
    $baseURL = "$universe/$prefixed$charId.$variant";
    $basePathAi = __DIR__ . "/htignore/$universe/$charId/ai.$prefixed$variant";
    $basePath = __DIR__ . "/htignore/$universe/$charId/$prefixed$variant";
    $suffix = '';
    if ($ai) {

        // If none of the files exist, return false
        $files = ["$basePathAi.webp", "$basePathAi.png", "$basePathAi.jpeg", "$basePathAi.avif", "$basePathAi.jpg"];
        $exists = false;
        foreach ($files as $file) {
            if (file_exists($file)) {
                $exists = true;
                break; // stop as soon as we find one
            }
        }
        if ($exists) {
            $basePath = $basePathAi;
            $baseURL = $baseURLAi;
        } elseif ($ai === 2) return false;
    }
    $result = ($lnx ? "<a target='_blank' href=$baseURL.lightbox>" : '') . "<picture>";
    $alt = htmlspecialchars12($alt);
    //$url = "images/$charId.$variant.png$suffix";
    $classes = implode(' ', $classes);
    if (file_exists("$basePath.avif")) $result .= "<source srcset=\"$baseURL.avif$suffix\" type=image/avif>";
    if (file_exists("$basePath.webp")) $result .= "<source srcset=\"$baseURL.webp$suffix\" type=image/webp>";
    if (file_exists("$basePath.png")) $result .= "<source srcset=\"$baseURL.png$suffix\" type=image/png>";
    if (file_exists("$basePath.jpeg")) $result .= "<source srcset=\"$baseURL.jpeg$suffix\" type=image/jpeg>";
    elseif (file_exists("$basePath.jpg")) $result .= "<source srcset=\"$baseURL.jpg$suffix\" type=image/jpeg>";
    if (!str_contains($result, '<source ') && str_contains($classes, 'mustsourced')) {
        return false;
    } else {
        $pathURL = file_exists("$basePath.webp") ? "$basePath.webp" : "$basePath.png";
        $size = getimagesize($pathURL)[3];
        $result .= "<img src=\"$baseURL.png$suffix\" $size " . //width=800 height=1280
            "alt=\"$alt\" class=\"$classes\" fetchpriority=auto loading=lazy>";
        return "$result</picture>" . ($lnx ? "</a>" : '');
    }
}
