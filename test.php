<?php
require_once('src/php1C__run.php');
	//require_once('php1C__code.php');
	$str = 'а = 1; b = 1; Для й=0 По 0 Цикл Пока a < 1 Цикл Пока b < 1 Цикл b = b + 1; КонецЦикла; a = a + 1; КонецЦикла Сообщить(1); КонецЦикла;'; 
	//$str = "Сообщить(Истина);"; 
	//$result = php1CTransfer\runC""de(""str);
	$result = test($str);
	//$result = php1CTransfer\makeCode($str);
	//echo toString1C($result);

	function test($str, $name=null){
		return php1CTransfer\runCode($str, $name);	
	}
?>