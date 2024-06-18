<?php

/**
* Модуль работы с числами и форматированием 1С
* 
* 
* @author  sikuda@yandex.ru
* @version 0.3
*/
namespace php1C;
use Exception;


/**
* Массив названий английских функций для работы с числами. Соответствует элементам русским функций.
*/   
const php1C_functionsPHP_Number = array('Int(','Round(','Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(','Format(', 'NumberInWords(', 'NStr('
	, 'PeriodPresentation(', 'StrTemplate(', 'StringWithNumber(');

// -----------------------------------------------------------------------------------------------------------

class Number1C
{
    /**
     * @var string внутреннее хранение числа
     */
    private string $value;

    /**
     * @throws Exception
     */
    function __construct($val) {
        if (is_numeric($val)) { $this->value = strval($val); }
        else throw new Exception(php1C_error_ConvertToNumberBad);
    }

    function getValue(): string
    {
        return $this->value;
    }

    function __toString(){
        return $this->value;
    }

    /**
     * @throws Exception
     */
    function add($arg): Number1C {
        $res = bcadd($this->value, strval($arg),Scale1C);
        return new Number1C($this->shrinkLastsZero($res));
    }

    /**
     * @throws Exception
     */
    function sub($arg): Number1C {
        $res = bcsub($this->value, strval($arg),Scale1C);
        return new Number1C($this->shrinkLastsZero($res));
    }

    /**
     * @throws Exception
     */
    function mul($arg): Number1C {
        $scale = $this->scaleLike1C($this->value);
        return new Number1C($this->shrinkLastsZero(bcmul($this->value, $arg, $scale)));
    }

    function div($arg): Number1C {

        if( bccomp($arg, "0", Scale1C) === 0) throw new Exception("Деление на 0");
        else {
            $scale = $this->scaleLike1C($this->value);
            return Number1C($this->shrinkLastsZero($this->round1C(bcdiv($this->value, $arg,$scale+1), $scale)));
        }
    }

    function or(Number1C $arg): bool{
        return $this->value || $arg->getValue();
    }
    function and(Number1C $arg): bool{
        return $this->value && $arg->getValue();
    }
    function equal(Number1C $arg): bool{
        return $this->cmp($arg) === 0;
    }
    function less(Number1C $arg): bool{
        return $this->cmp($arg) === -1;
    }
    function more(Number1C $arg): bool{
        return $this->cmp($arg) === 1;
    }

    private function cmp($arg): int {
        $scale = $this->scaleLike1C($this->value);
        return bccomp($this->value, $arg->getValue(), $scale);
    }

    /**
     * Алгоритм числа знаков после запятой в вычислениях в 1С
     * https://mista.ru/topic/892985#20
     * @param string $arg1
     * @return int
     */
    private function scaleLike1C(string $arg1):int
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
    private function round1C($number, int $scale=0): string
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
     * @return Number1C
     */
    private function shrinkLastsZero(string $arg): string
    {
        $pos = strpos($arg, ".");
        if($pos === false) return  $arg;
        $pos1 = mb_strlen($arg) - 1;
        while(mb_substr($arg,$pos1,1) == "0" && $pos1>$pos) $pos1--;
        if(mb_substr($arg,$pos1,1) == ".") $pos1--;
        return mb_substr($arg, 0, $pos1+1);
    }
}

function Number1C($arg){
    return new Number1C($arg);
}

/**
* Возвращает целое число от вещественного числа
*
* @param  $val - число для получения целого
* @return int целое число - результат  
*/
function Int( $val ): int
{
	return intval($val);
}

/**
 * Возвращает результат округления числа
 *
 * @param  $val - число для округления
 * @param int $pr точность округления
 */
function Round($val, int $pr): float
{
	return \round($val, $pr);
}

/**
 * Возвращает целое число от вещественного числа
 *
 * @param  $val - число для получения целого
 */
function Log($val): float
{
	return \log($val);
}

/**
 * Возвращает целое число от вещественного числа
 *
 * @param  $val - число для получения целого
 */
function Log10($val): float
{
	return \log10($val);
}

/**
* Возвращает синус числа
*
* @param  $val - число для получения синуса
*/
function Sin($val): float
{
	return \sin($val);
}

/**
* Возвращает косинус числа
*
* @param  $val - число для получения косинуса
*/
function Cos($val): float
{
	return \cos($val);
}

/**
* Возвращает тангенс числа
*
* @param $val - число для получения тангенса
*/
function Tan($val): float
{
	return \tan($val);
}

/**
* Возвращает арксинус числа
*
* @param  $val - число для получения арксинуса
*/
function ASin($val): float
{
	//var_dump(\asin($val));
	return \asin($val);
}

