<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');
require_once('src/php1C_common.php');

$code = '//Сортировать
ТаблЗнач = Новый ТаблицаЗначений;
ТаблЗнач.Колонки.Добавить("Товар");
ТаблЗнач.Колонки.Добавить("Стоимость");
стр1 = ТаблЗнач.Добавить();
стр1.Товар = "Товар3";
стр1.Стоимость = 105;
стр2 = ТаблЗнач.Добавить();
стр2.Товар = "Товар2";
стр2.Стоимость = 106;
стр3 = ТаблЗнач.Добавить();
стр3.Товар = "Товар1";
стр3.Стоимость = 107;
ТаблЗнач.Сортировать("Товар");
Результат = ТаблЗнач[0].Товар;';
$str = makeCode($code, "Результат");
//$undefined1C = new \php1C\undefined1C();
//$str = $undefined1C == $undefined1C;
echo toString1C($str);




