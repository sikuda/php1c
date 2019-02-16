<?php
/**
* Скрипт для обмена с 1С (обработка UnitTests.epf)
*
*/
require_once('src/php1C__run.php');
//require_once('src/php1C__code.php');


    $str = 'Пустой запрос';
    //echo file_get_contents('php://input');
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = file_get_contents('php://input');

    	//echo $code;
    	$str = php1C\runCode($code, "Результат"); 
	}	
	echo $str;
?>