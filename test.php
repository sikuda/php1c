<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');
require_once('src/php1C_common.php');

$code = 'Результат= Null;';
$str = makeCode($code, "Результат");
//$undefined1C = new \php1C\undefined1C();
//$str = $undefined1C == $undefined1C;
echo toString1C($str);

