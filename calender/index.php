<?php

use ANTHeader\ANTNavIStyle;
use ANTHeader\ANTNavOption;
use ANTHeader\ANTNavLinkTag;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavBinary;
use function ANTHeader\ANTNavFavicond;
use function Helpers\htmlspecialchars12;

$primaryColor = '9b1c3c';
$secondaryColor = '7f1731';

date_default_timezone_set('UTC');
$imgHref = "/dollmaker2/icon/endpoint.php?bgcolor=%23$primaryColor&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1";
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
create_head2($title = 'ANT\'s Calendar', [], [
        new ANTNavLinkTag('stylesheet', '../ddDL-table.css'),
        new ANTNavIStyle("table{width:100%}"),
], [
        ANTNavFavicond('https://ANTRequest.nl', 'ANT\'s Character Gallery'),
        ANTNavBinary('/gallery/ascii-table.php', 'Ascii Table'),
        ANTNavBuzz('/dollmaker3/', 'dollmakerV5 ANT'),
        new ANTNavOption(".", $imgHref,
                htmlspecialchars12($title),
                new Color("#$secondaryColor"),
                new Color("#$primaryColor"),
                true),
]);
$json = file_get_contents('events.json');
if ($json) $json = json_decode($json, true); else $json = null ?>
<script type=module src='../JSONScript.js'></script>
<script type=application/json is=output-script><?= json_encode(
            $json, JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type=module>
    class LocalTimeElement extends HTMLTimeElement {
        static get observedAttributes() {
            return Array.of('datetime');
        }

        isEnhanced = true;

        attributeChangedCallback(_name, _oldValue, _newValue, _xmlns) {
            this.textContent = this.date.toString().slice(4, 34);
        }

        get date() {
            return new Date(this.dateTime);
        }
    }

    customElements.define('localtime-elem', LocalTimeElement, {extends: 'time'});
</script>
<main class=divs>
    <div style=overflow-x:scroll>
        <table><?= '<thead><tr><th scope=col>Title<th scope=col>When<th scope=col>Description<tbody>';
            if ($json) {
                $flags = ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE;
                foreach ($json['events'] as $item) {
                    $when = $item['when'];
                    if (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})Z$/D', $when, $matches)) {
                        $matched = match ("$matches[2]") {
                            '01' => 'Jan',
                            '02' => 'Feb',
                            '03' => 'Mar',
                            '04' => 'Apr',
                            '05' => 'May',
                            '06' => 'Jun',
                            '07' => 'Jul',
                            '08' => 'Aug',
                            '09' => 'Sep',
                            '10' => 'Oct',
                            '11' => 'Nov',
                            '12' => 'Dec',
                            default => 'Unknown',
                        };
                        echo "<tr><!--  -->";
                        echo "<td><a href='" . htmlspecialchars("{$item['eventHref']}", $flags)
                                . "'>" . htmlspecialchars("{$item['title']}", $flags) . '</a>';
                        echo "<td><time is=localtime-elem datetime=$matches[1]-$matches[2]-$matches[3]T" .
                                "$matches[4]:$matches[5]:$matches[6]Z>$matches[3] $matched $matches[1] " .
                                "$matches[4]:$matches[5]:$matches[6] UTC+0000</time><td>";
                        echo htmlspecialchars("{$item['desc']}", $flags);
                    }
                }
            } ?></table>
    </div>
</main>
