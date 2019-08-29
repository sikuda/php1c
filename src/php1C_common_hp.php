<?php
/**
* Общий модуль работы с 1С (Высокая точность расчета HP)
* TO DO!!! 
* 
* Только функции с высокой точностью, для полного совмещения с 1С
* @author  sikuda admin@sikuda.ru
*/


/**
* Сложение двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат сложение в зависемости от типа переменных ('string', 'bool, 'Date1C')
*/
function add1C($arg1, $arg2){

	if(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) return bcadd($arg1,$arg2,26);
	}
	elseif(is_string($arg1)) return $arg1 . (string)$arg2;
	elseif(is_object($arg)){
		if( (get_class($arg1) === 'Date1C') && is_numeric($arg2) ) $arg1->add($arg2);
	}
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Вычитание двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат вычитания в зависемости от типа переменных ('string', 'number', 'Date1C', исключение)
*/
function sub1C($arg1, $arg2){

	if(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) return bcsub($arg1,$arg2,26);
	}
	elseif(is_object($arg)){
		if( (get_class($arg1) === 'Date1C') || (is_numeric($arg2) && !is_string($arg2))) return $arg1->sub($arg2);
	}	
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Умножение двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат сложение в зависемости от типа переменных ('number' или исключение)
*/
function mul1C($arg1, $arg2){

	if(is_numeric($arg1) && is_numeric($arg2) ) return bcmul($arg1,$arg2,26);
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Деление двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат сложение в зависемости от типа переменных ('number' или исключение)
*/
function div1C($arg1, $arg2){

	if(is_numeric($arg1) && is_numeric($arg2) ){
		if( $arg2 == 0) throw new Exception("Деление на 0");
		else  return bcdiv($arg1,$arg2,26);
	}	
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Операция преобразования bool 0 или 1
* @param any $arg
* @return string Результат bool для bc операций 
*/
function tran_bool($arg){
	if($arg === true) return "1";
	else return "0";
}

/**
* Операция ИЛИ в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции ИЛИ 
*/
function or1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(isset($arg1) && !is_string($arg1) && !is_object($arg1)) return $arg1 || $arg2;
	throw new Exception("Преобразование значения к типу Булево не может быть выполнено");
}

/**
* Операция И в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции И 
*/
function and1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(isset($arg1) && !is_string($arg1) && !is_object($arg1)) return $arg1 && $arg2;
	throw new Exception("Преобразование значения к типу Булево не может быть выполнено");
}

/**
* Операция Меньше в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Меньше 
*/
function less1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 < $arg2;
	throw new Exception("Операции сравнения на больше-меньше допустимы только для значений совпадающих примитивных типов (Булево, Число, Строка, Дата)");
}

/**
* Операция Меньше или равно в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Меньше или равно 
*/
function lessequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 <= $arg2;
	throw new Exception("Операции сравнения на меньше или равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Больше в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Больше 
*/
function more1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 > $arg2;
	throw new Exception("Операции сравнения на больше допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Больше или равно в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Больше или равно 
*/
function morequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 >= $arg2;
	throw new Exception("Операции сравнения на больше или равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Равно в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Равно 
*/
function equal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 === $arg2;
	throw new Exception("Операции сравнения равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Равно в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат операции Равно 
*/
function notequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'Date1C')) return $arg1 !== $arg2;
	throw new Exception("Операции сравнения равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