/**
* Возвращает арккосинус числа
*
* @param  $val - число для получения арккосинуса
*/
function ACos($val): float
{
	return \acos($val);
}

/**
* Возвращает арктангенс числа
*
* @param  $val - число для получения арктангенса
*/
function ATan($val): float
{
	return \atan($val);
}

/**
 * Возвращает e в степени число
 *
 * @param  $val - степень экспоненты
 * @return float - результат
 */
function Exp($val): float
{
    return \exp($val);
}

/**
 * Возвращает e в степени число
 *
 * @param  $val - число экспоненты
 * @param  $exp - степень экспоненты
 * @return float|int|Number1C - результат
 * @throws Exception
 */
function Pow($val, $exp){
    if($val instanceof Number1C && $exp instanceof Number1C)
        return new Number1C(bcpow($val->getValue(), $exp->getValue(), Scale1C));
    return \pow($val, $exp);
}


/**
 * Возвращает e в степени число
 *
 * @param  $val - параметр корня
 */
function Sqrt($val){
    if($val instanceof Number1C) return new Number1C(bcsqrt($val->getValue(), Scale1C));
    else return \sqrt($val);
}

//--------------------------------------- Форматирование -------------------------------------------

/**
 * Возвращает отформатированное значение величины по строке форматирования
 *
 * @param number $val , date, bool $val для форматирования
 * @param string $str_format строка форматирования
 * @return string результат форматирования
 *
 * ДФ (DF) - формат даты.
 * (д (d) - день месяца (цифрами) без лидирующего нуля);
 * дд (dd) - день месяца (цифрами) с лидирующим нулем;
 * (ддд (ddd) - краткое название дня недели *);
 * (дддд (dddd) - полное название дня недели *);
 * М (m) - минута без лидирующего нуля;
 * ММ (mm) - минута с лидирующим нулем;
 * (МММ (MMM) - краткое название месяца *);
 * (ММММ (MMMM) - полное название месяца *);
 * к (q) - номер квартала в году;
 * г (y) - номер года без века и лидирующего нуля;
 * гг (yy) - номер года без века с лидирующим нулем;
 * (гггг (yyyy) - номер года с веком);
 * ч (h) - час в 12-часовом варианте без лидирующих нулей;
 * чч (hh) - час в 12-часовом варианте с лидирующим нулем;
 * Ч (H) - час в 24-часовом варианте без лидирующих нулей;
 * ЧЧ (HH) - час в 24-часовом варианте с лидирующим нулем;
 * м (m) - минута без лидирующего нуля;
 * мм (mm) - минута с лидирующим нулем;
 * с (s) - секунда без лидирующего нуля;
 * сс (ss) - секунда с лидирующим нулем;
 *
 * - БЛ (BF) - строка, представляющая логическое значение Ложь.
 * - БИ (BT) - строка, представляющая логическое значение Истина.
 * @throws Exception
 */
function Format($val, string $str_format): string
{
	$ar_format = array();
	$ar_str = explode( ';', $str_format);

	foreach ($ar_str as $value) {
		//echo 'val:'.$value;
		$duo = explode( '=', $value);
		$ar_format[trim($duo[0])]=$duo[1];
		//echo 'duo-'.trim($duo[0]).'<->'.$duo[1];
	}
	if(is_bool($val)){
		if($val) return $ar_format['БИ'];
		else return $ar_format['БЛ'];	
	}
	elseif($val instanceof Number1C){
        $val = $val->getValue();
		$pr = $ar_format['ЧДЦ'];
		if(!isset($pr) && !$ar_format['ЧЦ']){
			$pr = strpos( strval($val), '.');
			if($pr === false) $pr = 0;
			else $pr = strlen(strval($val)) - $pr - 1;
		} 
		$dec = $ar_format['ЧРД'];
		if(!isset($dec)) $dec= '.';
		$th = $ar_format['ЧРГ'];
        if ($ar_format['ЧГ']==='0') $th="";
		if(!isset($th)) $th= mb_chr(160);

        $res = number_format($val, $pr, $dec, $th);
        if (isset($ar_format['ЧВН']) && isset($ar_format['ЧЦ'])){
            if (intval($ar_format['ЧЦ']) > strlen($res))
                return str_repeat('0', intval($ar_format['ЧЦ']) - strlen($res)).$res;
            else return str_repeat('9', intval($ar_format['ЧЦ']));
        }
        else return $res;
	}
	elseif($val instanceof Date1C){
    //Это дата
        $frm = $ar_format['ДФ'];
        if(isset($frm)){
            $frm = str_replace(
                array('\'','\"','гггг','yyyy','гг','yy','дд','dd','ММ','MM','чч','hh','ЧЧ','HH','мм','mm','сс','ss'),
                array('',  '',  'Y'   ,'Y'   ,'y' ,'y' ,'d' ,'d' ,'m', 'm' ,'h' ,'h' ,'H' ,'H' ,'i' ,'i','s','s'),
            $frm);
            if(method_exists($val,'toFormat'))
                return $val->toFormat($frm);
        }
        $frm = $ar_format['ДЛФ'];
        if(isset($frm)){
            $php1C_endOfYear = char(160).'г.';
            $frm = str_replace(
                array('\'','\"','ДД',                   'DD',                   'Д',    'D',    'В',     'T'),
                array(  '',  '','j F Y'.$php1C_endOfYear,'j F Y'.$php1C_endOfYear,'d.m.Y','d.m.Y','H:m:s' ,'H:m:s' ,),
            $frm);
            if(method_exists($val,'toFormat'))
                return $val->toFormat($frm);
        }
    }

	return strval($val);
}

