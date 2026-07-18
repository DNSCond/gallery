<?php header('content-type: image/svg+xml');
$country = null;
if (array_key_exists('country', $_GET)) if (in_array("{$_GET['country']}",
        explode(',', 'us,nl,fr,ng'))) $country = "{$_GET['country']}";
ob_start(fn(string $string): string => preg_replace('/\\s+/', " ", $string)) ?>
<svg width="350" height="218.75" viewBox="0 0 2048 1280" xmlns="http://www.w3.org/2000/svg">
    <rect width='2048' height='1280' fill='#ffffff'/>
    <!--<?= 'START-IF';
    if ($country === 'nl'): ?>-->
    <g>
        <rect width='2048' height='426' fill='#c8102e'/>
        <rect width='2048' height="853" y='853' fill='#003da5'/>
    </g>
    <!--<?= 'ELSE-IF';
    elseif ($country === 'fr' || $country === 'ng'): ?>-->
    <g>
        <rect width='683' x="1365" height='1280' fill='<?= $country === 'ng' ? '#008751' : '#c8102e' ?>'/>
        <rect width='682' height="1280" fill='<?= $country === 'ng' ? '#008751' : '#003da5' ?>'/>
    </g>
    <!--<?= 'ELSE-IF';
    elseif ($country === 'us'): ?>-->
    <g><?= "<!-- stripes -->\n";
        $indent = '            ';
        $y_offset = 0;
        for ($i = 0; $i < 13; $i++) {
            // Alternates height: 98px for even indices, 99px for odd indices
            $stripe_height = ($i % 2 === 0) ? 98 : 99;
            $fill = ($i % 2 === 0) ? '#c8102e' : 'white';

            echo "<rect width='2048' height='$stripe_height' y='$y_offset' fill='$fill'/>\n";

            // Push the next stripe's starting position down by the current stripe's height
            $y_offset += $stripe_height;
        } ?></g>
    <rect width='840' height='689' fill='#003da5'/>
    <defs>
        <polygon id="star" fill="white" points="<?= "0,-30 7.06,-9.7 28.54,-9.7 11.18,"
        . "2.9 17.82,23.22 0,10.5 -17.82,23.22 -11.18,2.9 -28.54,-9.7 -7.06,-9.7" ?>"/>
    </defs>
    <g><?= "<!-- stars -->\n";
        $canton_width = 819;
        $canton_height = 689;

        // Official proportions for star spacing
        $h_spacing = $canton_width / 12;  // Distance between columns
        $v_spacing = $canton_height / 10; // Distance between rows
        $xoffset = 20;
        $yoffset = +0;
        for ($row = 1; $row <= 9; $row++) {
            // Odd rows have 6 stars, even rows have 5 stars
            $stars_in_row = ($row % 2 !== 0) ? 6 : 5;

            // Adjust starting X offset for alternating rows
            $x_start = ($row % 2 !== 0) ? $h_spacing : $h_spacing * 2;

            for ($col = 0; $col < $stars_in_row; $col++) {
                $x = ($x_start + ($col * $h_spacing * 2)) + $xoffset;
                $y = ($row * $v_spacing) + $yoffset;

                // Output the star positioned at the calculated X and Y
                echo "<use href='#star' x='$x' y='$y' />\n";
            }
        } ?></g>
    <!--<?= 'ELSE';
    else: ?>-->
    <rect width='2048' height='1280' fill='#9ad9e8'/>
    <!--<?= 'endif';
    endif ?>-->
    <g fill="#000000">
        <!-- #ae782f -->
        <rect x="0" y="0" width="20" height="1280" class="wall left"/>
        <rect x="2028" y="0" width="20" height="1280" class="wall right"/>
        <rect x="0" y="0" width="2048" height="20" class="wall up"/>
        <rect x="0" y="1260" width="2048" height="20" class="wall down"/>
    </g>
</svg>
