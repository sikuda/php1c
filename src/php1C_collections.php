<?php
/**
* Модуль работы с универсальными коллекциями значений 1С
* 
* Модуль для работы с массивами, структурами в 1С и функциями для работы с ними
* (соответствиями, списком значений, таблица значений)
* 
* @author  sikuda@yandex.ru
* @version 0.3
*/
namespace php1C;
use Exception;
require_once('php1C__tokens.php');

/**
* Массив названий типов для работы с коллекциями переименовании
*/
const php1C_typesPHP_Collection = array('Array1C','Structure1C','Map1C','ValueTable', 'FixedArray1C');

/**
* Массив названий английских функций для работы с датой. Соответствует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
const php1C_functionsPHP_Collections = array('UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',    'Set(',       'Property(','LoadColumn(',     'UnloadColumn(',      'FillValues(',      'IndexOf(','Total(','Find(','FindRows(',    'Clear(',   'GroupBy(',  'Move(',    'Copy(',       'CopyColumns(',          'Sort(',       'Del(');

/**
 * Вызывает функции и функции объектов 1С работы с коллекциями
 *
 * @param string $key строка в названии функции со скобкой
 * @param array $arguments аргументы функции в массиве
 * @return Structure1C|Map1C|Array1C|ValueTable|FixedArray1C результат функции или выбрасывает исключение
 * @throws Exception
 */
function callCollectionType(string $key, array $arguments)
{
    switch($key) {
        case 'Array1C': return Array1C($arguments);
        case 'FixedArray1C': return FixedArray1C($arguments);
        case 'Structure1C': return Structure1C($arguments);
        case 'Map1C': return Map1C($arguments);
        case 'ValueTable': return ValueTable($arguments);
        default: throw new Exception('Пока тип в коллекциях не определен ' . $key);
    }
}

//---------------------------------------------------------------------------------------------------------

/**
 * Базовый класс коллекций 1С
 */
class BaseCollection1C{
    public array $value;

    function getItem($key, $value){
        return $value;
    }

    function toArray(): array{
        return $this->value;
    }
    function Count(): int
    {
        return count($this->value);
    }
}


/**
 * @throws Exception
 */
function FixedArray1C($args): FixedArray1C
{
    return new FixedArray1C($args);
}

class FixedArray1C extends BaseCollection1C {

    /**
     * @throws Exception
     */
    function __construct($array1C=null){
        if (is_null($array1C)) return;
        elseif(isset($array1C[0]) && $array1C[0] instanceof Array1C) $this->value = $array1C[0]->toArray();
        else throw new Exception("Неправильный конструктор ФиксированногоМассива");
    }

    function __toString(): string {
        return php1C_strFixedArray1C;
    }

    function UBound(): int
    {
        return max(array_keys($this->value));
    }

    /**
     * @throws Exception
     */
    function Find($val){
        $key = array_search($val, $this->value);
        if($key === FALSE) return php1C_UndefinedType;
        else {
            if(is_numeric($key)) return new Number1C(strval($key));
            else return $key;
        }
    }

    function Get($index){
        $index = $this->intIndex($index);
        return $this->value[$index];
    }
    private function intIndex($index):int{
        if($index instanceof Number1C) $index = intval($index->getValue());
        return $index;
    }
}

/**
 * @throws Exception
 */
function Array1C($args): Array1C
{
    return new Array1C($args);
}

/**
 * Класс для работы с массивом 1С
 *
 */
class Array1C extends FixedArray1C {

    function __construct($counts=null, $copy=null){

        parent::__construct();
        if(is_array($copy)) $this->value = $copy;
        else{
            $this->value = array();
            if(is_array($counts) && (count($counts)>0)){
                if( count($counts) > 1 ) throw new Exception("Многомерные массивы пока не поддерживаются");
                $cnt = $counts[0];
                if( is_numeric($cnt) && $cnt > 0 ){
                    for ($i=0; $i < $cnt; $i++) $this->value[$i] = null;
                }
            }
        }
    }

