<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

//$code = 'Процедура Сложение( d, Я) Результат = "4" + d + Я; КонецПроцедуры Результат=Сложение(1, 2);';
$code = 'Результат = Лев("1234", 4);';
$str = makeCode($code, "Результат");
echo toString1C($str);



