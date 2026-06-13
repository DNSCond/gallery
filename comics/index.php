<?php use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;

date_default_timezone_set('UTC');
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
create_head2($title = 'ANT\'s Comics', ['base' => '/gallery/comics/',
], [], [
        ANTNavFavicond('/', 'Home'),
        ANTNavBuzz('/gallery/comics/', $title, true)
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <ul><?= (function () {
            $result = '';
            foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/comic-images/*/*/edata.json") as $item) {
                if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\d+)\\/edata\\.json$/D', $item, $matches)) {
                    if (is_string($content = file_get_contents($item))) {
                        if (is_array($json_content = json_decode($content, true))) {
                            $name = htmlspecialchars12($json_content['name'] ?? "$matches[1]/$matches[2]");
                            $result .= "<li><a href=\"$matches[1]/$matches[2]\">$name ($matches[1]/$matches[2])</a>";
                        }
                    }
                }
            }
            return $result;
        })() ?></ul>
</div>
