<?php
	require_once('src/php1C__run.php');
	//require_once('src/php1C__code.php');
	//$str = 'Результат = КодСимвола(Символы.НПП);';
	//$str = 'Результат = 1; Процедура вва(Рез) Рез = Рез + 1;  Сообщить(Рез); КонецПроцедуры вва(Результат);';
	//eval('php1C\Message(123);');
	//eval('$Mass=php1C\Array1C();$Mass->Add("Печкин");$Rezultat=$Mass->GET(0);php1C\Message($Rezultat);');

	$str = 'Результат = ACos(-0.25); '; 
	//$result = php1C\makeCode($str, "Результат");
	$result = php1C\runCode($str, "Результат");
	echo $result;



// 	{
//     "folders":
//     [
//         {
//             "follow_symlinks": true,
//             "path": "."
//         }
//     ],
//     "settings": {
//         "xdebug": {
//             "url": "http://code1c.localhost/wp-content/plugins/codemirror1C/run/test.php",
//             "host": "code1c.localhost",
//             "break_on_start": false,
//             "launch_browser": true,
//             "close_on_stop": true,
//             "super_globals": true,    
//         }
//     }
// }
?>