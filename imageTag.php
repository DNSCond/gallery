<?php use function Helpers\htmlspecialchars12;

// imageTag

function imageTag(string  $charId, string $variant, string $alt,
                  ?string $prefixed, bool $ai, array $classes,
                  ?string $token = null): string|false
{
    $ai = $ai ? 'ai/' : '';
    $prefixed = is_string($prefixed) ? "$prefixed." : "";
    $baseURL = "images/$ai$prefixed$charId.$variant";
    $basePath = __DIR__ . "/htignore/images/$charId/" . ($ai ? 'ai.' : '') . "$prefixed$variant";
    $result = "<picture>";
    $suffix = '';
    if ($token) {
        $suffix = "?token=$token";
    }
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
        $result .= "<img src=\"$baseURL.png$suffix\" width=800 height=1280 alt=\"$alt\" class=\"$classes\" loading=lazy>";
        return "$result</picture>";
    }
}
