<?php
require_once('php1C.php');

    $str = '';
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = $_POST['code'];
		if( $code ){
			$input = fopen("input.txt", 'w') or die("Can't open file input.txt");
			fputs($input, $code);
    		fclose($input);
    		$str = php1CTransfer\transferPHP1C($code); 
		} 
		else $str = $_POST['codePHP'];
		if($str){
			$output = fopen("output.php", 'w') or die("Can't open file output.php");
			fputs($output, "<?php \nrequire_once( 'php1C_common.php');\n\n");
			fputs($output, $str);
			fputs($output, "\n\n ?>");
			fclose($output);		
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