    function __toString(): string {
        return php1C_strArray1C;
    }

    function Insert($index, $val){
        $index = $this->intIndex($index);
        if( isset($this->value[$index])){
            array_splice($this->value, $index, 0, $val);
        }
        else $this->value[$index] = $val;
    }

    function Add($val): Array1C
    {
        $this->value[] = $val;
        return $this;
    }

    function Clear(){
        unset($this->value);
        $this->value = array();
    }

    function Del($index){
        $index = $this->intIndex($index);
        unset($this->value[$index]);
    }

    function Set($index, $val){
        $index = $this->intIndex($index);
        $this->value[$index] = $val;
    }

    private function intIndex($index):int{
        if($index instanceof Number1C) $index = intval($index->getValue());
        return $index;
    }
}

//------------------------------------------------------------------------------------------
//function KeyAndValue1C( $key, $value): KeyAndValue1C
//{
//    return new KeyAndValue1C($key, $value);
//}

class KeyAndValue1C{
    public  $key;
    public  $value;

    function __construct($key=php1C_UndefinedType,$value=php1C_UndefinedType){
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Возвращает ключ или значение
     * @throws Exception
     */
    function Get($key){
        switch ($key){
            case "КЛЮЧ":
            case "KLYUCH":
            case "KEY": return $this->key;
            case "ЗНАЧЕНИЕ":
            case "ZNACHENIE":
            case "VALUE": return $this->value;
        }
        throw new Exception("Не найден ключ или значение".$key);

    }
}

/**
 * Класс для работы с фиксированной структурой 1С
 */
class FixedStructure1C extends BaseCollection1C{
    protected array $keysOrigin;

    function __construct($args=null,$copy=null){

        if(is_array($copy)) $this->value = $copy;
        else{
            $this->value = array();
            if( (count($args) > 0) && is_string($args[0])){
                $keys = explode(',',$args[0]);
                for ($i=0; $i < count($keys); $i++) {
                    $k = mb_strtoupper(trim ($keys[$i]));
                    if( fEnglishVariable ) $k = str_replace(php1C_LetterLng, php1C_LetterEng, $k);
                    if(!isset($args[$i+1]))
                        $this->value[$k] = php1C_UndefinedType;
                    else
                        $this->value[$k] = $args[$i+1];
                    $this->keysOrigin[$k] = $keys[$i];
                }
            }
        }
    }
    function __toString(){
        return php1C_strFixedStructure1C;
    }
    function getItem($key, $value): KeyAndValue1C
    {
        return new KeyAndValue1C($this->keysOrigin[$key], $value);
    }
    //Для получения данных через точку
    function Get($key){
        if(is_string($key)){
            if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
            $key = mb_strtoupper($key);
        }
        return $this->value[$key];
    }
}

/**
 * Получение структуры 1С
 *
 * @param array|null $args аргументы функции в массиве
 * @return Structure1C - возвращает новый объект массива 1С
 *
 */
function Structure1C(array $args=null): Structure1C
{
    return new Structure1C($args);
}

/**
* Класс для работы со структурой 1С
*/
class Structure1C extends FixedStructure1C {

	function __toString(){
		return php1C_strStructure1C;
	}

	function Insert($key, $val=null){
        $key2 = $key;
        if(is_string($key)){
            if( fEnglishVariable ) $key2 = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
            $key2 = trim(mb_strtoupper($key2));
        }
        $this->value[$key2] = $val;
        $this->keysOrigin[$key2] = $key;
	}

    /**
     * Есть свойство
     * @param $key - ключ
     * @param $value - значение
     * @return bool - Истина если ключ(значение) существует
     */
    function Property($key, &$value=null): bool{
        if (is_string($key)) {
            $key = mb_strtoupper($key);
            if (fEnglishVariable) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
        }
		$value = $this->value[$key];
		return array_key_exists($key, $this->value);
	}

	function Clear(){
		unset($this->value);
        unset($this->keysOrigin);
		$this->value = array();
        $this->keysOrigin = array();
	}

