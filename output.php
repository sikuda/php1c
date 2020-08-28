<?php 

require_once( 'src\php1C_common.php');

$STR=php1C\Structure1C(array("Дата, Клиент"));$STR->Insert("Поставщик","ООО");$REZULTAT=$STR->
Get("POSTAVSHHIK");

echo $REZULTAT;


 ?>