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

namespace php1C;

use Exception;
require_once('php1C_common.php');

/**
* Массив названий русских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
function typesRUS_Collection(){
	return array('Массив','Структура','ТаблицаЗначений');
}

/**
* Массив названий английских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
function typesENG_Collection(){
	return array('Array','Structure','ValueTable');
}

/**
* Массив названий типов для работы с коллекциями переименовании
* @return array of string - Массив названий функций работы с коллекциями или пустые.
*/
function typesPHP_Collection(){
	return array('Array1C','Structure1C','ValueTable');
}

/**
* Массив названий русских функций для работы с датой
* @return string[] Массив названий функций работы с датой.
*/
function functions_Collections(){
	return  array('ВГраница(', 'Вставить(', 'Добавить(', 'Количество(', 'Найти(', 'Очистить(','Получить(', 'Удалить(','Установить(','Свойство(','ЗагрузитьКолонку(','ВыгрузитьКолонку(', 'ЗаполнитьЗначения(','Индекс(', 'Итог(', 'Найти(','НайтиСтроки(','Очистить(','Свернуть(', 'Сдвинуть(','Копировать(', 'КопироватьКолонки(','Сортировать(','Удалить(');
}
/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
function functionsPHP_Collections(){
	return  array('UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',    'Set(',       'Property(','LoadColumn(',     'UnloadColumn(',      'FillValues(',      'IndexOf(','Total(','Find(','FindRows(',    'Clear(',   'GroupBy(',  'Move(',    'Copy(',       'CopyColumns(',          'Sort(',       'Del(');
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
		case 'ValueTable': return ValueTable($arguments);
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
			case 'Add(':    if(isset($arguments[0])) return $context->Add($arguments[0]);
							else return $context->Add();
			case 'Count(':  return $context->Count();
			case 'Find(':   return $context->Find($arguments[0]);
			case 'Clear(':  return $context->Clear();
			case 'Get(':    return $context->Get($arguments[0]);	
			case 'Del(':    return $context->Del($arguments[0]);
			case 'Set(':    return $context->Set($arguments[0], $arguments[1]);
			case 'Property(': return $context->Property($arguments[0], $arguments[1]);
			case 'LoadColumn(': return $context->LoadColumn($arguments[0], $arguments[1]);
			case 'UnloadColumn(': return $context->UnloadColumn($arguments[0]);
			case 'FillValues(': return $context->FillValues($arguments[0], $arguments[1]);
			case 'IndexOf(': return $context->IndexOf($arguments[0]);
			case 'Total(': return $context->Total($arguments[0]);
			case 'Find(': return $context->Total($arguments[0],$arguments[1]);
			case 'FindRows(': return $context->FindRows($arguments[0]);
			case 'Clear(': return $context->Clear();
			case 'GroupBy(': return $context->GroupBy($arguments[0], $arguments[1]);
			case 'Move(': return $context->Move($arguments[0], $arguments[1]);
			case 'Copy(': return $context->Copy($arguments[0], $arguments[1]);
			case 'CopyColumns(': return $context->CopyColumns($arguments[0]);
			case 'Sort(': return $context->Sort($arguments[0], $arguments[1]);
			case 'Del(': return $context->Del($arguments[0]);
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

//------------------------------------------------------------------------------------------

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

//----------------------------------------------------------------------------------------------

/**
* Получение ТаблицыЗначений
*
* @param array $args аргументы функции в массиве
* @return возвращает новый объект ТаблицаЗначений1С
*
*/
function ValueTable($args=null){
	return new ValueTable($args);
}

/**
* Класс для работы с таблицей значений 1С8
*
*/
class ValueTable{
	/**
	* @var array внутренее хранение таблицы значений в виде массива колонок (каждая колонка это массив)
	*/
	private $rows;   //array of ValueTableRow
	public $COLUMNS; //ValueTableColumnCollection - collection of ValueTableColumn
	public $КОЛОНКИ;
	public $INDEXES; //CollectionIndexes коллекция из CollectionIndex
	public $ИНДЕКСЫ;

	function __construct($args=null,$copy=null){

		if(is_array($copy)) $this->value = $copy;
		else{	
			$this->rows = array();
			$this->COLUMNS = new ValueTableColumnCollection($this);
			$this->КОЛОНКИ = &$this->COLUMNS;
			$this->INDEXES = new CollectionIndexes($this);
			$this->ИНДЕКСЫ = &$this->INDEXES;
		}
	}

	function __toString(){
		return "ТаблицаЗначений";
	}

	function Add(){
		$row = new ValueTableRow($this);
		$this->rows[] = $row;
		return $row;
	}

	function Insert($index){
		if(is_int($index)){
			$row = new ValueTableRow($this);
			$this->rows[$index] = $row;
			return $row;
		}
		else  new Exception("Индекс задан неверно");	
	}

	//Выгрузка колонки в Array1C
	function UnloadColumn($col){
		if(isset($col)){
			if(is_int($col)){
				$col = $this->COLUMNS->cols[$col];
			}elseif (is_string($col)) {
				$col = $this->COLUMNS->cols[strtoupper($col)];
			}
			if(is_object($col) && get_class($col) === 'php1C\ValueTableColumn'){
				$array = new Array1C;
				foreach ($this->rows as $key => $value) {
					$val = $value->Get($col->NAME);
					$array->Add($val);
				}
				return $array;
			}
		}
		throw new Exception("Не найдена колонка для выгрузки ".$col);
	}

	//Загрузка колонки из Array1C
	function LoadColumn($arr, $col){
		if(!is_object($arr) || get_class($arr) !== 'php1C\Array1C')
			throw new Exception("Первый аргумент должен быть массивом ".$arr);
		if(isset($col)){
			if(is_int($col)){
				$col = $this->COLUMNS->cols[$col];
			}elseif (is_string($col)) {
				$col = $this->COLUMNS->cols[strtoupper($col)];
			}	
			if(is_object($col) && get_class($col) === 'php1C\ValueTableColumn'){
				$k = 0;
				foreach ($this->rows as $key => $value) {
					$value->Set($col->NAME, $arr[$k]);
					$k++;
				}
				return;
			}
		}
		throw new Exception("Не найдена колонка для загрузки ".$col);
	}

	//Заполним имя всех столбцов
	function GetAllColumns(){
		$strcols = '';
		foreach ($this->COLUMNS->cols as $val) {
			$strcols .= $val->NAME.',';
		}	
		return $strcols;	
	}

	//Заполнить значениями таблицу
	function FillValues($value, $strcols=null){
		if(!isset($strcols)) $strcols = $this->GetAllColumns(); 
		$keys = explode(',',$strcols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			foreach ($this->rows as $val) {
				$val->Set($col, $value);
			}
		}
	}

	function IndexOf($row){
		$key = array_search( $row, $this->rows);
		if( $key === FALSE ) $key = -1;
		return $key;
		//throw new Exception("Пока нет реализации Индекс ");	
	}

	function Total($col){
		$col = strtoupper($col);
		$sum = 0;
		foreach ($this->rows as $key => $value) {
			$val = $value->Get($col);
			if(is_numeric($val)){
				$sum += toNumber1C($val);
			}	
		}
		return $sum;
	}

	//Возвратить количество строк
	function Count(){
		return count($this->rows);
	}

	function Find($value, $strcols=null){
		if(!isset($strcols)) $strcols = $this->GetAllColumns();
		$keys = explode(',',$strcols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			foreach ($this->rows as $row) {
				$val = $row->Get($col);
				if( $val === $value ) return $row;
			}
		}
		return null;
		//throw new Exception("Пока нет реализации Найти");	
	}

	function FindRows($filter){
		if(!is_object($arr) || get_class($arr) !== 'php1C\Array1C')
		$strcols = $this->GetAllColumns();
		$keys = explode(',',$strcols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			foreach ($this->rows as $row) {
				// $val = $row->Get($col);
				// if( $val === $value ) return $row;
			}
		}	
		throw new Exception("Аргумент функции должен быть структурой ".$filter);
	}

	function Clear(){
		$this->rows->setValueTable(null);
		unset($this->rows);
		$this->rows = array();
	}

	//Для получения данных через точку
	function Get($key){
		if(is_string($key)){
			$key = strtoupper($key);
			if($key === 'КОЛОНКИ' || $key === 'COLUMNS'){
				return $this->COLUMNS;
			}	
		}
		if(is_numeric($key)){
		 	return $this->rows[$key];
		}
		throw new Exception("Не найден ключ для строки ТаблицыЗначений ".$key);
	}

	//Для установки данных через точку
	function Set($key, $val=null){
		if(is_string($key)){
			$key = strtoupper($key);
			if(($key === 'КОЛОНКИ' || $key === 'COLUMNS') && (is_object($val) && get_class($val) === 'ValueTableColumnCollection')){
				$this->COLUMNS = $val;
				$this->COLUMNS->setValueTable($this);
			}	
		}
		if(is_numeric($key) && (is_object($val) && get_class($val) === 'ValueTableRow')){
		 	$this->rows[$key] = $val;
		}
		throw new Exception("Не найден имя столба ТаблицыЗначений ".$key);
	}

	function GroupBy($colgr, $colsum){
		throw new Exception("Пока нет реализации Свернуть");	
	}

	function Move($str, $offset){
		throw new Exception("Пока нет реализации Сдвинуть");	
	}

	function Copy($rows, $cols){
		throw new Exception("Пока нет реализации Скопировать");	
	}

	function CopyColumns($cols){
		throw new Exception("Пока нет реализации СкопироватьКолонки");	
	}

	function Sort($cols, $cmp_object=null){
		throw new Exception("Пока нет реализации Сортировать");	
	}

	function Del($row){
		if(is_int($row)){
			$row = $this->rows[$row];
		}elseif(!is_object($row) && get_class($row) !== 'php1C\ValueTableRow'){
			throw new Exception("Параметр может быть либо строкой либо числом");
		}		
		$row->setValueTable(null);
		unset($row);
	}
}

/**
* Класс колекции колонок таблицы значений 1С
*
*/
class ValueTableColumnCollection{

	/**
	* @var array коллекция ValueTableColumn
	*/
	private $ValueTable;
	public $cols; 

	function __construct($parent){
		$this->ValueTable = &$parent;
		$this->cols = array();
	}

	function setValueTable($parent){
		$this->ValueTable = &$parent;
	}

	function __toString(){
		return "КоллекцияКолонокТаблицыЗначений";
	}

	function Add($key=null){
		if(!isset($key)) $key = ''; //пустые имена колонок в 1С допустимы.
		if(is_string($key)){
			$key = strtoupper($key);
			$this->cols[$key] = new ValueTableColumn($key);
		}
		else  new Exception("Имя колонки должно быть строкой");	
	}

	function Count(){
		return count($this->cols);
	}
}

/**
* Класс колонки таблицы значений 1С
*
*/
class ValueTableColumn{

	/**
	* @var array коллекция значений в колонке
	*/
	public $NAME; 
	public $ИМЯ;

	function __construct($val=null){
		$this->NAME = $val;
		$this->ИМЯ  = &$this->NAME;
	}

	function __toString(){
		return "КолонкаТаблицыЗначений";
	}

}

/**
* Класс строки для таблицы значений 1С
*
*/
class ValueTableRow{

	/**
	* @var array коллекция значений в строке
	*/
	private $ValueTable;
	private $row; 

	function __construct($args=null){
		if(isset($args)) $this->ValueTable = &$args;
		$this->row = array();
	}

	function __toString(){
		return "СтрокаТаблицыЗначений";
	}

	//Для получения данных через точку
	function Get($key){
		if(is_string($key)){
			$array = $this->ValueTable->COLUMNS->cols;
			if(array_key_exists($key, $array)){
				$key = strtoupper($key);
				return $this->row[$key];
			} 	
		}
		throw new Exception("Поле объекта не обнаружено у строки таблицы ".$key);
	}

	//Для установки данных через точку
	function Set($key, $value=null){
		if(is_string($key)){
			$key = strtoupper($key);
			$this->row[$key] = $value;	
		}
		else throw new Exception("Нет такой колонки в таблице");
	}

}

class CollectionIndexes{
	/**
	* @var array коллекция значений в строке
	*/
	private $ValueTable;
	private $indexs;

	function __construct($parent){
	 	$this->ValueTable = &$parent;
	 	$this->indexs = array();
	}

	function __toString(){
	 	return "ИндексыКоллекции";
	}

	function Add($name){
		//if(!isset($key)) $key = ''; //пустые имена колонок в 1С допустимы.
		if(is_string($key)){
			$key = strtoupper($key);
			$this->cols[$key] = new CollectionIndex($name);
		}
		else  new Exception("Имя колонки должно быть строкой");	
	}

	function Count(){
		return count($this->indexs);
	}

	function Clear(){
		//tocheck
		unset($this->indexs);
		$this->indexs = array();
	}

	function Del($key){
		$key = strtoupper($key);
		unset($this->indexs[$key]);
	}
}

class CollectionIndex{
	/**
	* @var array коллекция значений в строке
	*/
	private $name;
	function __construct($col){
	// 	if(isset($args)) $this->ValueTable = &$args;
	 	$this->name = $col;
	}

	function __toString(){
	 	return "ИндексКоллекции";
	}	
}
