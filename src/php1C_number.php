<?php
/**
* Модуль работы с числами и форматированием 1С
* 
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/

namespace php1C;
use Exception;

/**
* Массив названий русских функций для работы с числами
* @return string[] Массив названий функций работы с числами.
*/
function functions_Number(){
	return  array('Цел(','Окр(', 'Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(','Формат(', 'ЧислоПрописью(', 'НСтр(', 'ПредставлениеПериода(', 'СтрШаблон(', 'СтрокаСЧислом('  );
}

/**
* Массив названий английских функций для работы с числами. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с числами.
*/   
function functionsPHP_Number(){
	return array('Int(','Round(','Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(','Format(', 'NumberInWords(', 'NStr(', 'PeriodPresentation(', 'StrTemplate(', 'StringWithNumber(');
}
//Формат, ЧислоПрописью, НСтр, ПредставлениеПериода, СтрШаблон, СтрокаСЧислом	

/**
* Вызывает функцию работы с числами
*
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callNumberFunction($key, $arguments){
	switch($key){
		default:
			case 'Int(': return Int($arguments[0]);
			case 'Round(': return Round($arguments[0],$arguments[1]);
			case 'Log(': return Log($arguments[0]);
			case 'Log10(': return Log10($arguments[0]);
			case 'Sin(': return Sin($arguments[0]);
			case 'Cos(': return Cos($arguments[0]);
			case 'Tan(': return Tan($arguments[0]);
			case 'ASin(': return ASin($arguments[0]);
			case 'ACos(': return ACos($arguments[0]);
			case 'ATan(': return ATan($arguments[0]);
			case 'Exp(': return Exp($arguments[0]);
			case 'Pow(': return Pow($arguments[0],$arguments[1]);
			case 'Sqrt(': return Sqrt($arguments[0]);
			case 'NumberInWords(': return NumberInWords($arguments[0],$arguments[1]);
			case 'NStr(': return NStr($arguments[0]);
			case 'Format(': return Format($arguments[0], $arguments[1]);
			case 'PeriodPresentation(': return PeriodPresentation($arguments[0],$arguments[1],$arguments[2]);
			case 'StrTemplate(': return StrTemplate($arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4],$arguments[5],$arguments[6],$arguments[7],$arguments[8],$arguments[9],$arguments[10]);
			case 'StringWithNumber(': return StringWithNumber($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
			throw new Exception("Неизвестная функция работы с числами и форматированием ".$key."");
			break;
	}
}	

// -----------------------------------------------------------------------------------------------------------

/**
* Возращает целое число от вещественного числа
*
* @param  float $val число для получения целого   
* @return int целое число - результат  
*/
function Int( $val ){
	return intval($val);
}

/**
* Возращает результат округления числа
*
* @param  float $val число для округления  
* @param  int $pr точность округления
* @return float результат округдения   
*/
function Round($val, $pr){
	return \round($val, $pr);
}

/**
* Возращает целое число от вещественного числа
*
* @param  float $val число для получения целого   
* @return int целое число - результат  
*/
function Log($val){
	return \log($val);
}

/**
* Возращает целое число от вещественного числа
*
* @param  float $val число для получения целого   
* @return int целое число - результат  
*/
function Log10($val){
	return \log10($val);
}

/**
* Возращает синус числа
*
* @param  float $val число для получения синуса   
* @return float - результат  
*/
function Sin($val){
	return \sin($val);
}

/**
* Возращает косинус числа
*
* @param  float $val число для получения косинуса   
* @return float - результат  
*/
function Cos($val){
	return \cos($val);
}

/**
* Возращает тангенс числа
*
* @param  float $val число для получения тангенса   
* @return float - результат  
*/
function Tan($val){
	return \tan($val);
}

/**
* Возращает арксинус числа
*
* @param  float $val число для получения арксинуса   
* @return float - результат  
*/
function ASin($val){
	//var_dump(\asin($val));
	return \asin($val);
}

/**
* Возращает аркосинус числа
*
* @param  float $val число для получения арккосинуса   
* @return float - результат  
*/
function ACos($val){
	return \acos($val);
}

/**
* Возращает артангенс числа
*
* @param  float $val число для получения арктангенса   
* @return float - результат  
*/
function ATan($val){
	return \atan($val);
}

