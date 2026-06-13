<?php use function ANTHeader\create_head2;
use function ANTHeader\ANTNavHome;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavIStyle;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";

create_head2('Factorial table!', [], [
        new ANTNavLinkTag('stylesheet', []),
        new ANTNavIStyle('table{border-collapse:collapse;background-color:white;margin:auto;width:100%}td,th{' .
                'border: 1px solid #dddddd;text-align:left;padding:8px;}tr:nth-child(even) {background-color:#dddddd;}'),
        new ANTNavIStyle('th[aria-sort=\'ascending\']::after{content:"\\20\\25B2"}'),
        new ANTNavIStyle('th[aria-sort=\'descending\']::after{content:"\\20\\25BC"}'),
], [ANTNavHome(true)]);
echo "<!---->\n"; ?>
    <script type=module>
        import {SortableTable} from './sortable-table.js';

        class ListedSortableTable extends SortableTable {
            _sortTable(th) {
                const index = th.cellIndex;
                Array.from(this.querySelectorAll('tbody>tr'), row => {
                    const td = row.cells[index], li = td.querySelector('&>ul,&>ol');
                    if (li) td.dataset.sortValue = li.querySelectorAll('&>li').length;
                });
                super._sortTable(th);
            }
        }

        customElements.define('sortable-list-table', ListedSortableTable, {extends: 'table'});
    </script><?= "\n";
echo "<div style=overflow-x:scroll>\n<table is=sortable-list-table><thead>";
echo "<tr><th>Number<th>Factors<th data-factori-list=''>Factor List<tbody>";
for ($i = 1; $i <= 100; $i++) {
    $factors = array();
    for ($j = 1; $j < $i + 1; $j++) {
        if ($i % $j == 0) $factors[] = $j;
    }
    $count = count($factors);
    echo "\n<tr><td><data value=$i>$i</data><td><data value=$count>$count</data><td><ul><li>" .
            implode('<li>', $factors) . '</ul>';
}
echo "\n</table></div>";