	function Del($key){
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$key = mb_strtoupper($key);
		unset($this->value[$key]);
        unset($this->keysOrigin[$key]);
	}

    /**
     * Для установки данных через точку
     * @throws Exception
     */
    function Set($key, $val=null){
        $key2 = $key;
        if(is_string($key)) {
            $key2 = mb_strtoupper($key2);
            if (fEnglishVariable) $key2 = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
        }
		if(array_key_exists($key2, $this->value)){
            $this->value[$key2] = $val;
            $this->keysOrigin[$key2] = $key;
        }
		else throw new Exception("Не найден ключ структуры ".$key);
	}
}

//------------------------------------------------------------------------------------------
class FixedMap1C extends BaseCollection1C{
    function __construct($args=null,$copy=null){

        if(is_array($copy)) $this->value = $copy;
        else{
            $this->value = array();
            if( (count($args) > 0) && is_string($args[0])){
                $keys = explode(',',$args[0]);
                for ($i=0; $i < count($keys); $i++) {
                    $k = strtoupper(trim ($keys[$i]));
                    if( fEnglishVariable ) $k = str_replace(php1C_LetterLng, php1C_LetterEng, $k);
                    if(!isset($args[$i+1])) $this->value[$k] = null;
                    else $this->value[$k] = $args[$i+1];
                }
            }
        }
    }

    function __toString(){
        return php1C_strFixedMap1C;
    }
    /*
     * Для получения данных через точку
    */
    function Get($key){
        if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
        $key = strtoupper($key);
        return $this->value[$key];
    }
}
/**
 * Получение соответствия 1С
 *
 * @param null $args
 * @return Map1C - возвращает новый объект массива 1С
 *
 */
function Map1C($args=null): Map1C
{
	return new Map1C($args);
}

/**
* Класс для работы со структурой 1С
*/
class Map1C extends FixedMap1C {

	function __toString(){
		return php1C_strMap1C;
	}

	function Insert($key, $val=null){
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$this->value[strtoupper($key)] = $val;
	}

	function Property($key, $value=null): bool
    {
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$key = strtoupper($key);
		$value = $this->value[$key];
		return array_key_exists($key, $this->value);
	}

	function Clear(){
		unset($this->value);
		$this->value = array();
	}

	function Del($key){
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$key = strtoupper($key);
		unset($this->value[$key]);
	}

    /**
     * Для установки данных через точку
     * @throws Exception
     */
    function Set($key, $val=null){
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$key = strtoupper($key);
		if(array_key_exists($key, $this->value)) $this->value[$key] = $val;
		else throw new Exception("Не найден ключ структуры ".$key);
	}
}

//----------------------------------------------------------------------------------------------

/**
 * Получение ТаблицыЗначений
 *
 * @param null $args аргументы функции в массиве
 * @return ValueTable - возвращает новый объект ТаблицаЗначений1С
 *
 */
function ValueTable($args=null): ValueTable
{
	return new ValueTable($args);
}

/**
* Класс для работы с таблицей значений 1С8
*
*/
class ValueTable {
	
	private array $rows;   //array of ValueTableRow
	public ValueTableColumnCollection $COLUMNS; //ValueTableColumnCollection - collection of ValueTableColumn
	//public $КОЛОНКИ;
	public $KOLONKI;
	public CollectionIndexes $INDEXES; //CollectionIndexes коллекция из CollectionIndex
	//public $ИНДЕКСЫ;
	public $INDEKSYY;

	function __construct($args=null,$copy=null){

		if(is_array($copy)) $this->rows = $copy;
		else{	
			$this->rows = array();
			$this->COLUMNS = new ValueTableColumnCollection($this);
			//$this->КОЛОНКИ = &$this->COLUMNS;
			$this->KOLONKI = &$this->COLUMNS;
			$this->INDEXES = new CollectionIndexes($this);
			//$this->ИНДЕКСЫ = &$this->INDEXES;
			$this->INDEKSYY = &$this->INDEXES;
		}
	}

