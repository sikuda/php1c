<?php
/**
* Модуль работы со строками 1С
* 
* Модуль функций для со строками 1С. (Устаревшая Найти в Общем модуле)
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/
namespace php1C;
use Exception;
require_once('php1C_collections.php'); //для функций СтрРазделить, СтрСоединить

/**
* Массив названий английских функций для работы со строками. Соответстует элементам русским функций.
*/   
const php1C_functionsPHP_String = array('StrLen(',  'TrimL(','TrimR(','TrimLR(','Left(','Right(','Mid(','StrFind(','Lower(','Upper(','Title(','Char(','CharCode(',   'IsBlankString(','StrReplace(', 'StrLineCount(', 'StrGetLine(',      'StrOccurrenceCount(','StrCompare(', 'StrStartsWith(', 'StrEndsWith(',                     'StrSplit(', 'StrConcat(');

/**
* Вызывает функцию работы с датой
*
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callStringFunction($key, $arguments){
	switch($key){
		case 'StrLen(': return StrLength($arguments[0]);
		case 'TrimL(': return TrimL($arguments[0]);
		case 'TrimR(': return TrimR($arguments[0]);
		case 'TrimLR(': return TrimLR($arguments[0]);
		case 'Left(': return Left($arguments[0], $arguments[1]);
		case 'Right(': return Right($arguments[0], $arguments[1]);
		case 'Mid(': return Mid($arguments[0], $arguments[1], $arguments[2]);
		case 'StrFind(': return StrFind($arguments[0], $arguments[1], $arguments[2],$arguments[3],$arguments[4]);
		case 'Lower(': return Lower($arguments[0]);
		case 'Upper(': return Upper($arguments[0]);
		case 'Title(': return Title($arguments[0]);
		case 'Char(': return Char($arguments[0]);
		case 'CharCode(': return CharCode($arguments[0], $arguments[1]);
		case 'IsBlankString(': return IsBlankString($arguments[0]);
		case 'StrReplace(': return StrReplace($arguments[0], $arguments[1],$arguments[2]);
		case 'StrLineCount(': return StrLineCount($arguments[0]);
		case 'StrGetLine(': return StrGetLine($arguments[0], $arguments[1]);
		case 'StrOccurrenceCount(': return StrOccurrenceCount($arguments[0], $arguments[1]);
		case 'StrCompare(': return StrCompare($arguments[0], $arguments[1]);
		case 'StrStartsWith(': return StrStartsWith($arguments[0], $arguments[1]);
		case 'StrEndsWith(': return StrEndsWith($arguments[0], $arguments[1]);
		case 'StrSplit(': return StrSplit($arguments[0], $arguments[1],$arguments[2]);
		case 'StrConcat(': return StrConcat($arguments[0], $arguments[1]);
		default:
			throw new Exception("Неизвестная функция работы со строкой ".$key."");
			break;
	}
}	

// -----------------------------------------------------------------------------------------------------------

/**
* Возращает длину строки текста
*
* @param  string обычно строкаа 
* @return float длина строки 
*/
function StrLength( $arg ){
	return strlen($arg);
}

/**
* Возращает строку без левых пробелов
*
* @param  string cтрока 
* @return string строка 
*/
function TrimL( $arg ){
	return ltrim($arg);
}

/**
* Возращает строку без правых пробелов(и др спец. символов пробелов)
*
* @param  string cтрока 
* @return string строка  
*/
function TrimR( $arg ){
	return rtrim($arg);
}

/**
* Возращает строку без левых и правых пробелов
*
* @param  string cтрока 
* @return string строка  
*/
function TrimLR( $arg ){
	return trim($arg);
}

/**
* Возращает строку в количестве $arg2 левых элементов строки $arg1
*
* @param  $arg1 string cтрока 
* @param  $arg2 int количество элементов строки 
* @return string строка  
*/
function Left( $arg1, $arg2 ){
	return substr($arg1, 0, $arg2);
}

