<?php
	define('Language1C', 'en');
	require_once('src/php1C__code.php');
	$str = chr(10).'var Result;'.chr(10).'Procedure fAdd(Y,z)'.chr(10).'Return "4"+Y+z;'.chr(10).'EndProcedure'.chr(10).'Result = fAdd(5,7);';
	//$str = chr(10).' result = 1  +   1;';
	//$result = php1C\makeCode($str, "Результат");
	$result = php1C\makeCode($str);
	echo $result;
	//echo $str;
?>