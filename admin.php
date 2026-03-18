<?php use ANTHeader\ANTNavLinkTag;
use function ANTHeader\ANTNavBinary;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
require_once "loginService.php";
global $JWT;
$maxAge = 86400 * 3;
$currentUsername = null;
$hidewatermarks = false;
if (array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) {
    $fileContent = file_get_contents('htignore/auth.txt');
    if ($fileContent)
        if (preg_match_all('/^([a-z0-9\\-_]+):(.+)$/mi', $fileContent,
                        $matches, PREG_SET_ORDER) > 0) {
            $htpasswd = null;
            $username = null;
            foreach ($matches as $match) {
                if (is_null($username)) {
                    if ($match[1] === "{$_POST['username']}") {
                        $username = $match[1];
                        $htpasswd = $match[2];
                    }
                }
            }
            if ($htpasswd) {
                if (password_verify("{$_POST['password']}", $htpasswd)) {
                    $cookie = $JWT->generate(array('username' => $username), $maxAge);
                    set_cookie('htpasswd', $cookie, ['max-age' => $maxAge, 'HttpOnly' => true]);
                    $currentUsername = $username;
                }
            }
        }
} elseif (is_array($token = $JWT->validate("{$_COOKIE['htpasswd']}"))) {
    $currentUsername = $token['username'];
    if (array_key_exists('hidewatermarks', $_POST)) {
        $hidewatermarks = (bool)(int)match ($_POST['hidewatermarks']) {
            '1' => '1',
            default => '0',
        };
        set_cookie('hidewatermarks', $hidewatermarks ? '1' : '0',['max-age' => $maxAge, 'HttpOnly' => true]);
    }
}

function createSelectElement(string $name, array $options, null|string|callable|array $select = null): string
{
    $name = htmlspecialchars12($name);
    $result = array("<select name=\"$name\">");
    foreach ($options as $key => $val) {
        $selected = false;
        if (is_string($select)) {
            $selected = $select === "$key";
        } elseif (is_callable($select)) {
            $selected = !!$select("$key", $val);
        } elseif (is_array($select)) {
            $selected = in_array("$key", $select);
        }
        $key = htmlspecialchars12($key);
        $val = htmlspecialchars12($val);
        $selected = $selected ? 'selected' : '';
        $result[] = "<option $selected value=\"$key\">$val</option>";
    }
    return implode('', $result) . '</select>';
}

create_head2($title = 'ANT\'s ANTMIN', ['base' => '/gallery/',
], [new ANTNavLinkTag('stylesheet', ["cssx.css", 'ddDL-table.css']),
], [
        ANTNavFavicond('/', 'Home'),
        ANTNavBinary('admin.php', $title, true),
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <form method=post>
        <div><label>username: <input name=username autocomplete=username type=text></label></div>
        <div><label>password: <input name=password autocomplete=current-password type=password></label></div>
        <!--<div><label>inviteId: <input name=inviteId autocomplete=off type=text></label></div>-->
        <div>
            <button type=submit>LogIn</button>
        </div>
    </form>
</div>
<!--<?= ' XPHPML ';
if (is_null($currentUsername)) exit ?>-->
<div class=divs>
    Hello <span><?= htmlspecialchars12("ANT//$currentUsername") ?></span>!
    <form method=post>
        <label>hide watermarks while logged in: <span><?= createSelectElement('hidewatermarks',
                        ['1' => 'Yes', '0' => 'No'], (string)(int)$hidewatermarks)
                ?></span></label>
        <div>
            <button type=submit>apply preferences</button>
        </div>
    </form>
</div>
