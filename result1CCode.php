<?php

/**
* Скрипт для обмена выполняемого кода по 1С (обработка UnitTests.epf)
*
*/
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$lang = $_GET["lang"];
if(isset($lang)) define(Language1C, $lang); 
require_once('src/php1C__code.php');

    $str = 'Silence is gold';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = file_get_contents('php://input');

    	//echo $str;
    	//die();


    	//echo $code;
    	//$str .= '2'.isset($iscode);
    	if(isset($_GET["code"])) $str = php1C\makeCode($code);
    	else{	
	    	if (Language1C === 'en') {
	            $str = php1C\makeCode($code, "Result");    
	        }
	        else{
	            $str = php1C\makeCode($code, "Результат"); 
	        } 
	    }
	}	
	echo $str;