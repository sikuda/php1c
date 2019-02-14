<?php
/**
* Модуль работы c универсальными коллекциями значений 1С
* 
* Модуль для работы с массивами,  в 1С и функциями для работы с ними
* для будущего (структурами, соответствиями, списком значений, таблица значений)
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/

/**
* Массив названий русских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
function typesRUS_Collection(){
	return array('Массив');
}

/**
* Массив названий английских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
function typesENG_Collection(){
	return array('Array');
}

/**
* Массив названий типов для работы с коллекциями переименовании
* @return array of string - Массив названий функций работы с коллекциями или пустые.
*/
function typesPHP_Collection(){
	return array('Array1C');
}

/**
* Массив названий русских функций для работы с датой
* @return string[] Массив названий функций работы с датой.
*/
function functions_Collections(){
	return  array('ВГраница(', 'Вставить(', 'Добавить(', 'Количество(', 'Найти(', 'Очистить(','Получить(', 'Удалить(', 'Установить(');
}
/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
function functionsPHP_Collections(){
	return  array('UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',     'Set(');
}

/**
* Вызывает функции и функции объектов 1С работы с коллекциями
*
* @param object $context объект для вызова функции или null
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callCollectionFunction($context=null, $key, $arguments){
		if($context === null){
		switch($key){
		// case 'Message(':
		// 	if(isset($arguments[2])) throw new Exception("Ожидается ) ");
		// 	return Message($arguments[0], $arguments[1]);
		default:
			throw new Exception("Неизвестная функция работы с коллекциями ".$key."");
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
				throw new Exception("Нет обработки функции для объекта коллекции ".$key."");
			}
		}else{
			throw new Exception("Не найдена функция у объекта коллекции  ".$key."");
		}
	}
}


//---------------------------------------------------------------------------------------------------------

/**
* Класс для работы с массивом 1С
*
*/
class Array1C{
	/**
	* @var array внутренее хранение массива
	*/
	private $value; 

	function __construct($count=null){
		if(is_array($count)) $this->value = $count;
		else{	
			$this->value = array();
			if( $count > 0 ){
				for ($i=0; $i < $count; $i++) $this->value[i] = null;
			}	
		}
	}

	function __toString(){
		return "Массив";
	}

	function UBOUND(){
		//tocheck
		$key = array_key_last($this->value);
		if( is_null($key) ) return -1;
		else return $key; 
	}

	function INSERT($index, $val){
		if(isset($val)) $val = new undefined1C;
		$this->value[$index] = $val;
	}

	function ADD($val){
		//tocheck
		$this->value[] = $val;
		return $this;
	}

	function COUNT(){
		return count($this->value);
	}

	function FIND($val){
		//tocheck
		$key = array_search($val, $this->value);
		if($key === FALSE) return new undefined1C();
		else return $key;
	}

	function CLEAR(){
		//tocheck
		unset($this->value);
		$this->value = array();
		//return array_filter($this->value, function(){ return FALSE;});
	}

	function GET($index){
		return $this->value[$index];
	}

	function DEL($index){
		array_splice($this->value, $index, 1);
	    //unset($this->value[$index]);
	}

	function SET($index, $val){
		$this->value[$index] = $val;
	}
}

/**
* Получение массива 1С (пока одномерного)
*
* @param array $cnt аргументы функции в массиве
* @return возвращает новый объект массива 1С
*
*/
function Array1C($cnt=null){
	if( $cnt === null) return new Array1C();
	if( count($cnt) > 1 ) throw new Exception("Многомерные массивы пока не поддерживаются"); 
	else return new Array1C($cnt[0]);
}