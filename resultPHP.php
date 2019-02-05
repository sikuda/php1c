<?php
require_once('src/php1C__code.php');
    $str = '';
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    	$code = $_POST['code'];
		if($code){
			$str = php1CTransfer\makeCode($code);
			$output = fopen("output.php", 'w') or die("Can't open file output.php");
			fputs($output, "<?php \nrequire_once( 'php1C_common.php');\n\n");
			fputs($output, $str);
			fputs($output, "\n\n ?>");
			fclose($output);		
		}
	}	
	echo $str;
?>