/**
* Возращает строку в количестве $arg2 правых элементов строки $arg1
*
* @param  $arg1 string cтрока 
* @param  $arg2 int количество элементов строки 
* @return string строка  
*/
function Right( $arg1, $arg2 ){
	return substr($arg1, count($arg1)-$arg2-1, $arg2);
}

/**
* Возращает строку начиная с $arg2(счет с 1) в количестве $arg3 элементов строки $arg1
*
* @param  $arg1 string cтрока 
* @param  $arg2 int позиция первого элемента начиная с 1 
* @param  $arg3 int количество элементов 
* @return string строка  
*/
function Mid( $arg1, $arg2, $arg3 ){
	if($arg2<=0) $arg2 = 1;
	return substr($arg1, $arg2-1, $arg3);
}

//TODO - пока без трех последних параметров
/**
* Возращает строку начиная с $arg2(счет с 1) в количестве $arg3 элементов строки $arg1
* 
* @param  $arg1 string cтрока 
* @param  $arg2 strung подстрока поиска 
* @param  $arg3 <> направление поиска 
* @param  $arg4 int начальная позиция поиска (если слева то с 1) 
* @param  $arg5 int количество вхождений в поиск 
* @return int найденная позиция в подстроке начиная с 1, если не нашел то 0  
*/
function StrFind($arg1,$arg2,$arg3=1,$arg4=1,$arg5=1){
	$res = strpos($arg1, $arg2);
	if($res === false) return 0;
	else return $res+1;
}

/**
* Возращает строку без нижнем регистре
*
* @param  string cтрока 
* @return string строка 
*/
function Lower( $arg ){
	return strtolower($arg);
}

/**
* Возращает строку без верхнем регистре
*
* @param  string cтрока 
* @return string строка 
*/
function Upper( $arg ){
	return strtoupper($arg);
}

/**
* Возращает строку все первые буквы слов в верхнем регистре
*
* @param  string $str начальная cтрока 
* @return string полученная строка 
*/
function Title( $str ){
	$len = strlen($str);
	$str = strtoupper(substr($str,0,1)).substr($str,1);
	for ($i=0; $i < $len; $i++) {
		if($i<($len-1)){ 
			if( strpos("\r\n\t ", substr($str,$i,1)) !== false) 
				$str = substr($str,0,$i+1).strtoupper(substr($str,$i+1,1)).substr($str,$i+2);
			else $str = substr($str,0,$i+1).strtolower(substr($str,$i+1,1)).substr($str,$i+2);
		}	
	}
	return $str;
}

/**
* Возращает строку из кода символа
*
* @param  int dec код символа 
* @return string строка 
*/
function Char( $dec ){
	//us1.php.net/manual/ru/function.chr.php#55978
	if ($dec < 128) { 
		$utf = chr($dec); 
	} else if ($dec < 2048) { 
		$utf = chr(192 + (($dec - ($dec % 64)) / 64)); 
		$utf .= chr(128 + ($dec % 64)); 
	} else { 
		$utf = chr(224 + (($dec - ($dec % 4096)) / 4096)); 
		$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64)); 
		$utf .= chr(128 + ($dec % 64)); 
	} 
	return $utf;
}

/**
* Возращает код символа из буквы строки
*
* @param  string str входящая строка
* @param  string num номер символа в стрке начиная с 1 
* @return int код символа 
*/
function CharCode( $str, $num_letter=1){

	if(!isset($num_letter)) $num_letter = 1;
	if(!is_numeric($num_letter) || $num_letter<1) return -1;
	$character = substr($str, $num_letter-1);
	//get from drupal https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Transliteration%21PhpTransliteration.php/function/PhpTransliteration%3A%3AordUTF8/8.2.x
	$first_byte = ord($character[0]);
	if (($first_byte & 0x80) == 0) {

	// Single-byte form: 0xxxxxxxx.
	return $first_byte;
	}
	if (($first_byte & 0xe0) == 0xc0) {

	// Two-byte form: 110xxxxx 10xxxxxx.
	return (($first_byte & 0x1f) << 6) + (ord($character[1]) & 0x3f);
	}
	if (($first_byte & 0xf0) == 0xe0) {

	// Three-byte form: 1110xxxx 10xxxxxx 10xxxxxx.
	return (($first_byte & 0xf) << 12) + ((ord($character[1]) & 0x3f) << 6) + (ord($character[2]) & 0x3f);
	}
	if (($first_byte & 0xf8) == 0xf0) {

	// Four-byte form: 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx.
	return (($first_byte & 0x7) << 18) + ((ord($character[1]) & 0x3f) << 12) + ((ord($character[2]) & 0x3f) << 6) + (ord($character[3]) & 0x3f);
	}

	// Other forms are not legal.
	return -1;
}

