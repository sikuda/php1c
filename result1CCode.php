<?php
/**
* Скрипт для обмена выполняемого кода по 1С (обработка UnitTests.epf)
*
*/
require_once('src/php1C__code.php');

    $str = 'Пустой запрос';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = file_get_contents('php://input');

    	//echo $code;
    	$str = php1CTransfer\makeCode($code, "Результат"); 
	}	
	echo $str;
?>