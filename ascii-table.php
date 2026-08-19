<?php use function ANTHeader\ANTNavBinary;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavHome;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use ANTHeader\ANTNavIStyle;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";

$bgcolor = new Color('#0073a6');
create_head2('ascii table!', [], [
    new ANTNavLinkTag('stylesheet', []),
    new ANTNavIStyle('table{border-collapse:collapse;background-color:white;margin:auto;width:400px;}td,th{' .
        'border: 1px solid #dddddd;text-align:left;padding:8px;}tr:nth-child(even) {background-color:#dddddd;}'),
], [
    ANTNavHome(),
    ANTNavBinary('/gallery/ascii-table.php', 'Ascii Table', true),
    new ANTNavOption('/gallery/hashtable.php', '/dollmaker2/icon/endpoint.php?' .
        'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
        'Hash Table', '#00a6a6', '#00ffff'),
]);
echo '<div style=overflow-x:scroll><table><tr><th>char<th>code<th>0x hexcode</tr>';
function numtoHTMLStr($i): void
{
    $char = (match ("$i") {
        '10' => '\\n',
        '32' => '\\s',
        default => "&#$i;"
    });
    echo "\n    <tr><td>$char<td>$i<td>0x" . dechex($i);
}

numtoHTMLStr(10);
for ($i = 0x20; $i <= 126; $i++) {
    numtoHTMLStr($i);
}
echo "\n</table></div>\n";