	function __toString(){
		return php1C_strValueTable1C;
	}

	function toArray(): array{
		return $this->rows;
	}

    /**
     * Возвратить элемент для каждого
     */
    function getItem($key, $value){
        return $value;
    }

    /**
     * Возвратить количество строк
     */
    function Count(): int{
        return count($this->rows);
    }

	//Добавить новую строку в таблицу
	function Add(): ValueTableRow {
		$row = new ValueTableRow($this);
		$this->rows[] = $row;
		return $row;
	}

    /**
     * Вставить новую строку в таблицу
     * @throws Exception
     */
    function Insert($index): ValueTableRow
    {
		if(is_int($index)){
			$row = new ValueTableRow($this);
			$this->rows[$index] = $row;
			return $row;
		}
		else  throw new Exception("Индекс задан неверно");
	}

    /**
     * Выгрузка колонки в Array1C
     * @throws Exception
     */
    function UnloadColumn($col): Array1C
    {
        $array = new Array1C;
		if(is_int($col)){
			$col = $this->COLUMNS->cols[$col];
		}elseif (is_string($col)) {
			if( fEnglishVariable ) $col = str_replace(php1C_LetterLng, php1C_LetterEng, $col);
			$col = $this->COLUMNS->cols[strtoupper($col)];
		}
		else throw new Exception("Не задана колонка для выгрузки ".$col);
		if(is_object($col) && get_class($col) === 'php1C\ValueTableColumn'){
			foreach ($this->rows as $key => $value) {
				$val = $value->Get($col->NAME);
				$array->Add($val);
			}
		}
        return $array;
	}

