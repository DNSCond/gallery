<?php use function ANTHeader\create_head3;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
create_head3($title = 'Interger Sheet', [
    'stylelinks' => ['/gallery/statics/ascii-table.css'],
    'canonical' => "/gallery/ascii-table.php",
    'noVent' => true,
]);
echo "<H1>$title</H1><TABLE><THEAD><TR><TH SCOPE=col>Index<th scope=col>Health<th scope=col>Cost<tBODY>";
for ($i = 1; $i <= 74; $i++) {
    echo "\n  <tr><td>$i<td>" . format_int_or_float((($i * $i) * 250) + 250);
    echo "<td>" . format_int_or_float(250_000 * ($i * $i));
}
echo "\n</table>"; // &nbsp;
function format_int_or_float(float|int $number): string
{
    return number_format($number, 0, '.', '_');
}
