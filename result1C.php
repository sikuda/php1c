<?php
/**
* Скрипт для обмена с 1С (обработка ПроверкаВыполнения1СPHP.epf)
*
*/
require_once('src/php1C__run.php');


    $str = 'Пустой запрос';
    //echo file_get_contents('php://input');
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = file_get_contents('php://input');
    	//echo $code;
    	$str = php1CTransfer\runCode($code, "Результат"); 
	}	
	echo $str;
?>