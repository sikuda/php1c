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
	return  array('Цел(','Окр(', 'Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt('  );
}

/**
* Массив названий английских функций для работы с числами. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с числами.
*/   
function functionsPHP_Number(){
	return array('Int(','Round(','Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(');
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
			case 'Round(': return Round1C($arguments[0],$arguments[1]);
			case 'Log(': return Log1C($arguments[0]);
			throw new Exception("Неизвестная функция работы с числами ".$key."");
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
function Round1C($val, $pr){
	return round($val, $pr);
}

/**
* Возращает целое число от вещественного числа
*
* @param  float $val число для получения целого   
* @return int целое число - результат  
*/
function Log1C($val){
	return log($val);
}