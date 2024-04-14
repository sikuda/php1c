<?php

/**
* Общий модуль работы с 1С
* 
* Модуль для работы 1С
* 
* @author  sikuda@yandex.ru
*/
namespace php1C;
use Exception;

require_once('php1C_settings.php');
if (Language1C === 'en') {
 	require_once('lang/en.php');
}
else{
	require_once('lang/ru.php');
}

require_once('php1C_number.php');
require_once('php1C_string.php');
require_once('php1C_date.php');
require_once('php1C_collections.php');
require_once('php1C_file.php');

/**
* Массив функций PHP для общей работы с 1С. Соответствует элементам в языковых файлах.
*/   
const php1C_functionsPHP_Com = array('Message(','Find(','ValueIsFilled(','Type(','TypeOf(','toString1C(','toNumber1C(');

class undefined1C
{
    function __toString(){
        return php1C_Undefined;
    }
}

/**
* Выводит данные в представлении 1С (на установленном языке)
* @param $arg
* @return string Возвращаем значение как в 1С ('Да', 'Нет', Дату в формате 1С dd.mm.yyyy, 'Неопределенно' и другое
*/  
function toString1C($arg): string
{
	if(!isset($arg)) return php1C_Undefined;
	if(is_bool($arg)){
		if($arg === true ) return php1C_Bool[0]; //"Да";
		else return php1C_Bool[1]; //"Нет";
	}
	$val = strval($arg);
	//делаем пробелы между тысячными, миллионам и тд.
	if(Regional_grouping && is_numeric($arg)){
		$pos_point = strpos($val, '.');
        $val_int = $val;
        $val_fraction = "";
		if($pos_point !== false){
            $val_int = substr($val, 0, $pos_point);
            $val_fraction = substr($val, -$pos_point);
        }
		$val = implode(" ", str_split($val_int,3)).$val_fraction;
	} 
    return $val;
}

/**
 * Преобразует аргумент в число
 * @param $arg
 * @return mixed - Возвращаем значение числа как в 1С (string - для чисел повышенной точности, float - если повышенная точность не важна
 */
function toNumber1C($arg)
{
	if(fPrecision1C) return $arg;
	else return floatval($arg);
}

/**
 * Сложение двух переменных в 1С
 * @param $arg1
 * @param $arg2
 * @return string Результат сложение в зависимости от типа переменных (string, bool, Date1C)
 * @throws Exception
 */
function add1C($arg1, $arg2) {

	if(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) 
			if(fPrecision1C) return shrinkLastsZero(bcadd($arg1,$arg2,Scale1C));
			else return $arg1+$arg2;
	}
    elseif (is_string($arg1)) {
        return $arg1 . $arg2;
    }
	elseif(is_object($arg1)){
		if( (get_class($arg1) === 'php1C\Date1C') && is_numeric($arg2) ) return $arg1->add($arg2);
	}
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
 * Вычитание двух переменных в 1С
 * @param $arg1
 * @param $arg2
 * @return mixed - Date1C Результат вычитания в зависимости от типа переменных (float, Date1C, исключение)
 * @throws Exception
 */
function sub1C($arg1, $arg2){

	if(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2))
            if(fPrecision1C) return shrinkLastsZero(bcsub($arg1,$arg2,Scale1C));
			else return $arg1-$arg2;
	}
	elseif(is_object($arg1)){
		if( $arg1.is_object(Date1C::class) && is_numeric($arg2) && !is_string($arg2) )
            return $arg1->sub($arg2);
	}	
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
 * Умножение двух переменных в 1С
 * @param $arg1
 * @param $arg2
 * @return float|int|string - Результат сложение в зависимости от типа переменных (float или исключение)
 * @throws Exception
 */
