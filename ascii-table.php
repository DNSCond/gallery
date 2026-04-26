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
    new ANTNavOption('/gallery/hashtable.php', '/dollmaker1/endpoint.php?bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
        'Hash Table', '#00a6a6', '#00ffff'),
]);
echo '<table><tr><th>char<th>code<th>0x hexcode</tr>';
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
//echo"\n\n<tr><th colspan=3>Greek &amp; Copti\n";
//for ($i = 0x0370; $i <= 0x03FF; $i++)
// {numtoHTMLStr($i);}
//echo"\n\n<tr><th colspan=3>Greek &amp; Copti\n";
//for ($i = 0x0370; $i <= 0x03FF; $i++)
// {numtoHTMLStr($i);}
//// Display Hiragana (U+3040 to U+309F)
//echo "\n\n<tr><th colspan=3>Hiragana\n";
//for ($i = 0x3040; $i <= 0x309F; $i++)
// {numtoHTMLStr($i);}
//// Display Katakana (U+30A0 to U+30FF)
//echo "\n\n<tr><th colspan=3>Katakana\n";
//for ($i = 0x30A0; $i <= 0x30FF; $i++)
// {numtoHTMLStr($i);}
//// Display General Punctuation (U+2000 to U+206F)
//echo "\n\n<tr><th colspan=3>General Punctuation\n";
//for ($i = 0x2000; $i <= 0x206F; $i++)
// {numtoHTMLStr($i);}
echo "\n</table>\n";
