<?php
//require_once('src/php1C__run.php');
require_once('src/php1C__code.php');
	//$str = 'Результат = КодСимвола(Символы.НПП);';
	//$str = 'Результат = 1; Процедура вва(Рез) Рез = Рез + 1;  Сообщить(Рез); КонецПроцедуры вва(Результат);';
	$str = 'Результат = ЗначениеЗаполнено(Новый Массив);'; 

	$result = php1CTransfer\makeCode($str, "Результат");
	//$result = php1CTransfer\runCode($str, "Результат");
	echo $result;

	//$result = php1CTransfer\makeCode($str);
	//echo $result;

?>