/**
* Проверяет строку на пустоту
*
* @param  string $arg входящая строка
* @return bool строка пустая 
*/
function IsBlankString($arg){
	return strlen($arg) == 0;
}

/**
* Возращает строку замены одного шаблона другим
*
* @param  string arg1 исходная строка
* @param  string arg2 строка поиска
* @param  string arg3 строка замены 
* @return string результат замены  
*/
function StrReplace($arg1, $arg2,$arg3){
	return str_replace($arg2, $arg3, $arg1);
}

/**
* Возращает код символа из буквы строки
*
* @param  string str входящая строка
* @return int количество строк с строке
*/
function StrLineCount($str){
	return substr_count($str, chr(10)) + 1;
}

/**
* Возращает num(начиная с 1) строку из общей строки
*
* @param  string str входящая строка
* @param  int num номер строки в общей строке
* @return string получившая строка 
*/
function StrGetLine($str, $num){
	while($num>1){
		$pos = strpos($str, chr(10));
		$str = substr($str, $pos+1);
		$num--;
	}
	$pos = strpos($str,chr(10));
	if($роs !== false){
		$str = substr($str, 0, $pos);	
	} 
	return $str;
}

/**
* Возращает число вхождений подстроки в строку
*
* @param  string str входящая строка
* @param  string substr подстрока поиска
* @return int число вхождений подстроки в строку 
*/
function StrOccurrenceCount($str, $substr){
	return substr_count($str, $substr);
}

/**
* Возращает число вхождений подстроки в строку
*
* @param  string str1 первая строка
* @param  string str2 вторая строка
* @return int число результат сравнения строк 
*/
function StrCompare($str1, $str2){
	$res = strcmp($str1,$str2);
	if($res > 0) $res = 1;
	if($res < 0) $res = -1;
	return $res;
}

/**
* Возращает число вхождений подстроки в строку
*
* @param  string str первая строка
* @param  string substr вторая строка
* @return int число результат сравнения строк 
*/
function StrStartsWith($str, $substr){
	$res = strpos($str, $substr);
	if($res===0) return true;
	else return false;
}	

/**
* Возращает число вхождений подстроки в строку
*
* @param  string str первая строка
* @param  string substr вторая строка
* @return int число результат сравнения строк 
*/
function StrEndsWith($str, $substr){
	$res = strrpos($str, $substr);
	//return $res;
	if($res == (strlen($str)-strlen($substr))) return true;
	else return false;
}

/**
* Возращает массив из подстрок общей строки
*
* @param  string str общая строка
* @param  string spliter строка разделитель
* @return bool andempty включать в результат пустые строки 
*/
function StrSplit($str, $spliter, $andempty){
	$array = explode($spliter, $str);
	if(!$andempty){
		$array = array_filter($array, function($var){ return (!isset($var) || (strlen($var)==0));} );
	}
	return new Array1C(null, $array);

}

/**
* Возращает массив из подстрок общей строки
*
* @param  Аrray1C array1C массив строк для объединения
* @param  string spliter строка разделитель
* @return string объединенная получаемая строка   
*/
function StrConcat($array1C, $spliter){
	return implode($array1C->getArray(), $spliter);
}