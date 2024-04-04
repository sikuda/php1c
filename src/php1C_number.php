<?php /** @noinspection PhpUnused */

/**
* Модуль работы с числами и форматированием 1С
* 
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/
namespace php1C;
use Exception;
use function bcpow;


/**
* Массив названий английских функций для работы с числами. Соответствует элементам русским функций.
*/   
const php1C_functionsPHP_Number = array('Int(','Round(','Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(','Format(', 'NumberInWords(', 'NStr('
	, 'PeriodPresentation(', 'StrTemplate(', 'StringWithNumber(');


/**
 * Вызывает функцию работы с числами
 *
 * @param string $key строка в названии функции со скобкой
 * @param array $arguments аргументы функции в массиве
 * @throws Exception
 */
//function callNumberFunction(string $key, array $arguments){
//	switch($key){
//		case 'Int(': return Int($arguments[0]);
//		case 'Round(': return Round($arguments[0],$arguments[1]);
//		case 'Log(': return Log($arguments[0]);
//		case 'Log10(': return Log10($arguments[0]);
//    	case 'Sin(': return Sin($arguments[0]);
//        case 'Cos(': return Cos($arguments[0]);
//        case 'Tan(': return Tan($arguments[0]);
//        case 'ASin(': return ASin($arguments[0]);
//        case 'ACos(': return ACos($arguments[0]);
//        case 'ATan(': return ATan($arguments[0]);
//        case 'Exp(': return Exp($arguments[0]);
//        case 'Pow(': return Pow($arguments[0],$arguments[1]);
//        case 'Sqrt(': return Sqrt($arguments[0]);
//        case 'NumberInWords(': return NumberInWords($arguments[0],$arguments[1]);
//        case 'NStr(': return NStr($arguments[0]);
//        case 'Format(': return Format($arguments[0], $arguments[1]);
//        case 'PeriodPresentation(': return PeriodPresentation($arguments[0],$arguments[1],$arguments[2]);
//        case 'StrTemplate(': return StrTemplate($arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4],$arguments[5],$arguments[6],$arguments[7],$arguments[8],$arguments[9],$arguments[10]);
//        case 'StringWithNumber(': return StringWithNumber($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
//        default:
//			throw new Exception("Неизвестная функция работы с числами и форматированием ".$key);
//	}
//}

// -----------------------------------------------------------------------------------------------------------

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
 * @return float|int|object|string - результат
 */
function Pow($val, $exp){
    if (fPrecision1C) return bcpow($val, $exp, Scale1C);
	return \pow($val, $exp);
}

/**
 * Возвращает e в степени число
 *
 * @param  $val - параметр корня
 */
function Sqrt($val){
    if (fPrecision1C) return bcsqrt($val, Scale1C);
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
	elseif(is_numeric($val)){
		$pr = $ar_format['ЧДЦ'];
		if(!isset($pr)){
			$pr = strpos( strval($val), '.');
			if($pr === false) $pr = 0;
			else $pr = strlen(strval($val)) - $pr - 1;
		} 
		$dec = $ar_format['ЧРД'];
		if(!isset($dec)) $dec= '.';
		$th = $ar_format['ЧРГ'];
		if(!isset($th)) $th= ' ';
		return number_format($val, $pr, $dec, $th); 
	}
	elseif(is_object($val)){
		//Это дата
        $name = get_class($val);
		if( $name === 'php1C\Date1C'){
            $frm = $ar_format['ДФ'];
			if(isset($frm)){
				$frm = str_replace(
					array('\'','\"','гггг','yyyy','гг','yy','дд','dd','ММ','MM','чч','hh','ЧЧ','HH','мм','mm'),
					array('',  '',  'Y'   ,'Y'   ,'y' ,'y' ,'d' ,'d' ,'m', 'm' ,'h' ,'h' ,'H' ,'H' ,'i' ,'i'), 
				$frm);
                if(method_exists($val,'toFormat'))
				    return $val->toFormat($frm);
			}
			$frm = $ar_format['ДЛФ'];
			if(isset($frm)){
				$frm = str_replace(
					array('\'','\"','ДД',    'DD',   'Д',    'D',    'В',     'T'),
					array('',  '',  'd.m.Y' ,'d.m.Y','d.m.Y','d.m.Y','h:m:s' ,'h:m:s' ,), 
				$frm);
                if(method_exists($val,'toFormat'))
				    return $val->toFormat($frm);
			} 
		}
    }
	return strval($val);
}

/**
* Представление числа прописью.
*
* @param  $val
* @param  string $frm форматная строка
* @return string - результат  
*/
function NumberInWords($val, string $frm): string{
	return 'Еще не реализовано'.$val.$frm;
}

/**
* Функция заглушка, возвращает русскую строку или самому строчку
*
* @param  string $str Строки на разных языках, разделенные символом ";" (точка с запятой). Строка на одном языке состоит из кода языка, указанного в метаданных, символа "=" (равно) и собственно строки текста на данном языке в одинарных кавычках, двойных кавычках или без кавычек (когда указывается только один язык).
* @return string - результат  
*/
function NStr(string $str): string
{
	$ar_format = array();
	$ar_str = explode( ';', $str);
	foreach ($ar_str as $value) {
		$duo = explode( '=', $value);
		$ar_format[trim($duo[0])]=$duo[1];
	}
	if(isset($ar_format['ru'])) return $ar_format['ru'];
	return $str;
}

/**
* Функция заглушка, возвращает строковое представление периода
*
* @param  Date1C $date1 первая дата
* @param  Date1C $date2 вторая дата
* @param  string $frm строка форматирования
* @return string - результат  
*/
function PeriodPresentation(Date1C $date1,Date1C $date2,string $frm): string
{
	return 'Еще не реализовано'.$date1.$date2.$frm;
}

/**
* Функция заглушка, возвращает русскую строку или самому строчку
*
* @param string $str строка шаблон для вывода
* @param  $val1 - число
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
function StrTemplate(string $str, $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10): string
{
	return 'Еще не реализовано'.$str.$val1.$val2.$val3.$val4.$val5.$val6.$val7.$val8.$val9.$val10;
}

/**
* Функция заглушка, Представление строки числа в требуемой форме.
*
* @param  string $str str строка шаблон для вывода
* @param  $val - первое число
* @param  string $prm параметры
* @return string - результат  
*/
function StringWithNumber(string $str, $val, string $frm, string $prm): string{
	return 'Еще не реализовано'.$str.$val.$frm.$prm;
}