/**
* Представление числа прописью.
*
* @param  string|Number1C
* @param  string $frm форматная строка
* @return string - результат  
*/
function NumberInWords($val, string $frm=""): string{
    if ($val instanceof Number1C) $val = $val->getValue();
	return 'Еще не реализовано'.$val.$frm;
}

/**
 * Число прописью
* Функция заглушка, возвращает русскую строку или самому строчку
*
* @param  string $str Строки на разных языках, разделенные символом ";" (точка с запятой).
 * Строка на одном языке состоит из кода языка, указанного в метаданных,
 * символа "=" (равно) и собственно строки текста на данном языке в одинарных кавычках,
 * двойных кавычках или без кавычек (когда указывается только один язык).
* @return string|Number1C - результат
*/
function NStr($str): string
{
    if ($str instanceof Number1C) $str = $str->getValue();

	$ar_format = array();
	$ar_str = explode( ';', $str);
	foreach ($ar_str as $value) {
		$duo = explode( '=', $value);
		$ar_format[trim($duo[0])]=trim($duo[1]);
	}
	if(isset($ar_format[php1C_lang])) return trim($ar_format[php1C_lang],"'");
	return "";
}

/**
* Функция заглушка, возвращает строковое представление периода
*
* @param  Date1C $date1 первая дата
* @param  Date1C $date2 вторая дата
* @param  string $frm строка форматирования
* @return string - результат  
*/
function PeriodPresentation(Date1C $date1, Date1C $date2, string $frm=""): string
{
	return 'Еще не реализовано'.$date1.$date2.$frm;
}

/**
* Функция заглушка, возвращает русскую строку или самому строчку
*
* @param string $str строка шаблон для вывода
* @param  $val1 - string|Number1C
* @param  $val2 - число
* @param  $val3 - число
* @param  $val4 - число
* @param  $val5 - число
* @param  $val6 - число
* @param  $val7 - число
* @param  $val8 - число
* @param  $val9 - число
* @param  $val10 - число
* @return string - результат  
*/
function StrTemplate(string $str, $val1="", $val2="", $val3="", $val4="", $val5="", $val6="", $val7="", $val8="", $val9="", $val10=""): string
{
    if($val1 instanceof Number1C) $val1 = $val1->getValue();
    if($val2 instanceof Number1C) $val1 = $val2->getValue();
    if($val3 instanceof Number1C) $val1 = $val3->getValue();
    if($val4 instanceof Number1C) $val1 = $val4->getValue();
    if($val5 instanceof Number1C) $val1 = $val5->getValue();
    if($val6 instanceof Number1C) $val1 = $val6->getValue();
    if($val7 instanceof Number1C) $val1 = $val7->getValue();
    if($val8 instanceof Number1C) $val1 = $val8->getValue();
    if($val9 instanceof Number1C) $val1 = $val9->getValue();
    if($val10 instanceof Number1C) $val1 = $val10->getValue();

	return 'Еще не реализовано'.$str.$val1.$val2.$val3.$val4.$val5.$val6.$val7.$val8.$val9.$val10;
}

/**
* Функция заглушка, Представление строки числа в требуемой форме.
*
* @param  string $str str строка шаблон для вывода
* @param  $val - string|Number1C
* @param  string $prm параметры
* @return string - результат  
*/
function StringWithNumber(string $str, $val="", string $frm="", string $prm=""): string{
    if($val instanceof Number1C) $val = $val->getValue();
	return 'Еще не реализовано'.$str.$val.$frm.$prm;
}