<?php use function Helpers\htmlspecialchars12;

function imageTag(string  $charId, string $variant, string $alt,
                  ?string $prefixed, bool|int $ai, array $classes,
                  string  $universe = 'images', bool $lnx = false): string|false
{
    $prefixed = is_string($prefixed) ? "$prefixed." : "";
    $basePathAi = __DIR__ . "/htignore/$universe/$charId/ai.$prefixed$variant";
    $basePath = __DIR__ . "/htignore/$universe/$charId/$prefixed$variant";
    $baseURLAi = "$universe/ai/$prefixed$charId.$variant";
    $baseURL = "$universe/$prefixed$charId.$variant";
    if ($ai) {
        // If none of the files exist, return false
        $files = ["$basePathAi.webp", "$basePathAi.png", /*"$basePathAi.avif", "$basePathAi.jpeg", "$basePathAi.jpg"*/];
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
    $result = ($lnx ? "<a target='_blank' href=$baseURL.lightbox~lightbox>" : '') . "<picture>";
    $alt = htmlspecialchars12($alt);
    $classes = implode(' ', $classes);
    if (array_key_exists('night', $_GET) && "{$_GET['night']}") {
        if (file_exists("$basePath-night.avif")) {
            $filegc = file_get_contents("$basePath-night.avif");
            $suffix = base64UrlEncode_temporary(sha256Bin($filegc));
            $result .= "<source srcset=\"$baseURL-night.avif~$suffix\" type=image/avif>";
        }
    }
    //$baseSuffix = '';
    if (file_exists("$basePath.avif")) {
        $filegc = file_get_contents("$basePath.avif");
        $suffix = base64UrlEncode_temporary(sha256Bin($filegc));
        $result .= "<source srcset=\"$baseURL.avif~$suffix\" type=image/avif>";
    }
    $filegc = file_get_contents("$basePath.webp");
    $suffix = base64UrlEncode_temporary(sha256Bin($filegc));
    if (file_exists("$basePath.webp")) {
    $result .= "<source srcset=\"$baseURL.webp~$suffix\" type=image/webp>";
    }$baseSuffix = $suffix;
    if (file_exists("$basePath.png")) {
        $filegc = file_get_contents("$basePath.png");
        $suffix = base64UrlEncode_temporary(sha256Bin($filegc));
        $result .= "<source srcset=\"$baseURL.png~$suffix\" type=image/png>";
    }

    if (!str_contains($result, '<source ') &&
        str_contains($classes, 'mustsourced')) {
        return false;
    } else {
        $size = getimagesize("$basePath.webp")[3];
        $result .= "<img src=\"$baseURL.webp~$baseSuffix\" $size alt=\""
            . "$alt\" class=\"$classes\" fetchpriority=auto loading=lazy>";
        return "$result</picture>" . ($lnx ? "</a>" : '');
    }
}

function sha256Bin(string $string): string
{
    return hash('sha256', $string, true);
}

function base64UrlEncode_temporary(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