    /**
     * Загрузка колонки из Array1C
     * @throws Exception
     */
    function LoadColumn($arr, $col){
		if($arr instanceof Array1C)
			throw new Exception("Первый аргумент должен быть массивом ".$arr);
		if(isset($col)){
			if(is_int($col)){
				$col = $this->COLUMNS->cols[$col];
			}elseif (is_string($col)) {

				$col = $this->COLUMNS->cols[strtoupper($col)];
			}	
			if($col instanceof ValueTableColumn){
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

	/**
	 * Заполним имя всех столбцов
    */
	function GetAllColumns(){
		$strCols = '';
		foreach ($this->COLUMNS->cols as $val) {
			$strCols .= $val->NAME.',';
		}	
		return substr($strCols,0,-1); //уберем последнюю запятую
	}

	/**
	 * Заполнить значениями таблицу
    */
	function FillValues($value, string $strCols=null){
		if(!isset($strCols)) $strCols = $this->GetAllColumns();
        if($strCols===false) return;
		if( fEnglishVariable ) $strCols = str_replace(php1C_LetterLng, php1C_LetterEng, $strCols);
		$keys = explode(',',$strCols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			foreach ($this->rows as $val) {
				$val->Set($col, $value);
			}
		}
	}

	/**
	 * Возвратить индекс строки в таблице
    */
	function IndexOf($row){
		$key = array_search( $row, $this->rows);
		if( $key === FALSE ) $key = -1;
		return $key;
	}

	/**
	 * Возвратить итог по колонке
     * @throws Exception
     */
    function Total($col){
		if( fEnglishVariable ) $col = str_replace(php1C_LetterLng, php1C_LetterEng, $col);
		$col = strtoupper($col);
		$sum = 0;
		foreach ($this->rows as $value) {
			$val = $value->Get($col);
			if(is_numeric($val) || $val instanceof Number1C){
				$sum = add1C($sum, $val);
			}	
		}
		return $sum;
	}

	/**
	 * Найти значение в таблице и возвращать строку или Неопределенно
    */
	function Find($value, $strCols=null){
		if(!isset($strCols)) $strCols = $this->GetAllColumns();
		$keys = explode(',',$strCols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			foreach ($this->rows as $row) {
				$val = $row->Get($col);
				if( $val === $value ) return $row;
			}
		}
		return php1C_UndefinedType;
	}

    /**
     * Поиск по структуре возврат Array1C
     * @throws Exception
     */
    function FindRows($filter): Array1C
    {
		if($filter instanceof Structure1C){
			throw new Exception("Аргумент функции должен быть структурой ".$filter);
		} 
		$array_filter = $filter->toArray();
		$array = new Array1C();
		foreach ($this->rows as $key => $row){
			$found = true;
			foreach ($array_filter as $key_filter => $value_filter) {
				if( $row[$key_filter] == $value_filter ){
					$found = false;
				}
			}
			if($found) $array->Add($row); 	
		}
		return $array;
	}

    /**
     * Очистить значения таблицы
     */
	function Clear(){
		$this->COLUMNS->setValueTable(null);
		unset($this->rows);
		$this->rows = array();
	}

    /**
     * Для получения данных через точку
     * @throws Exception
     */
    function Get($key){
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
			$key = strtoupper($key);
			if($key === 'КОЛОНКИ' || $key === 'COLUMNS' || $key === 'KOLONKI'){
				return $this->COLUMNS;
			}	
		}
		if(is_numeric($key)){
		 	return $this->rows[$key];
		}
		throw new Exception("Не найден ключ для строки ТаблицыЗначений ".$key);
	}
    /**
     * Для установки данных через точку
     * @throws Exception
     */
    function Set($key, \php1C\ValueTableColumnCollection $val){
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
			$key = strtoupper($key);
			if(($key === 'КОЛОНКИ' || $key === 'COLUMNS') && (get_class($val) === 'php1C\ValueTableColumnCollection')){
				$this->COLUMNS = $val;
				$this->COLUMNS->setValueTable($this);
			}	
		}
		if(is_numeric($key) && (get_class($val) === 'ValueTableRow')){
		 	$this->rows[$key] = $val;
		}
		throw new Exception("Не найден имя столба ТаблицыЗначений ".$key);
	}

	//Группируем данные таблицы значений
    /**
     * @throws Exception
     */
    function GroupBy(string $colGr, string $colSum){
		if( fEnglishVariable ) $colGr = str_replace(php1C_LetterLng, php1C_LetterEng, $colGr);
		if( fEnglishVariable ) $colSum = str_replace(php1C_LetterLng, php1C_LetterEng, $colSum);
        $grKeys = explode(',',$colGr);
		$sumKeys = explode(',',$colSum);
		$table = $this->CopyColumns($colGr.','.$colSum);
		$this->COLUMNS = $table->COLUMNS;
		$this->COLUMNS->setValueTable($this);
		foreach ($this->rows as $row) {

			//Поиск совпадений по группировке
			$fNew = true;
			foreach ($table->rows as $newRow){
				$found = true;
				foreach ($grKeys as $grKey){
					if($newRow->Get($grKey) != $row->Get($grKey)){
						$found = false;
						break;
					}
				}
				if($found){
					$fNew = false;
					break;
				} 
			}
			
			if($fNew){
				//новая строка
				$newRow = $table->Add($this);
				$newRow->setValueTable($this);
				foreach ($grKeys as $grkey){
					$newRow->Set($grkey, $row->Get($grkey));
				}
				foreach ($sumKeys as $sumkey){
					$newRow->Set($sumkey, $row->Get($sumkey));
				}
			}else{
				//суммируем данные в строку
				foreach ($sumKeys as $sumkey){
					$curr = $newRow->Get($sumkey);
					$newRow->Set($sumkey, $curr + $row->Get($sumkey));
				}
			}
		}
		unset($this->rows);
		$this->rows = $table->rows;
		unset($table);
	}

	//Сдвинуть строку $row на $offset
	function Move($row, $offset){
		if(is_object($row) && get_class($row) === 'php1C\ValueTableRow'){
			$row = $this->IndexOf($row);
		}
        $row_int = intval($row);
        $offset_int = intval($offset);
		$row_object = $this->rows[$row_int];
		array_splice($this->rows,$row_int,1);
		array_splice($this->rows,$row_int+$offset_int,0,array($row_object));
	}

    /**
     * Скопировать таблицуЗначений с фильтрацией по строкам и колонкам
     *
     * @param null $rows массив строк для выгрузки
     * @param string|null $strcols
     * @return ValueTable - возвращает новый объект ТаблицаЗначений1С
     * @throws Exception
     */
	function Copy($rows=null, string $strcols=null): ValueTable
    {
		if(isset($row) && (!is_object($rows) || get_class($rows) !== 'php1C\Array1C')) throw new Exception("Первый параметр должен быть массивом строк или пустым");
		if(!isset($strcols)) $strcols = $this->GetAllColumns();
		if( fEnglishVariable ) $strcols = str_replace(php1C_LetterLng, php1C_LetterEng, $strcols);
		$array = $this->CopyColumns($strcols);
		if(!isset($rows)) $rows = $this->rows;
		else $rows = $rows->toArray();
		foreach ($rows as $row){
            $newRow = $array->Add();
			foreach ($array->COLUMNS->cols as $col){
				//var_dump($col);
				$newRow->Set($col->NAME, $row->Get($col->NAME));
			}
		}	
		return $array;
	}

    /**
     * Скопировать пустые колонки ТаблицуЗначений в новую ТаблицуЗначений
     *
     * @param string $strCols строка перечисления колонок
     * @return ValueTable - возвращает новый объект ТаблицаЗначений1С
     * @throws Exception
     */
	function CopyColumns(string $strCols): ValueTable
    {
		if(!isset($strCols)) $strCols = $this->GetAllColumns();
		if( fEnglishVariable ) $strCols = str_replace(php1C_LetterLng, php1C_LetterEng, $strCols);
		$array = new ValueTable;
		$keys = explode(',',$strCols);
		for ($i=0; $i < count($keys); $i++){
			$col = strtoupper(trim($keys[$i]));
			$array->COLUMNS->Add($col);
		}
		return $array;
	}

    /**
     * Отсортировать таблицу значений по стоке с колонками
     *
     * @param string $strolls @strcols string строка перечислений колонов и порядка сортировки ("Товар, Цена Убыв")
     * @param @cmp_object объект сортировки
     * @throws Exception
     */
	function Sort(string $strolls, $cmp_object=null){

		if (isset($cmp_object)) throw new Exception("Пока нет реализации по объекту сравнения");

		if(!isset($strolls)) $strolls = $this->GetAllColumns();
		if( fEnglishVariable ) $strolls = str_replace(php1C_LetterLng, php1C_LetterEng, $strolls);
		if(!is_string($strolls)) throw new Exception("Первый параметр должен быть обязаельно заполнен наименованиями колонок");
        $Sort = array();
        $Sorted = array();
        $pairs = explode(',',$strolls);
		foreach ($pairs as $pair) {
		 	$keys = explode(' ',$pair);
            if ($keys[0] === false) $col = "";
		  	else $col = strtoupper(trim($keys[0]));
            if ($keys[1] === false) $colder = "";
			else $colder = strtoupper(trim($keys[1]));
			if($colder==='УБЫВ' || $colder==="DESC") $Sorted[] =-1;
			else $Sorted[] = 1;
			$Sort[] = $col;
		}
		usort($this->rows, function($a, $b) use ($Sorted, $Sort) {
			for($i=0;$i<count($Sort);$i++){
				$vala = $a->Get($Sort[$i]);
                $vale = $b->Get($Sort[$i]);
				if($vala !== $vale) return $Sorted[$i] *(($vala < $vale) ? -1 : 1);
			}
			return 0;
		});
		unset($this->sortdir);
		unset($this->sort);
	}

    /**
     * Удалить строку из таблицы
     * @throws Exception
     */
    function Del($row){
		if(is_int($row)){
			$row = $this->rows[$row];
		}elseif(!is_object($row) && get_class($row) !== 'php1C\ValueTableRow'){
			throw new Exception("Параметр может быть либо строкой либо числом");
		}
		$key = $this->IndexOf($row);
		if($key !== -1){
			$row->setValueTable(null);
			unset($this->rows[$key]);
		}	
	}
}

/**
* Класс коллекции колонок таблицы значений 1С
*
*/
class ValueTableColumnCollection{

