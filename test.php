<?php
//require_once('src/php1C__run.php');
require_once('src/php1C__code.php');
	//$str = 'Результат = КодСимвола(Символы.НПП);';
	//$str = 'Результат = 1; Процедура вва(Рез) Рез = Рез + 1;  Сообщить(Рез); КонецПроцедуры вва(Результат);';
	//eval('php1C\Message(123);');
	//eval('$Mass=php1C\Array1C();$Mass->Add("Печкин");$Rezultat=$Mass->GET(0);php1C\Message($Rezultat);');

	$str = 'Результат = СтрЧислоСтрок("Первая" + Символы.ПС + "Вторая" +Символы.ПС + "Третья") ; '; 
	$result = php1C\makeCode($str, "Результат");
	//$result = php1C\runCode($str, "Результат");
	echo $result;
	//$result = php1CTransfer\makeCode($str);
	//echo $result;

?>