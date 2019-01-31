<?php
/**
* Общий модуль работы с 1С
* 
* Модуль для работы 1С
* 
* @author  sikuda admin@sikuda.ru
*/
require_once('php1C_date.php');
require_once('php1C_file.php');
require_once('php1C_collections.php');

/**
* Массив названий русских функций для общей работы с 1С
* @return string[] Массив названий функций общей работы с 1С.
*/
function functions_Com(){
	return  array('Сообщить(', 'ВГраница(', 'Вставить(', 'Добавить(', 'Количество(', 'Найти(', 'Очистить(','Получить(', 'Удалить(', 'Установить(');
}

/**
* Массив названий английских функций для общей работы с 1С. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
function functionsPHP_Com(){
	return  array('Message(',  'UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',     'Set(');
}

/**
* Выводит данные в представлении 1С (на русском)
* @param any $arg
* @return string Возвращем значение как в 1С ('Да', 'Нет', Дату в формате 1С dd.mm.yyyy, 'Неопределено' и другое
*/  
function toString1C($arg){
	if(!isset($arg)) return "Неопределено";
	if(is_bool($arg)){
		if($arg === true ) return "Да";
		else return "Нет";
	}
	return (string)$arg; 
}

/**
* Сложение двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат сложение в зависемости от типа переменных ('string', 'bool, 'Date1C')
*/
function add1C($arg1, $arg2){

	if (is_string($arg1)) return $arg1 . (string)$arg2;
	elseif(is_bool($arg1) || is_numeric($arg1)){
		if(is_bool($arg2) || is_numeric($arg2)) return $arg1+$arg2;
	}
	elseif(is_object($arg1)){
		if( (get_class($arg1) === 'Date1C') && is_numeric($arg2) && !is_string($arg2) ) return $arg1->add($arg2);
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
		if(is_bool($arg2) || is_numeric($arg2)) return $arg1-$arg2;
	}
	elseif(is_object($arg)){
		if( (get_class($arg1) === 'Date1C') && is_numeric($arg2) && !is_string($arg2) ) return $arg1->sub($arg2);
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

	if(is_numeric($arg1) && !is_string($arg1) && is_numeric($arg2) && !is_string($arg2) ) return $arg1*$arg2;
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Деление двух переменных в 1С
* @param any $arg1
* @param any $arg2
* @return string Результат сложение в зависемости от типа переменных ('number' или исключение)
*/
function div1C($arg1, $arg2){

	if(is_numeric($arg1) && !is_string($arg1) && is_numeric($arg2) && !is_string($arg2) ) return $arg1/$arg2;
	throw new Exception("Преобразование значения к типу Число не может быть выполнено");
}

/**
* Операция преобразования bool d 0 или 1
* @param any $arg1
* @param any $arg2
* @return string Результат операции ИЛИ 
*/
function tran_bool($arg){
	if($arg === true) return (float)1;
	else return (float)0;
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
		case 'MESSAGE(':
			return Message($arguments[0], $arguments[1]);
			break;
		default:
			throw new Exception("Неизвестная общая функция ".$key."");
		}	
	}
	else{
		if( method_exists($context, substr($key, 0, -1) )){ 
			switch($key){
			case 'UBOUND(': return $context->UBOUND();
			case 'INSERT(': return $context->INSERT($arguments[0], $arguments[1]);
			case 'ADD(':    return $context->ADD($arguments[0]);
			case 'COUNT(':  return $context->COUNT();
			case 'FIND(':   return $context->FIND($arguments[0]);
			case 'CLEAR(':  return $context->CLEAR();
			case 'GET(':    return $context->GET($arguments[0]);	
			case 'DEL(':    return $context->DEL($arguments[0]);
			case 'SET(':    return $context->SET($arguments[0], $arguments[1]);
			default:
				throw new Exception("Нет обработки функции для объекта  ".$key."");
			}
		}else{
			throw new Exception("Не найдена функция у объекта  ".$key."");
		}
	}
}

/**
* Выводит сообщение через echo
*
* @param string $mess
* @param integer $status (пока не используется)
*/
function Message($mess='', $status=0){
	echo $mess;
}

?>

