<?php
//require_once('src/php1C__run.php');
	require_once('src/php1C__code.php');
	$str = 'Результат = 0;Если Результат = 0 Тогда Результат = 1; Если Результат = 0 Тогда Результат = 5; Иначе Результат = 6; КонецЕсли; КонецЕсли;'; 
	//$str = "Сообщить(Истина);"; 
	//$result = php1CTransfer\runC""de(""str);
	$result = test($str, "Результат");
	//$result = php1CTransfer\makeCode($str);
	echo $result;

	function test($str, $name=null){
		return php1CTransfer\makeCode($str, $name);	
	}
?>