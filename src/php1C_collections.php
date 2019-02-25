<?php
/**
* Модуль работы c универсальными коллекциями значений 1С
* 
* Модуль для работы с массивами, структурами  в 1С и функциями для работы с ними
* для будущего (соответствиями, списком значений, таблица значений)
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
	return array('Массив','Структура');
}

/**
* Массив названий английских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
function typesENG_Collection(){
	return array('Array','Structure');
}

/**
* Массив названий типов для работы с коллекциями переименовании
* @return array of string - Массив названий функций работы с коллекциями или пустые.
*/
function typesPHP_Collection(){
	return array('Array1C','Structure1C');
}

/**
* Массив названий русских функций для работы с датой
* @return string[] Массив названий функций работы с датой.
*/
function functions_Collections(){
	return  array('ВГраница(', 'Вставить(', 'Добавить(', 'Количество(', 'Найти(', 'Очистить(','Получить(', 'Удалить(', 'Установить(','Свойство(');
}
/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
function functionsPHP_Collections(){
	return  array('UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',     'Set(',       'Property(');
}

/**
* Вызывает функции и функции объектов 1С работы с коллекциями
*
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callCollectionType($key, $arguments){
	switch ($key) {
		case 'Array1C': return Array1C($arguments);
		case 'Structure1C': return Structure1C($arguments);
		default:
			throw new Exception('Пока тип в коллекциях не определен '.$key);
			break;
	}
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
		//case 'func(':
		//	break;
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
		//array_splice($this->value, $index, 1);
	    unset($this->value[$index]);
	}

	function Set($index, $val){
		$this->value[$index] = $val;
	}
}

/**
* Получение структуры 1С 
*
* @param array $cnt аргументы функции в массиве
* @return возвращает новый объект массива 1С
*
*/
function Structure1C($args=null){
	return new Structure1C($args);
}

/**
* Класс для работы со структурой 1С
*/
class Structure1C{
	/**
	* @var array внутренее хранение массива
	*/
	private $value; //array of PHP 

	function __construct($args=null,$copy=null){

		if(is_array($copy)) $this->value = $copy;
		else{	
			$this->value = array();
			if( (count($args) > 0) && is_string($args[0])){
				$keys = explode(',',$args[0]);
				for ($i=0; $i < count($keys); $i++) {
					$k = strtoupper(trim ($keys[$i]));
					if(!isset($args[$i+1])) $this->value[$k] = null;
					else $this->value[$k] = $args[$i+1];
				}
			}
		}
	}

	function __toString(){
		return "Структура";
	}

	function Insert($key, $val=null){
		$this->value[strtoupper($key)] = $val;
	}

	function Count(){
		return count($this->value);
	}

	function Property($key){
		$key = strtoupper($key);
		return array_key_exists($key, $this->value);
	}

	function Clear(){
		//tocheck
		unset($this->value);
		$this->value = array();
	}

	function Del($key){
		$key = strtoupper($key);
		unset($this->value[$key]);
	}

	//Для получения данных через точку
	function Get($key){
		$key = strtoupper($key);
		return $this->value[$key];
	}

	//Для установки данных через точку
	function Set($key, $val=null){
		$key = strtoupper($key);
		if(array_key_exists($key, $this->value)) $this->value[$key] = $val;
		else throw new Exception("Не найден ключ структуры ".$key);
	}	
}

