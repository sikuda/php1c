<?php
/**
* Общий модуль работы с 1С
* 
* Модуль для работы 1С
* 
* @author  sikuda admin@sikuda.ru
*/

/**
* Подключаем пространство имен
*/
namespace php1C;
use Exception;

//Подключаем язык
require_once('php1C_settings.php');
if (LANGUAGE == 'en') {
	require_once('lang/en.php');   
}
else{
	require_once('lang/ru.php');
}


/*
*  Подключаем все модули для 1С
*/
require_once('php1C_number.php');
require_once('php1C_string.php');
require_once('php1C_date.php');
require_once('php1C_collections.php');
require_once('php1C_file.php');

/**
* Массив функций PHP для общей работы с 1С. Соответстует элементам в язоковых файлах.
* @return string[] Массив названий общих функций.
*/   
function functionsPHP_Com(){
	return  array('Message(',  'Find(',  'ValueIsFilled(',    'Type(','TypeOf(');
}

/**
* Вызывает общие функции и функции объектов 1С 
*
* @param object $context объект для вызова функции или null
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callCommonFunction($context=null, $key, $arguments){
	if($context === null){
		switch($key){
		case 'Message(':
			if(isset($arguments[2])) throw new Exception("Ожидается ) ");
			return Message($arguments[0], $arguments[1]);
		case 'Find(':
			if(isset($arguments[2])) throw new Exception("Ожидается ) ");
			return Find($arguments[0], $arguments[1]);
		case 'ValueIsFilled(':
			if(isset($arguments[1])) throw new Exception("Ожидается ) ");
			return ValueIsFilled($arguments[0]);
		case 'Type(':
			if(isset($arguments[1])) throw new Exception("Ожидается ) ");
			return Type($arguments[0]);
		case 'TypeOf(':
			if(isset($arguments[1])) throw new Exception("Ожидается ) ");
			return TypeOf($arguments[0]);
		default:
			throw new Exception("Неизвестная общая функция ".$key."");
		}	
	}
	else{
		if( method_exists($context, substr($key, 0, -1) )){ 
			switch($key){
			case 'Find(':   return $context->Find($arguments[0]);
			default:
				throw new Exception("Нет обработки общей функции для объекта  ".$key."");
			}
		}else{
			throw new Exception("Не найдена общая функция у объекта  ".$key."");
		}
	}
}

/**
* Выводит данные в представлении 1С (на русском)
* @param any $arg
* @return string Возвращем значение как в 1С ('Да', 'Нет', Дату в формате 1С dd.mm.yyyy, 'Неопределено' и другое
*/  
function toString1C($arg){
	if(!isset($arg)) return php1C_Undefined; //"Неопределено";
	if(is_bool($arg)){
		if($arg === true ) return php1C_Bool[0]; //"Да";
		else return php1C_Bool[1]; //"Нет";
	}
	return strval($arg); 
}

/**
* Преобразует аргумент в число 
* @param stirng $arg число как строка
* @return (string or float) Возвращем значение числа как в 1С (string - для чисел повышенной точности, float - если повышенная точность не важна
*/  
function toNumber1C($arg){
	return floatval($arg);
}

