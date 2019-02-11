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
	return  array('СтрДлина(','СокрЛ(','СокрП(','СокрЛП(','Лев(','Прав(','Сред(','СтрНайти(','НРег(','ВРег(','Символ(','КодСимвола(');
}

//Лев(Прав,Сред(,СтрНайти(,ВРег(,НРег(,ТРег(,Символ(, КодСимвола(, ПустаяСтрока(, СтрЗаменить(,СтрЧислоСтрок(, СтрПолучитьСтроку(, СтрЧислоВхождений(, СтрСравнить(, СтрНачинаетсяС(, СтрЗаканчиваетсяНа(, СтрРазделитель(, СтрСоединить()

/**
* Массив названий английских функций для работы со строками. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы со строками.
*/   
function functionsPHP_String(){
	return array('StrLen(',  'TrimL(','TrimR(','TrimLR(','Left(','Right(','Mid(','StrFind(','Lower(','Upper(','Char(','CharCode(');
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
		case 'TrimL(': return TrimL($arguments[0]);
		case 'TrimR(': return TrimR($arguments[0]);
		case 'TrimLR(': return TrimLR($arguments[0]);
		case 'Left(': return Left($arguments[0], $arguments[1]);
		case 'Right(': return Right($arguments[0], $arguments[1]);
		case 'Mid(': return Mid($arguments[0], $arguments[1], $arguments[2]);
		case 'StrFind(': return StrFind($arguments[0], $arguments[1], $arguments[2],$arguments[3],$arguments[4]);
		case 'Lower(': return Lower($arguments[0]);
		case 'Upper(': return Upper($arguments[0]);
		case 'Char(': return Char($arguments[0]);
		case 'CharCode(': return CharCode($arguments[0]);
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

/**
* Возращает строку начиная с $arg2(счет с 1) в количестве $arg3 элементов строки $arg1
* //TODO - пока без трех последних параметров
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


//FOR TESTING
/**
* Возращает строку из кода символа
*
* @param  int код символа 
* @return string строка 
*/
function Char( $arg ){
	return chr($arg);
}

/**
* Возращает код символа из буквы строки
*
* @param  string символ 
* @return int код символа 
*/
function CharCode( $arg ){
	return ord($arg);
}