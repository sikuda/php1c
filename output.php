<?php 
require_once( 'php1C_common.php');

$Rezultat = null;
function VVA($d,$YA){
$Rezultat=add1C($d,$YA);
}
VVA(1,2);
$Rezultat=StrSplit("одын два три"," ",true);$Rezultat=$Rezultat->GET(1);
Message($Rezultat);
//заработало, надо тестировать



 ?>