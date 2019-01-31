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
* Класс для работы с массивом 1С
*
*/
class Array1C{
	/**
	* @var array внутренее хранение массива
	*/
	private $value; 

	function __construct(){
		$this->value = array();
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

function Array1C(){
	return new Array1C();
}