function mul1C($arg1, $arg2)
{

	if((is_bool($arg1) || is_numeric($arg1)) && (is_bool($arg2) || is_numeric($arg2)) )
        if(fPrecision1C) {
            $scale = scaleLike1C($arg1);
            return shrinkLastsZero(bcmul($arg1,$arg2,$scale));
        }
        else return $arg1*$arg2;
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
 * Деление двух переменных в 1С
 * @param $arg1
 * @param $arg2
 * @return float|int|string Результат сложение в зависимости от типа переменных (float или исключение)
 * @throws Exception
 */
function div1C($arg1, $arg2){

	if((is_bool($arg1) || is_numeric($arg1)) && (is_bool($arg2) || is_numeric($arg2)) ){
		if(fPrecision1C){ 
			if( bccomp($arg2, "0", Scale1C) === 0) throw new Exception("Деление на 0");
			else {
                $scale = scaleLike1C($arg1);
                return shrinkLastsZero(round1C(bcdiv($arg1,$arg2,$scale+1), $scale));
            }
		}
		else{
			if(floatval($arg2) == 0) throw new Exception("Деление на 0");
			else return $arg1/$arg2;	
		}
	} 
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
 * Алгоритм числа знаков после запятой в вычислениях в 1С
 * https://mista.ru/topic/892985#20
 * @param string $arg1
 * @return int
 */
function scaleLike1C(string $arg1):int
{
    $pos = strpos($arg1, ".");
    if($pos === false) return  Scale1C_Int;
    $pos1 = mb_strlen($arg1) - 1;
    while(mb_substr($arg1,$pos1,1) == "0" && $pos1>$pos) $pos1--;
    $pos = $pos1 - $pos;
    if ($pos<10) return Scale1C;
    else return intdiv($pos-10,9)*9+45;
}


/**
 * //https://www.php.net/manual/en/function.bcscale.php
 * @param $number
 * @param int $scale
 * @return string
 */
function round1C($number, int $scale=0): string
{
    if($scale < 0) $scale = 0;
    $sign = '';
    if(bccomp('0', $number, 64) == 1) $sign = '-';
    $increment = $sign . '0.' . str_repeat('0', $scale) . '5';
    $number = bcadd($number, $increment, $scale+1);
    return bcadd($number, '0', $scale);
}

/**
 * Убрать последние нули в числе
 * @param string $arg
 * @return string
 */
function shrinkLastsZero(string $arg): string
{
    $pos = strpos($arg, ".");
    if($pos === false) return  $arg;
    $pos1 = mb_strlen($arg) - 1;
    while(mb_substr($arg,$pos1,1) == "0" && $pos1>$pos) $pos1--;
    if(mb_substr($arg,$pos1,1) == ".") $pos1--;
    return mb_substr($arg, 0, $pos1+1);
}

/**
* Операция преобразования bool в 0 или 1
* @param $arg
* @return float преобразование bool в 0 или 1
*/
function tran_bool($arg): float
{
	if($arg === true) return 1.0;
	else return 0.0;
}

/**
 * Операция ИЛИ в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции ИЛИ
 * @throws Exception
 */
function or1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(isset($arg1) && !is_string($arg1) && !is_object($arg1)) return $arg1 || $arg2;
	throw new Exception("Преобразование значения к типу Булево не может быть выполнено");
}

/**
 * Операция И в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции И
 * @throws Exception
 */
function and1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(isset($arg1) && !is_string($arg1) && !is_object($arg1)) return $arg1 && $arg2;
	throw new Exception("Преобразование значения к типу Булево не может быть выполнено");
}

/**
 * Операция Меньше в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции Меньше
 * @throws Exception
 */
function less1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 < $arg2;
	throw new Exception(php1C_error_BadOperTypeEqual);
}

/**
 * Операция Больше в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции Больше
 * @throws Exception
 */
function more1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 > $arg2;
	throw new Exception(php1C_error_BadOperTypeEqual);
}

/**
 * Операция Равно в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции Равно
 * @throws Exception
 */
function equal1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);

	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C'))
        if(fPrecision1C && is_numeric($arg1) && is_string($arg1) ){
            return shrinkLastsZero($arg1) === shrinkLastsZero($arg2);
        }
        else return $arg1 === $arg2;
	throw new Exception(php1C_error_BadOperTypeEqual);
}

/**
 * Операция НЕ Равно в 1С
 * @param $arg1
 * @param $arg2
 * @return bool Результат операции Равно
 * @throws Exception
 */
function notequal1C($arg1, $arg2): bool
{
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 !== $arg2;
	throw new Exception(php1C_error_BadOperTypeEqual);
}

/**
 * @throws Exception
 */
function more_equal1C($arg1, $arg2): bool
{
    return equal1C($arg1,$arg2) || more1C($arg1, $arg2);
}

/**
 * @throws Exception
 */
function less_equal1C($arg1, $arg2): bool
{
    return equal1C($arg1,$arg2) || less1C($arg1, $arg2);
}

// ---------------------- Общие функции -----------------------------

/**
 * Выводит сообщение через echo
 *
 * @param string $mess
 */
function Message(string $mess=''){
	echo toString1C($mess);
}

/**
* Находит строку в строке
* Хотя 1С считает эту функцию устаревшей, мы ее сделаем
*
* @param string $str строка в которой ищут
* @param string $substr строка поиска(которую ищут)
* @return int позицию найденной строки начиная с 1. Если ничего не найдено возвратит 0
*/
function Find(string $str='', string $substr=''): int
{
	$res = mb_strpos($str, $substr);
	if($res === false) return 0;
	else return $res+1;
}

/**
 * Проверяет заполненность параметра по 1C
 *
 * @param $val
 * @return bool если значение заполнено иначе ложь
 */
function ValueIsFilled($val): bool
{
	if(is_object($val)){
		switch (get_class($val)) {
		 	case 'php1C\Date1C': return $val != "01.01.0001 00:00:00";
		 	case 'php1C\Array1C':
		 	case 'php1C\ValueTable':
		 	case 'php1C\ValueTableColumnCollection': return ($val->Count()>0);	
		 	default:
		 		break;
		 } 
	}
	return isset($val);	
}

/*
* Класс для работы с типами 1С
*/
class Type1C{
	private string $val;

    function __construct($str = '') {
		$this->val = $str;
	}	

	function __toString(){
		return $this->val;
	}
}

/**
* ТипЗнч - Возвращает тип значения 1C
*
* @param  $val - объект для получения типа
* @return Type1C
*/
function TypeOf($val): Type1C
{
	$str = php1C_Undefined;
	if(is_bool($val)) $str = php1C_strBool;
	elseif(is_numeric($val)) $str = php1C_Number;
	elseif(is_string($val)) $str = php1C_String;
	elseif(is_object($val)) $str = $val->__toString();
	return new Type1C($str);

}

/**
* Тип - Возвращает тип 1С по его описанию в строке
*
* @param string $str строка описание типа
* @return Type1C
*/
function Type(string $str): Type1C
{
	return new Type1C($str);
}	

