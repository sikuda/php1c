<?php
require_once('php1C__run.php');
	//require_once('php1C__code.php');
	$str = "Результат = ДобавитьМесяц(ТекущаяДата(), -2);  "; 
	//$str = "Сообщить(Истина);"; 
	//$result = php1CTransfer\runC""de(""str);
	$result = test($str, "Результат");
	//$result = php1CTransfer\makeCode($str);
	echo toString1C($result);

	function test($str, $name=null){
		return php1CTransfer\runCode($str, $name);	
	}
?>