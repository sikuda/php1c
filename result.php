<?php
require_once('src/php1C__run.php');

    $str = '';
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = $_POST['code'];
		if( $code ){
			$input = fopen("input.txt", 'w') or die("Can't open file input.txt");
			fputs($input, $code);
    		fclose($input);
    		$str = php1CTransfer\runCode($code); 
		} 
	}	
	else{
		//load text from file input.txt
		if( $_GET['load'] ){
		 	$input = fopen("input.txt", 'r') or die("Can't open file input.txt");
		 	$str = fread($input,filesize("input.txt"));
     		fclose($input);	
		}	
	} 
	echo $str;
?>