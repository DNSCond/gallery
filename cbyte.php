<?php use function Helpers\cbyte;

$array = array('cbyte' => null);
header('content-type: application/json');
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
if (array_key_exists('bytes', $_GET))
    if (preg_match('/^\\d+$/D', $bytes = "{$_GET['bytes']}"))
        $array['cbyte'] = cbyte(+$bytes);
echo json_encode($array);
