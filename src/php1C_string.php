<?php
/**
* Модуль работы со строками 1С
* 
* Модуль функций для со строками 1С. (Устаревшая Найти в Общем модуле)
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/

/**
* Массив названий русских функций для работы со строками
* @return string[] Массив названий функций работы со строками.
*/
function functions_String(){
	return  array('СтрДлина(');
}
/**
* Массив названий английских функций для работы со строками. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы со строками.
*/   
function functionsPHP_String(){
	return  array('StrLen(');
}	

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