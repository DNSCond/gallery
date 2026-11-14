<?php use function Helpers\htmlspecialchars12;
use function Helpers\toDataSet;

function imageTag(?string $characterName, array $hashes,
                  array   $formats, string $alt, array $classes,
                  ?string $fallback = 'webp', bool|int $highPriority = false,
                  ?array  $dataset = array()): string|false
{
    $lastValid = null;
    if (!count($formats)) return false;
    $cname = is_string($characterName) ? "$characterName--" : '';
    $fn = htmlspecialchars12("$cname{$hashes['data-name']}");
    if (is_array($dataset)) {
        if (!array_key_exists('file-name', $dataset))
            $dataset['file-name'] = $fn;
    } else $dataset = ['file-name' => $fn];
    $datasetString = toDataSet($dataset);
    $result = "<picture $datasetString>";
    foreach ($formats as $format) if (array_key_exists($format, $hashes)) {
        $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
        $result .= "<source srcset='imgdata/{$hashes[$format]['hash']}' $size type={$hashes[$format]['t']}>";
        $lastValid = $format;
    }
    $alt = htmlspecialchars12($alt);
    $classes = implode(' ', $classes);
    if ($fallback !== null) $format = $fallback;
    elseif ($lastValid === null) return false;
    else $format = $lastValid;
    if (!array_key_exists($format, $hashes)) return false;
    $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
    $fetchpriority = $highPriority ? ($highPriority == 1 ? 'fetchpriority=high' : '') : 'loading=lazy';
    $result .= "<img src=\"imgdata/{$hashes[$format]['hash']}\" $size alt=\"$alt\""
        . " class=\"$classes\" data-type='{$hashes[$format]['t']}' $fetchpriority>";
    return "$result</picture>";
}
