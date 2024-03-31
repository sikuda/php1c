<?php

require_once('src/php1C__code.php');

$code = 'Перем Результат; \nПроцедура Сложение( d, Я) \nРезультат = "4" + d + Я; \nКонецПроцедуры  \nСложение(1, 2);';
$str = php1C\makeCode($code);
echo $str;