	/**
	* @var array коллекция ValueTableColumn
	*/
	private $ValueTable;
	public array $cols;

	function __construct($parent){
		$this->ValueTable = &$parent;
		$this->cols = array();
	}

	function toArray(): array
    {
		return $this->cols;
	}

	function setValueTable($parent){
		$this->ValueTable = &$parent;
	}

	function __toString(){
		return php1C_strColumnsValueTable1C;
	}

    /**
     * @throws Exception
     */
    function Add($key=null){
		if(!isset($key)) $key = ''; //пустые имена колонок в 1С допустимы.
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);		
			$key = strtoupper($key);
			$this->cols[$key] = new ValueTableColumn($key);
		}
		else  throw new Exception("Имя колонки должно быть строкой");
	}

	function Count(): int {
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
	//public $ИМЯ;

	function __construct($val=null){
		$this->NAME = $val;
		//$this->ИМЯ  = &$this->NAME;
	}

	function __toString(){
		return php1C_strColumnValueTable1C;
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
	private ValueTable $ValueTable; //parent
	private $row;        //array of fields

	function __construct($args=null){
		if(isset($args)) $this->ValueTable = &$args;
		$this->row = array();
	}

	function __toString(){
		return php1C_strRowValueTable1C;
	}

	function setValueTable($parent){
        if ($parent === null) unset($this->ValueTable);
		else $this->ValueTable = &$parent;
	}

	//Для получения данных через точку

    /**
     * @throws Exception
     */
    function Get($key){
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
			$key = strtoupper($key);
			$array = $this->ValueTable->COLUMNS->cols;
			if(array_key_exists($key, $array)){
				$key = strtoupper($key);
				return $this->row[$key];
			} 	
		}
		throw new Exception("Поле объекта не обнаружено у строки таблицы ".$key);
	}

	//Для установки данных через точку

    /**
     * @throws Exception
     */
    function Set($key, $value=null){
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
			$key = strtoupper($key);
			$this->row[$key] = $value;	
		}
		else throw new Exception("Нет такой колонки в таблице");
	}
}

