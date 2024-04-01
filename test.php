<?php
require_once('src/php1C__code.php');

$code = 'Процедура Сложение( d, Я) Результат = "4" + d + Я; КонецПроцедуры Результат=Сложение(1, 2);';
$Language1C = 'en';
$code = 'Procedure fAdd(Y,z) Return "4"+Y+z; EndProcedure Result = fAdd(5,7);';
$str = php1C\makeCode($code, "Result");
echo $str;
