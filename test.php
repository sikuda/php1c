<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

$code = 'Результат = Формат(102.68, "ЧЦ=8; ЧДЦ=0; ЧН=0; ЧГ=0");';
$str = makeCode($code, "Результат");
echo toString1C($str);

//$n = 5000;
//$array = array();
//$array[0] = false;
//$array[1] = false;
//for($i = 2; $i <= $n; $i++){
//    $array[$i] = true;
//}
//
//$timeBegin = hrtime(true)/1e+6;;
//$i = 2;
//while ( $i <= $n ) {
//    if( $array[$i] === true) {
//        $sq = $i * $i;
//        if($sq <= $n) {
//            $m = $sq;
//            while ($m <= $n) {
//                $array[$m] = false;
//                $m += $i;
//            }
//        }
//    }
//    $i += 1;
//}
//
//$timeEnd = hrtime(true)/1e+6;;
//$diff = $timeEnd - $timeBegin;
//echo $diff;
//
//$N=php1C\Number1C("5000");
//$МАССИВ=php1C\Array1C(array());
//
//$МАССИВ->Add(false);$МАССИВ->Add(false);
//for($INDEKS=php1C\Number1C("2") ;$INDEKS<=$N;$INDEKS=$INDEKS->add(1)){
//    $МАССИВ->Add(true);
//}
//$VREMYANACHALA=php1C\CurrentUniversalDateMilliseconds();
//for($INDEKS=php1C\Number1C("2") ;$INDEKS<=$N;$INDEKS=$INDEKS->add(1)){
//    if($МАССИВ->GET($INDEKS) ){
//        $KVADRAT=php1C\mul1C($INDEKS,$INDEKS);
//        if(php1C\less_equal1C($KVADRAT,$N)){
//            $M=$KVADRAT;
//            while(php1C\less_equal1C($M,$N)){
//                $МАССИВ->SET($M, false);
//                $M=php1C\add1C($M,$INDEKS);
//            }
//        }
//    }
//}
//$VREMYAOKONCHANIYA=php1C\CurrentUniversalDateMilliseconds();
//$REZULTAT=php1C\sub1C($VREMYAOKONCHANIYA,$VREMYANACHALA);
//echo '<--------->';
//echo $REZULTAT;
//
////---------------------------------------------------------------------
//
//$N=5000;
//$МАССИВ=php1C\Array1C(array());
//
//$МАССИВ->Add(false);$МАССИВ->Add(false);
//for($INDEKS=2 ;$INDEKS<=$N;$INDEKS=$INDEKS+1){
//    $МАССИВ->Add(true);
//}
//$VREMYANACHALA=php1C\CurrentUniversalDateMilliseconds();
//for($INDEKS=2 ;$INDEKS<=$N;$INDEKS=$INDEKS+1){
//    if($МАССИВ->GET($INDEKS) ){
//        $KVADRAT=$INDEKS*$INDEKS;
//        if($KVADRAT <= $N){
//            $M=$KVADRAT;
//            while($M < $N){
//                $МАССИВ->SET($M, false);
//                $M=$M + $INDEKS;
//            }
//        }
//    }
//}
//$VREMYAOKONCHANIYA=php1C\CurrentUniversalDateMilliseconds();
//$REZULTAT=php1C\sub1C($VREMYAOKONCHANIYA,$VREMYANACHALA);
//echo '<--------->';
//echo $REZULTAT;


//Скорость эратосфена (миллисекунды) (5000)
//- 1С 16
//- чистый php 0.6
//- эмуляция 1С индексы большие числа - 231
//- эмуляция 1С индексы целые числа 7
// Выполнятор 1С - 38

