<?php
require_once('src/php1C__run.php');
//require_once('src/php1C__code.php');
	$str = 'Результат = Сред("Угадай где кот", 11, 4);';
	//$str = 'Результат = 1; Процедура вва(Рез) Рез = Рез + 1;  Сообщить(Рез); КонецПроцедуры вва(Результат);';

	//php1CTransfer\runCode($str, "Результат");
	$result = php1CTransfer\runCode($str, "Результат");
	echo $result;

	//$result = php1CTransfer\makeCode($str);
	//echo $result;

?>