/**
* Сложение двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return any Результат сложение в зависемости от типа переменных (string, bool, Date1C)
*/
function add1C($arg1, $arg2){

	if (is_string($arg1)) return $arg1 . (string)$arg2;
	elseif(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) return $arg1+$arg2;
	}
	elseif(is_object($arg1)){
		if( (get_class($arg1) === 'php1C\Date1C') && is_numeric($arg2) && !is_string($arg2) ) return $arg1->add($arg2);
	}
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Вычитание двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return float, Date1C Результат вычитания в зависемости от типа переменных (float, Date1C, исключение)
*/
function sub1C($arg1, $arg2){

	if(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) return $arg1-$arg2;
	}
	elseif(is_object($arg1)){
		if( (get_class($arg1) === 'php1C\Date1C') && is_numeric($arg2) && !is_string($arg2) ) return $arg1->sub($arg2);
	}	
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Умножение двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return float Результат сложение в зависемости от типа переменных (float или исключение)
*/
function mul1C($arg1, $arg2){

	if((is_bool($arg1) || is_numeric($arg1)) && !is_string($arg1) && (is_bool($arg2) || is_numeric($arg2)) && !is_string($arg2) ) return $arg1*$arg2;
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Деление двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return float Результат сложение в зависемости от типа переменных (float или исключение)
*/
function div1C($arg1, $arg2){

	if((is_bool($arg1) || is_numeric($arg1)) && !is_string($arg1) && (is_bool($arg2) || is_numeric($arg2)) && !is_string($arg2) ){
		if( $arg2 == 0) throw new Exception("Деление на 0");
		else return $arg1/$arg2;	
	} 
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Операция преобразования bool в 0 или 1
* @param any $arg1
* @param any $arg2
* @return float преобразование bool в 0 или 1
*/
function tran_bool($arg){
	if($arg === true) return (float)1;
	else return (float)0;
}

/**
* Операция ИЛИ в 1С
* @param any $arg1
* @param any $arg2
* @return bool Результат операции ИЛИ 
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
* @return bool Результат операции И 
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
* @return bool Результат операции Меньше 
*/
function less1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 < $arg2;
	throw new Exception("Операции сравнения на больше-меньше допустимы только для значений совпадающих примитивных типов (Булево, Число, Строка, Дата)");
}

/**
* Операция Меньше или равно в 1С
* @param any $arg1
* @param any $arg2
* @return float Результат операции Меньше или равно 
*/
function lessequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 <= $arg2;
	throw new Exception("Операции сравнения на меньше или равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Больше в 1С
* @param any $arg1
* @param any $arg2
* @return bool Результат операции Больше 
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
* @return bool Результат операции Больше или равно 
*/
function morequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 >= $arg2;
	throw new Exception("Операции сравнения на больше или равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Равно в 1С
* @param any $arg1
* @param any $arg2
* @return bool Результат операции Равно 
*/
function equal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 === $arg2;
	throw new Exception("Операции сравнения равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

/**
* Операция Равно в 1С
* @param any $arg1
* @param any $arg2
* @return bool Результат операции Равно 
*/
function notequal1C($arg1, $arg2){
	if(is_bool($arg1)) $arg1 = tran_bool($arg1);
	if(is_bool($arg2)) $arg2 = tran_bool($arg2);
	if(is_numeric($arg1) || is_string($arg1) || (is_object($arg1) && get_class($arg1) === 'php1C\Date1C')) return $arg1 !== $arg2;
	throw new Exception("Операции сравнения равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)");
}

// ---------------------- Общие функции -----------------------------

/**
* Выводит сообщение через echo
*
* @param string $mess
* @param integer $status (пока не используется)
*/
function Message($mess='', $status=0){
	echo toString1C($mess);
}

/**
* Находит строку в строке
* Хотя 1С считает эту функцию устаревшей, мы ее сделаем
*
* @param string $str строка в которой ищут
* @param string $substr строка поиска(которую ищут)
* @return возвращает позицию найденной строки начиная с 1. Если ничего не нашло возвратит 0
*/
function Find($str='', $substr=''){
	$res = strpos($str, $substr);
	if($res === false) return 0;
	else return $res+1;
}

/**
* Проверяет заполненность параметра по 1C
*
* @param string $str строка в которой ищут
* @param string $substr строка поиска(которую ищут)
* @return Истина если значение заполнено иначе ложь
*/
function ValueIsFilled($val){
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
	/**
	* @var string строка описание типа
	*/
	private $val; 

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
* @param  any $val объект для получения типа
* @return object Type1C 
*/
function TypeOf($val){

	$str = "Неопределено";
	if(is_bool($val)) $str = "Булево"; 
	elseif(is_numeric($val)) $str = "Число"; 
	elseif(is_string($val)) $str = "Строка"; 
	elseif(is_object($val)) $str = $val->__toString();
	return new Type1C($str);

}

/**
* Тип - Возвращает тип 1С по его описанию в строке
*
* @param string $str строка описание типа
* @return object Type1C 
*/
function Type($str){
	return new Type1C($str);
}	

