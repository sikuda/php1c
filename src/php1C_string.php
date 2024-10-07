<?php /** @noinspection ALL */

/**
* Модуль работы со строками 1С
* 
* Модуль функций для со строками 1С. (Устаревшая Найти в Общем модуле)
* 
* @author  sikuda@yandex.ru
* @version 0.3
*/
namespace Sikuda\Php1c;
use Exception;
require_once('php1C_collections.php'); //для функций СтрРазделить, СтрСоединить

/**
* Массив названий английских функций для работы со строками. Соответствует элементам русским функций.
*/   
const php1C_functionsPHP_String = array('StrLen(',  'TrimL(','TrimR(','TrimLR(','Left(','Right(','Mid(','StrFind(','Lower(','Upper(','Title(','Char(','CharCode(',   'IsBlankString(','StrReplace(', 'StrLineCount(', 'StrGetLine(',      'StrOccurrenceCount(','StrCompare(', 'StrStartsWith(', 'StrEndsWith(',                     'StrSplit(', 'StrConcat(');

// -----------------------------------------------------------------------------------------------------------

/**
* Возвращает длину строки текста
*
* @param  string $arg обычно строка обычно русская
* @return int длина строки
*/
function StrLength( string $arg ): int
{
	return mb_strlen($arg);
}

/**
* Возвращает строку без левых пробелов
*
* @param string $arg
* @return string
*/
function TrimL(string $arg ): string
{
	return ltrim($arg);
}

/**
* Возвращает строку без правых пробелов(и др спец. символов пробелов)
*
* @param string $arg
* @return string
*/
function TrimR(string $arg ): string
{
	return rtrim($arg);
}

/**
* Возвращает строку без левых и правых пробелов
*
* @param string $arg
* @return string
*/
function TrimLR(string $arg ): string
{
	return trim($arg);
}

/**
* Возвращает строку в количестве $arg2 левых элементов строки $arg1
*
* @param  $arg1 string
* @param  $arg2 int|Number1C - количество элементов строки
* @return string строка  
*/
function Left(string $arg1, $arg2 ): string
{
    if ($arg2 instanceof Number1C) $arg2 = intval($arg2->getValue());
	return mb_substr($arg1, 0, $arg2);
}

/**
* Возвращает строку в количестве $arg2 правых элементов строки $arg1
*
* @param  $arg1 string
* @param  $arg2 int|Number1C количество элементов строки
* @return string строка  
*/
function Right(string $arg1, $arg2 ):string {
    if ($arg2 instanceof Number1C) $arg2 = intval($arg2->getValue());
	return mb_substr($arg1, mb_strlen($arg1)-$arg2, $arg2);
}

/**
* Возвращает строку начиная с $arg2(счет с 1) в количестве $arg3 элементов строки $arg1
*
* @param  $arg1 string
* @param  $arg2 int|Number1C позиция первого элемента начиная с 1
* @param  $arg3 int|Number1C количество элементов
* @return string строка  
*/
function Mid(string $arg1, $arg2, $arg3 ): string
{
    if ($arg2 instanceof Number1C) $arg2 = intval($arg2->getValue());
    if ($arg3 instanceof Number1C) $arg3 = intval($arg3->getValue());
    if($arg2<=0) $arg2 = 1;
	return mb_substr($arg1, $arg2-1, $arg3);
}

//TODO - пока без трех последних параметров
/**
 * Возвращает строку начиная с $arg2(счет с 1) в количестве $arg3 элементов строки $arg1
 *
 * @param  $arg1 string
 * @param  $arg2 string подстрока поиска
 * @param  $arg3 int <> направление поиска
 * @param  $arg4 int начальная позиция поиска (если слева то с 1)
 * @param  $arg5 int количество вхождений в поиск
 * @return int найденная позиция в подстроке начиная с 1, если не нашел то 0
 * @throws Exception
 */
function StrFind(string $arg1, string $arg2, $arg3=0, $arg4=0, $arg5=0): int
{
    if ($arg3 instanceof Number1C) $arg3 = intval($arg3->getValue());
    if ($arg4 instanceof Number1C) $arg4 = intval($arg4->getValue());
    if ($arg5 instanceof Number1C) $arg5 = intval($arg5->getValue());

    if ($arg3 <> 0 || $arg4 <> 0 || $arg5 <> 0) throw new Exception("Еще не реализовано");
	$res = mb_strpos($arg1, $arg2);
	if($res === false) return 0;
	else return $res+1;
}

/**
* Возвращает строку в нижнем регистре
*
* @param string $arg
* @return string строка 
*/
function Lower(string $arg ): string
{
	return mb_strtolower($arg);
}

/**
* Возвращает строку в верхнем регистре
*
* @param string $arg
* @return string строка 
*/
function Upper(string $arg ): string
{
	return mb_strtoupper($arg);
}

