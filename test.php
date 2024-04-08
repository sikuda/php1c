<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

//$code = 'Процедура Сложение( d, Я) Результат = "4" + d + Я; КонецПроцедуры Результат=Сложение(1, 2);';
//echo bcdiv("1.12345678901234567","3",27);
$code = 'Результат = 3001.1 >= 37.1;';
//$Language1C = 'en';
//$code = 'Procedure fAdd(Y,z) Return "4"+Y+z; EndProcedure Result = fAdd(5,7);';
$str = makeCode($code, "Результат");
echo toString1C($str);
