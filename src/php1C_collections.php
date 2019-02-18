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
* Подключаем пространство имен
*/
namespace php1C;

/**
* Используем стандартные исключения
*/
use Exception;

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
			case 'UBound(': return $context->UBound();
			case 'Insert(': return $context->Insert($arguments[0], $arguments[1]);
			case 'Add(':    return $context->Add($arguments[0]);
			case 'Count(':  return $context->Count();
			case 'Find(':   return $context->Find($arguments[0]);
			case 'Clear(':  return $context->Clear();
			case 'Get(':    return $context->Get($arguments[0]);	
			case 'Del(':    return $context->Del($arguments[0]);
			case 'Set(':    return $context->Set($arguments[0], $arguments[1]);
			case 'Property(': return $context->Property($arguments[0], $arguments[1]);
			default:
				throw new Exception("Нет обработки функции для объекта коллекции ".$key."");
			}
		}else{
			throw new Exception("Не найдена функция у объекта коллекции  ".$key."");
		}
	}
}


//---------------------------------------------------------------------------------------------------------
function Array1C($args=null){
	return new Array1C($args);
}

/**
* Класс для работы с массивом 1С
*
*/
class Array1C{
	/**
	* @var array внутренее хранение массива
	*/
	private $value; //array of PHP 

	function __construct($counts=null, $copy=null){

		if(is_array($copy)) $this->value = $copy;
		else{	
			$this->value = array();
			$cnt = 0;
			if(is_array($counts) && (count($counts)>0)){
				//if( count($counts) > 1 ) throw new Exception("Многомерные массивы пока не поддерживаются");
				$cnt = $counts[0];
				if( is_numeric($cnt) && $cnt > 0 ){
					for ($i=0; $i < $cnt; $i++) $this->value[i] = null;
				}
			} 
		}
	}

	function __toString(){
		return "Массив";
	}

	function UBound(){
		//$key = array_key_last($this->value); //php7.3
		$key = count($this->value);
		if(is_null($key) ) return -1;
		else return $key-1;  
	}

	function Insert($index, $val){
		//if(!isset($val)) $val = null;
		$this->value[$index] = $val;
	}

	function Add($val){
		//tocheck
		$this->value[] = $val;
		return $this;
	}

	function Count(){
		return count($this->value);
	}

	function Find($val){
		//tocheck
		$key = array_search($val, $this->value);
		if($key === FALSE) return new undefined1C();
		else return $key;
	}

	function Clear(){
		//tocheck
		unset($this->value);
		$this->value = array();
		//return array_filter($this->value, function(){ return FALSE;});
	}

	function Get($index){
		return $this->value[$index];
	}

	function Del($index){
		array_splice($this->value, $index, 1);
	    //unset($this->value[$index]);
	}

	function Set($index, $val){
		$this->value[$index] = $val;
	}
}

//TODO
/**
* Класс для работы со структурой 1С
*
*/
class Structure1C{
	/**
	* @var array внутренее хранение массива
	*/
	private $value; //array of PHP 

	function __construct($count=null){

		if(is_array($count)) $this->value = $count;
		else{	
			$this->value = array();
		}
	}

	function __toString(){
		return "Структура";
	}

	function Insert($index, $val){
		if(isset($val)) $val = new undefined1C;
		$this->value[$index] = $val;
	}

	function Count(){
		return count($this->value);
	}

	function Property($val){
		//tocheck
		$key = array_search($val, $this->value);
		if($key === FALSE) return new undefined1C();
		else return $key;
	}

	function Clear(){
		//tocheck
		unset($this->value);
		$this->value = array();
		//return array_filter($this->value, function(){ return FALSE;});
	}

	function Del($index){
		array_splice($this->value, $index, 1);
	    //unset($this->value[$index]);
	}
}

/**
* Получение массива 1С (пока одномерного)
*
* @param array $cnt аргументы функции в массиве
* @return возвращает новый объект массива 1С
*
*/
function Structure1C($cnt=null){
	if( $cnt === null) return new Structure1C();
}