/**
* Возвращает строку все первые буквы слов в верхнем регистре
*
* @param string $str
* @return string полученная строка 
*/
function Title(string $str ): string
{
	$len = mb_strlen($str);
	$str = mb_strtoupper(mb_substr($str,0,1)).mb_substr($str,1);
	for ($i=0; $i < $len; $i++) {
		if($i<($len-1)){ 
			if( mb_strpos("\r\n\t ", mb_substr($str,$i,1)) !== false)
				$str = mb_substr($str,0,$i+1).mb_strtoupper(mb_substr($str,$i+1,1)).mb_substr($str,$i+2);
			else $str = mb_substr($str,0,$i+1).mb_strtolower(mb_substr($str,$i+1,1)).mb_substr($str,$i+2);
		}	
	}
	return $str;
}

/**
 * Символ
* Возвращает строку из кода символа
*
* @param  $dec int код символа
* @return string строка 
*/
function Char($dec ): string
{
    if ($dec instanceof Number1C) $dec = intval($dec->getValue());
    return mb_chr($dec);
}

/**
 * КодСимвола
 * Возвращает код символа из буквы строки
 *
 * @param string $str входящая строка
 * @param int $num_letter
 * @return int код символа
 */
function CharCode(string $str, $num_letter=1): int
{
    if ($num_letter instanceof Number1C) $num_letter = intval($num_letter->getValue());
    if($num_letter<1 || $num_letter>mb_strlen($str)) return -1;
    $character = mb_substr($str, $num_letter-1);
    return mb_ord($character);
}

/**
* Проверяет строку на пустоту
*
* @param string $arg входящая строка
* @return bool строка пустая 
*/
function IsBlankString(string $arg): bool
{
	return mb_strlen($arg) == 0;
}

/**
* Возвращает строку замены одного шаблона другим
*
* @param string $arg1 arg1 исходная строка
* @param string $arg2 arg2 строка поиска
* @param string $arg3 arg3 строка замены
* @return string результат замены  
*/
function StrReplace(string $arg1, string $arg2, string $arg3): string
{
	return str_replace($arg2, $arg3, $arg1);
}

/**
* Возвращает код символа из буквы строки
*
* @param string $str str входящая строка
* @return int количество строк в строке
*/
function StrLineCount(string $str): int
{
	return substr_count($str, chr(10)) + 1;
}

/**
* Возвращает num(начиная с 1) строку из общей строки
*
* @param string $str str входящая строка
* @param int|Number1C num номер строки в общей строке
* @return string получившая строка 
*/
function StrGetLine(string $str, $num): string
{
    if ($num instanceof Number1C) $num = intval($num->getValue());
	while($num>1){
		$pos = mb_strpos($str, chr(10));
		$str = mb_substr($str, $pos+1);
		$num--;
	}
	$pos = mb_strpos($str,chr(10));
    if($pos !== false){
		$str = mb_substr($str, 0, $pos);
	} 
	return $str;
}

/**
* Возвращает число вхождений подстроки в строку
*
* @param string $str str входящая строка
* @param string $substr substr подстрока поиска
* @return int число вхождений подстроки в строку 
*/
function StrOccurrenceCount(string $str, string $substr): int
{
	return substr_count($str, $substr);
}

/**
* Возвращает число вхождений подстроки в строку
*
* @param string $str1 str1 первая строка
* @param string $str2 str2 вторая строка
* @return int число результат сравнения строк 
*/
function StrCompare(string $str1, string $str2): int
{
	$res = strcmp($str1,$str2);
	if($res > 0) $res = 1;
	if($res < 0) $res = -1;
	return $res;
}

/**
* Возвращает число вхождений подстроки в строку
*
* @param string $str str первая строка
* @param string $substr substr вторая строка
* @return bool число результат сравнения строк
*/
function StrStartsWith(string $str, string $substr): bool
{
	$res = mb_strpos($str, $substr);
	if($res===0) return true;
	else return false;
}	

/**
* Возвращает число вхождений подстроки в строку
*
* @param string $str str первая строка
* @param string $substr substr вторая строка
* @return bool число результат сравнения строк
*/
function StrEndsWith(string $str, string $substr): bool
{
	$res = mb_strrpos($str, $substr);
	//return $res;
	if($res == (mb_strlen($str)-mb_strlen($substr))) return true;
	else return false;
}

/**
 * Возвращает массив из подстрок общей строки
 *
 * @param string $str str общая строка
 * @param string $split строка разделитель
 * @return Array1C and empty включать в результат пустые строки
 * @throws Exception
 */
function StrSplit(string $str, string $split, $and_empty): Array1C
{
	$array = explode($split, $str);
	if(!$and_empty){
		$array = array_filter($array, function($var){ return (!isset($var) || (strlen($var)==0));} );
	}
	return new Array1C(null, $array);

}

/**
* Возвращает массив из подстрок общей строки
*
* @param  Array1C $array1C массив строк для объединения
* @param string $split строка разделитель
* @return string объединенная получаемая строка   
*/
function StrConcat(Array1C $array1C, string $split): string
{
	return implode($split, $array1C->toArray());
}