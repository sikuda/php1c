<?php 
require_once( 'php1C_common.php');

$Rezultat = null;
function SLOZHENIE($d,$YA){
$Rezultat=add1C(add1C("4",$d),$YA);
}
SLOZHENIE(1,2);
$Rezultat=ValueIsFilled(Array1C());

 ?>