/**
* Коллекция индексов(пока пустая реализация для ТаблицыЗначений)
*/
class CollectionIndexes{
	/**
	* @var array коллекция значений в строке
	*/
	private $ValueTable;
	private array $Indexes;

	function __construct($parent){
	 	$this->ValueTable = &$parent;
	 	$this->Indexes = array();
	}

	function __toString(){
	 	return php1C_strIndexesCollection1C;
	}

	function toArray(): array
    {
		return $this->Indexes;
	}

    /**
     *  Добавляем колонку в коллекцию индексов
     *
     * @throws Exception
     */
    function Add($key): void
    {
		if(is_string($key)){
			if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
			$key = strtoupper($key);
			$this->Indexes[$key] = new CollectionIndex($key);
		}
		else  throw new Exception("Имя колонки должно быть строкой");
	}

	function Count(): int
    {
		return count($this->Indexes);
	}

	function Clear(): void
    {
		unset($this->Indexes);
		$this->Indexes = array();
	}

	function Del($key): void
    {
		if( fEnglishVariable ) $key = str_replace(php1C_LetterLng, php1C_LetterEng, $key);
		$key = strtoupper($key);
		unset($this->Indexes[$key]);
	}
}

/**
* Индекс коллекции(пока пустая реализация для ТаблицыЗначений)
*/
class CollectionIndex{
	protected string $name;
    function __construct(string $col){
	 	$this->name = $col;
    }

	function __toString(){
	 	return php1C_strIndexCollection1C;
	}	
}
