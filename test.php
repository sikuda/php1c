<?php
use function php1C\toString1C;
use function php1C\makeCode;

require_once('src/php1C__code.php');

//$code = 'Массив = Новый Массив(); Массив[0] = Истина;Результат = Массив[0];';
//$str = makeCode($code, "Результат");
//echo toString1C($str);

$n = 5000;
$array = array();
$array[0] = false;
$array[1] = false;
for($i = 2; $i <= $n; $i++){
    $array[$i] = true;
}

$timeBegin = hrtime(true)/1e+6;;
$i = 2;
while ( $i <= $n ) {
    if( $array[$i] === true) {
        $sq = $i * $i;
        if($sq <= $n) {
            $m = $sq;
            while ($m <= $n) {
                $array[$m] = false;
                $m += $i;
            }
        }
    }
    $i += 1;
}

$timeEnd = hrtime(true)/1e+6;;
$diff = $timeEnd - $timeBegin;
echo $diff;

$N=5000;
$MASSIV=php1C\Array1C(array());

$MASSIV->SET("0", false);
$MASSIV->SET("1", false);

for($INDEKS=2 ;$INDEKS<=$N;$INDEKS=php1C\add1C($INDEKS,1)){
    $MASSIV->SET($INDEKS, true);
}

$VREMYANACHALA=php1C\CurrentUniversalDateMilliseconds();
//for($INDEKS=2 ;$INDEKS<=$N;$INDEKS=php1C\add1C($INDEKS,1)){
for($INDEKS=2 ; $INDEKS<=$N; $INDEKS +=1){
    if( $MASSIV->GET($INDEKS) ){
    //if( $MASSIV->value[$INDEKS] ){
        //$KVADRAT=php1C\mul1C($INDEKS,$INDEKS);
        $KVADRAT= $INDEKS * $INDEKS;
        //if( php1C\less_equal1C($KVADRAT,$N)){
        if($KVADRAT <= $N){
            $M=$KVADRAT;
            //while(  php1C\less_equal1C($M,$N)){
            //while( is_numeric($M) && is_numeric($N) && $M <= $N ){
            while( $M <= $N ){
                    $MASSIV->SET($M, false);
                    //$MASSIV->value[$M] = false;
                    //$M =php1C\add1C($M,$INDEKS);
                    $M += $INDEKS;
            }
        }
    }
}

$VREMYAOKONCHANIYA=php1C\CurrentUniversalDateMilliseconds();
$REZULTAT=php1C\sub1C($VREMYAOKONCHANIYA,$VREMYANACHALA);echo '<--------->';
echo $REZULTAT;

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

