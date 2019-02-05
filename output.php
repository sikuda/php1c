<?php 
require_once( 'php1C_common.php');

//Проверка выполнение перекрестных циклов и условий
$per1=10;
$per2=1;
while(less1C($per1,20)){
if(less1C($per2,$per1)){
$per1=add1C($per2,1);
}
$per1=add1C($per1,1);
}
Message(add1C($per1,$per2));
//И облом



 ?>