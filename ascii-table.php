<?php use function ANTHeader\create_head3;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
create_head3($title = 'Ascii Table', [
    'stylelinks' => ['/gallery/statics/ascii-table.css'],
    'canonical' => "/gallery/ascii-table.php",
    'noVent' => true,
]);
echo "<H1>$title</H1><TABLE><THEAD><TR><TH SCOPE=col>"
    . 'char<th scope=col>hex<th scope=col>code<tBODY>';
function chrToHTMLStr($i): void
{
    $char = (match ("$i") {
        '10' => '\\n',
        '32' => '\\s',
        default => "&#$i;",
    });
    $x = dechex($i);
    echo "\n  <tr><td>$char<td>0x$x<td>$i";
}

chrToHTMLStr(10);
for ($i = 0x20; $i <= 126; $i++) chrToHTMLStr($i);
echo "\n</table>";
