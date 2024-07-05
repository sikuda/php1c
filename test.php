<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

$code = 'Текст = Новый ЗаписьТекста("win.txt"); 
Текст.ЗаписатьСтроку("123");
Текст.Закрыть();

Текст = Новый ЧтениеТекста("win.txt"); 
Стр = Текст.ПрочитатьСтроку();
Пока Стр <> Неопределено Цикл 
    Сообщить(Стр);
    Стр = Текст.ПрочитатьСтроку();
КонецЦикла;';
$str = makeCode($code, "Результат");
echo toString1C($str);

