<?php use function Helpers\htmlspecialchars12;

use function JWT\generateToken;

$dir = __DIR__;
require_once "$dir/loginService.php";
require_once "$dir/JWT.php";
global $JWT;
function imageTag(string  $charId, string $variant, string $alt,
                  ?string $prefixed, bool|int $ai, array $classes,
                  ?string $token = null): string|false
{
    global $JWT;
    $prefixed = is_string($prefixed) ? "$prefixed." : "";
    $baseURLAi = "images/ai/$prefixed$charId.$variant";
    $baseURL = "images/$prefixed$charId.$variant";
    $basePathAi = __DIR__ . "/htignore/images/$charId/ai.$prefixed$variant";
    $basePath = __DIR__ . "/htignore/images/$charId/$prefixed$variant";
    $result = "<picture>";
    $suffix = '';
    if ($token) {
        $suffix = "?token=$token";
    } elseif ($_COOKIE['hidewatermarks']) {
        if (is_array($JWT->validate("{$_COOKIE['htpasswd']}"))) {
            $suffix = "?token=" . generateToken([
                    'nonce' => bin2hex(random_bytes(8)),
                    'nowatermark' => true,
                ], 86400);
        }
    }
    if ($ai) {
        $basePath = $basePathAi;
        $baseURL = $baseURLAi;
        if ($ai === 2) {
            // If none of the files exist, return false
            $files = ["$basePath.webp", "$basePath.png", "$basePath.jpeg", "$basePath.avif", "$basePath.jpg"];
            $exists = false;
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $exists = true;
                    break; // stop as soon as we find one
                }
            }
            if (!$exists) return false;
        }
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
