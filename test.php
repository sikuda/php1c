<?php
require_once('src/php1C__run.php');
//require_once('src/php1C__code.php');
	//$str = 'Результат = КодСимвола(Символы.НПП);';
	//$str = 'Результат = 1; Процедура вва(Рез) Рез = Рез + 1;  Сообщить(Рез); КонецПроцедуры вва(Результат);';
	$str = 'Масс = Новый Массив(); Масс.Добавить("Печкин"); Масс.Добавить("Гаврюша"); Масс.Вставить(0, "Печкин"); Результат = Масс[0];'; 
	//$result = php1C\makeCode($str, "Результат");
	$result = php1C\runCode($str, "Результат");
	echo $result;

	//$result = php1CTransfer\makeCode($str);
	//echo $result;

?>