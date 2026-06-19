<?php header('content-type: image/svg+xml');
header('cache-control: max-age=1500, public');
header('cache-control: max-age=0, public');
$stroke = array_key_exists('stroke', $_GET) && !!$_GET['stroke'];
$boolean = array_key_exists('bool', $_GET) && !!$_GET['bool'] ?>
<svg width="512" height="512" xmlns="http://www.w3.org/2000/svg">
    <rect x="0" y="0" fill="<?= $boolean ? "#96f06e" : "#fa6982" ?>" stroke-width="16"
          width="512" height="512" stroke="<?= $stroke ? "block" : "transparent" ?>"/>
    <path d="M 64 64 L 448 448 M 64 448 L 448 64" stroke-width="16" stroke="<?= $boolean ? "transparent" : "black" ?>"/>
    <path d="M 96 288 l 96 96 l 256 -256" fill="transparent" stroke-width="16"
          stroke="<?= $boolean ? "black" : "transparent" ?>"/>
</svg>