/**
* Возвращает e в степени число
*
* @param  float $val спетень экспонениты   
* @return float - результат  
*/
function Exp($val){
	return \exp($val);
}

/**
* Возвращает e в степени число
*
* @param  float $val спетень экспонениты   
* @return float - результат  
*/
function Pow($val, $exp){
	return \pow($val, $exp);
}

/**
* Возвращает e в степени число
*
* @param  float $val спетень экспонениты   
* @return float - результат  
*/
function Sqrt($val){
	return \sqrt($val);
}

//--------------------------------------- Форматирование -------------------------------------------

/**
* Возращает отформатированное значение величины по строке форматирования
*
* @param  number, date, bool $val для форматирования   
* @param  string $str_format строка форматирования   
* @return string результат форматирования
*
* ДФ (DF) - формат даты.
* д (d) - день месяца (цифрами) без лидирующего нуля;
* дд (dd) - день месяца (цифрами) с лидирующим нулем;
* ддд (ddd) - краткое название дня недели *);
* дддд (dddd) - полное название дня недели *);
* М (m) - минута без лидирующего нуля;
* ММ (mm) - минута с лидирующим нулем;
* МММ (MMM) - краткое название месяца *);
* ММММ (MMMM) - полное название месяца *);
* к (q) - номер квартала в году;
* г (y) - номер года без века и лидирующего нуля;
* гг (yy) - номер года без века с лидирующим нулем;
* гггг (yyyy) - номер года с веком;
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
*/
function Format($val,$str_format){
	$ar_format = array();
	$arstr = explode( ';', $str_format);

	foreach ($arstr as $value) {
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
				return $val->toFormat($frm);
			}
			$frm = $ar_format['ДЛФ'];
			if(isset($frm)){
				$frm = str_replace(
					array('\'','\"','ДД',    'DD',   'Д',    'D',    'В',     'T'),
					array('',  '',  'd.m.Y' ,'d.m.Y','d.m.Y','d.m.Y','h:m:s' ,'h:m:s' ,), 
				$frm);
				return $val->toFormat($frm);	
			} 
		}
    }
	return strval($val);
}

/**
* Представление числа прописью.
*
* @param  float $val чиcло для вывода   
* @param  float $frm форматная строка   
* @return string - результат  
*/
function NumberInWords($val, $frm){
	return 'Еще не реализовано';
}

/**
* Функция заглушка, возвращает или русскую строку или самому строчку
*
* @param  float $str Строки на разных языках, разделенные символом ";" (точка с запятой). Строка на одном языке состоит из кода языка, указанного в метаданных, символа "=" (равно) и собственно строки текста на данном языке в одинарных кавычках, двойных кавычках или без кавычек (когда указывается только один язык).
* @return string - результат  
*/
function NStr($str){
	$ar_format = array();
	$arstr = explode( ';', $str_format);
	foreach ($arstr as $value) {
		$duo = explode( '=', $value);
		$ar_format[trim($duo[0])]=$duo[1];
	}
	if(isset($ar_format['ru'])) return $ar_format['ru'];
	return str;
}

/**
* Функция заглушка, возвращает строковое представление периода
*
* @param  object Date1C date1 первая дата
* @param  object Date1C date2 вторая дата
* @param  string frm строка форматирования
* @return string - результат  
*/
function PeriodPresentation($date1,$date2,$frm){
	return 'Еще не реализовано';
}

/**
* Функция заглушка, возвращает или русскую строку или самому строчку
*
* @param  string str строка шаблон для вывода
* @param  float val1 первое число 
* @param  float val2 первое число 
* @param  float val3 первое число 
* @param  float val4 первое число 
* @param  float val5 первое число 
* @param  float val6 первое число 
* @param  float val7 первое число 
* @param  float val8 первое число 
* @param  float val9 первое число 
* @param  float val10 первое число 
* @return string - результат  
*/
function StrTemplate( $str,$val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8,$val9,$val10){
	return 'Еще не реализовано';	
}

/**
* Функция заглушка, Представление строки числа в требуемой форме.
*
* @param  string str строка шаблон для вывода
* @param  float val1 первое число 
* @param  string frm 
* @param  string prm параметры
* @return string - результат  
*/
function StringWithNumber($str,$val,$frm,$prm){
	return 'Еще не реализовано';
}