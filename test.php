<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

$code = 'Н = 5000;
Массив = Новый Массив();

Массив.Добавить(Ложь);
Массив.Добавить(Ложь);

Для индекс = 2 По Н Цикл
    Массив.Добавить(Истина);
КонецЦикла;

времяНачала = ТекущаяУниверсальнаяДатаВМиллисекундах();
Для индекс = 2 По Н Цикл
    Если Массив[индекс] Тогда
        квадрат = индекс * индекс;
        Если квадрат <= Н Тогда
            м = квадрат;
            Пока м <= Н Цикл
                Массив[м] = Ложь;
                м = м + индекс;
            КонецЦикла;
        КонецЕсли;
    КонецЕсли;
КонецЦикла;

времяОкончания = ТекущаяУниверсальнаяДатаВМиллисекундах();
Результат = времяОкончания - времяНачала;';
$str = makeCode($code, "Результат");
echo toString1C($str);



