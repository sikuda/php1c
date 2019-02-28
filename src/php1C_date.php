<?php
/**
* Модуль работы датой 1С
* 
* Модуль для работы самой датой 1С и функциями для работы с датой
* 
* @author  sikuda admin@sikuda.ru
* @version 0.2
*/

/**
* Подключаем пространство имен
*/
namespace php1C;

/**
* Используем стандартные исключения и классы дат
*/
use Exception;
use \Datetime;
use \DateInterval;

/**
* Массив названий русских функций для работы с датой
* @return string[] Массив названий функций работы с датой.
*/
function functions_Date(){
	return  array('Дата(','ТекущаяДата(', 'Год(', 'Месяц(','День(', 'Час(',  'Минута(', 'Секунда(','НачалоГода(','НачалоКвартала(','НачалоМесяца(','НачалоНедели(','НачалоДня(','НачалоЧаса(','НачалоМинуты(','КонецГода(','КонецКвартала(','КонецМесяца(','КонецНедели(','КонецДня(','КонецЧаса(','КонецМинуты(','НеделяГода(', 'ДеньГода(', 'ДеньНедели(', 'ДобавитьМесяц(');
}
/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/   
function functionsPHP_Date(){
	return  array('Date(','CurrentDate(', 'Year(', 'Month(','Day(', 'Hour(', 'Minute(', 'Second(', 'BegOfYear(', 'BegOfQuarter(',  'BegOfMonth(', 'BegOfWeek('   ,'BegOfDay(' ,'BegOfHour(' ,'BegOfMinute(', 'EndOfYear(','EndOfQuarter(', 'EndOfMonth(', 'EndOfWeek(',  'EndOfDay(','EndOfHour(','EndOfMinute(','WeekOfYear(', 'DayOfYear(', 'WeekDay(',   'AddMonth(');
}	

/**
* Вызывает функцию работы с датой
*
* @param string $key строка названии функции со скобкой
* @param array $arguments аргументы функции в массиве
* @return возвращает результат функции или выбрасывает исключение
*/
function callDateFunction($key, $arguments){
	switch($key){
		case 'Date(': return Date1C($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6]);
		case 'CurrentDate(': 
			if(isset($arguments[0])) throw new Exception("Ожидается символ ')'");
			else return CurrentDate();
			break;
		case 'Year(': return Year($arguments[0]);
		case 'Month(': return Month($arguments[0]);
		case 'Day(': return Day($arguments[0]);
		case 'Hour(': return Hour($arguments[0]);
		case 'Minute(': return Minute($arguments[0]);
		case 'Second(': return Second($arguments[0]);
		case 'BegOfYear(': return BegOfYear($arguments[0]);
		case 'BegOfQuarter(': return BegOfQuarter($arguments[0]);
		case 'BegOfMonth(': return BegOfMonth($arguments[0]);
		case 'BegOfWeek(': return BegOfWeek($arguments[0]);
		case 'BegOfDay(': return BegOfDay($arguments[0]);
		case 'BegOfHour(': return BegOfHour($arguments[0]);
		case 'BegOfMinute(': return BegOfMinute($arguments[0]);
		case 'EndOfYear(': return EndOfYear($arguments[0]);
		case 'EndOfQuarter(': return EndOfQuarter($arguments[0]);
		case 'EndOfMonth(': return EndOfMonth($arguments[0]);
		case 'EndOfWeek(': return  EndOfWeek($arguments[0]);
		case 'EndOfDay(': return EndOfDay($arguments[0]);
		case 'EndOfHour(': return EndOfHour($arguments[0]);
		case 'EndOfMinute(': return EndOfMinute($arguments[0]);
		case 'WeekOfYear(': return WeekOfYear($arguments[0]);
		case 'DayOfYear(': return DayOfYear($arguments[0]);
		case 'WeekDay(': return WeekDay($arguments[0]);
		case 'AddMonth(': return AddMonth($arguments[0], $arguments[1]);
		default:
			throw new Exception("Неизвестная функция работы с датой ".$key."");
			break;
	}
}	

// ----------------------------------------------------------------------------------------------------------------------

/**
* Класс для работы с датой 1С
*
* Основной класс для работы с датой 1С. Капсулирует работу с операторами для даты и хранит внутреннее представление даты.
*/
class Date1C {
	/**
	* @var DateTime внутренее хранение даты-времени
	*/
	public $value; 

    /**
    * Конструктор объекта Date1C
    * @param string $str Получение даты из строки YYYYmmdd (19171107) или YYYYmmddHHiiss (19700101000000) 
    * @param int $second Секунды. котрые надо добавить к дате.
    */
	function __construct($str = '', $seconds=0) {
		if(is_string($str)){
			if(strlen($str)==8) $this->value = DateTime::createFromFormat("YmdHis", $str . "000000");
			elseif(strlen($str)==12) $this->value = DateTime::createFromFormat("YmdHis", $str . "00");
	        else $this->value = DateTime::createFromFormat("YmdHis", $str);	//strlen($str)==14
		}
		if($seconds!==0) $this->add($seconds);
	}	

	/**
    * Преобразоваить дату в строку шаблона "d.m.Y H:i:s"
    * @return string "d.m.Y H:i:s"
    */
	function __toString(){
		return $this->value->format("d.m.Y H:i:s");
	}

	function toFormat($str){
		return $this->value->format($str);	
	}
    
    /**
    * Добавить к дате количество секунд, возвращает текущий объект.
    * @param int $seconds Секунды, котрые надо добавить к дате.
    * @return Date1C текущий объект
    */
	public function add($seconds){
		$seconds = intval($seconds);
		if($seconds>0) $this->value = $this->value->add( new DateInterval('PT'.$seconds.'S'));
		else $this->value = $this->value->sub( new DateInterval('PT'.-$seconds.'S'));
		return $this;
	}

	/**
    * Отнимает от даты количество секунд, возвращает текущий объект.
    * @param int $seconds Секунды, котрые надо отнять от даты.
    * @return Date1C текущий объект
    */
	public function sub($seconds){
		$seconds = intval($seconds);
		if($seconds>0) $this->value = $this->value->sub( new DateInterval('PT'.$seconds.'S'));
		else $this->value = $this->value->add( new DateInterval('PT'.-$seconds.'S'));
		return $this;
	}
}

/**
* Основная функция создания даты из строки или из последовательности чисел
* 
* @param string $str Получение даты из строки YYYYmmdd (19171025) или YYYYmmddHHiiss (19700101000000) 
* или
* @param int $str - год
* @param int $month - номер месяца с 1
* @param int $day - дата в месяце
* @param int $hour - час
* @param int $minute - минуты
* @param int $second - секунды
*
* @return Date1C Возвращает класс Date1C или вызывается исключение
*/
function Date1C($str, $month=1, $day=1, $hour=0, $minute=0, $second=0){
	if(is_string($str)){
		if(strlen($str)==8 || strlen($str)==12 || strlen($str)==14) return new Date1C($str);	
		else throw new Exception('Преобразование значения к типу Дата не может быть выполнено. Длина строки не 8, не 12 и не 14');
	} 
	elseif(is_numeric($str)){

		$check_date = checkdate( $month, $day, $str ) && ($hour >= 0) && ($hour < 60) && ($minute >=0) && ($minute < 60) && ($second >= 0) && ($second < 60);
		$str = str_pad($str,4,"0",STR_PAD_LEFT).str_pad($month,2,"0",STR_PAD_LEFT).str_pad($day,2,"0",STR_PAD_LEFT).str_pad($hour,2,"0",STR_PAD_LEFT).str_pad($minute,2,"0",STR_PAD_LEFT).str_pad($second,2,"0",STR_PAD_LEFT);
		
		if( $check_date ) return new Date1C($str);
		throw new Exception('Преобразование значения к типу Дата не может быть выполнено');	
	}
	;
}

/**
* Возращает текущую дату в объекте Date1C
*
* @return Date1C 
*/
function CurrentDate( $arg=null ){
	return new Date1C(date("YmdHis"));
}

/**
* Возращает год от даты
*
* @param Date1C $date
* @return int, Год из даты $date
*/
function Year( $date ){
    return intval(date_format($date->value,"Y"));
}

/**
* Возращает месяц от даты
*
* @param Date1C $date
* @return int, Порядковый номер месяца из даты $date (1-12)
*/
function Month( $date ){
	return intval(date_format($date->value,"n"));
}

/**
* Возращает месяц от даты
*
* @param Date1C $date
* @return int, День месяца без ведущего нуля (от 1 до 31)
*/
function Day( $date ){
	return intval(date_format($date->value,"j"));
}

/**
* Возращает час от даты
*
* @param Date1C $date
* @return int, Часы в 24-часовом формате без ведущего нуля (от 0 до 24)
*/
function Hour( $date ){
	return intval(date_format($date->value,"G"));
}

/**
* Возращает минуты от даты
*
* @param Date1C $date
* @return int, Минуты без ведущего нуля типа 01 (от 0 до 59)
*/
function Minute( $date ){
	return intval(date_format($date->value,"i"));
}

/**
* Возращает секунды от даты
*
* @param Date1C $date
* @return int, секунды без ведущего нуля типа 01 (от 0 до 59)
*/
function Second( $date ){
	return intval(date_format($date->value,"s"));
}

/**
* Возращает начало года для даты
*
* @param Date1C $date
* @return Date1C , начало года для даты $date
*/
function BegOfYear( $date ){
	return new Date1C(date_format($date->value,"Y").'0101000000');
}

/**
* Возращает начало квартала для даты
*
* @param Date1C $date
* @return Date1C , начало квартала для даты $date
*/
function BegOfQuarter( $date ){
	$month = intdiv(date_format($date->value,"m"),3)+1;
	return Date1C(intval(date_format($date->value,"Y")), $month, 1);
}

/**
* Возращает начало месяца для даты
*
* @param Date1C $date
* @return Date1C , начало квартала для даты $date
*/
function BegOfMonth( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").'01000000');
}

/**
* Возращает начало недели (с понедельника)
*
* @param Date1C $date
* @return Date1C , начало недели с понедельника для даты $date
*/
function BegOfWeek( $date ){
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    $ts = (date('w', $ts) == 1) ? $ts : strtotime('last monday', $ts);
    return new Date1C(date("YmdHis", $ts));
}

/**
* Возращает начало дня для даты
*
* @param Date1C $date
* @return Date1C , начало дня с полночи для даты $date
*/
function BegOfDay( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").'000000');
}

/**
* Возращает начало часа для даты
*
* @param Date1C $date
* @return Date1C , начало часа для даты $date
*/
function BegOfHour( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").'0000');
}  

/**
* Возращает начало минуты для даты
*
* @param Date1C $date
* @return Date1C , начало минуты для даты $date
*/
function BegOfMinute( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").'00');
} 

/**
* Возращает конец года для даты
*
* @param Date1C $date
* @return Date1C , конец года для даты $date
*/
function EndOfYear( $date ){
	return new Date1C(date_format($date->value,"Y").'1231235959');
}

/**
* Возращает конец квартала для даты
*
* @param Date1C $date
* @return Date1C , конец квартала для даты $date
*/
function EndOfQuarter( $date ){
	$month = intdiv(date_format($date->value,"m"),3)+4;
	return new Date1C(date_format($date->value,"Y").str_pad($month, 2, "0", STR_PAD_LEFT)."01", -1);
}

/**
* Возращает конец месяца для даты
*
* @param Date1C $date
* @return Date1C , конец месяца для даты $date
*/
function EndOfMonth( $date ){
	$month = intval(date_format($date->value,"m"))+1;
	return new Date1C(date_format($date->value,"Y").str_pad($month, 2, "0", STR_PAD_LEFT)."01", -1);	
}

/**
* Возращает конец недели для даты
*
* @param Date1C $date
* @return Date1C , конец недели для даты $date
*/
function EndOfWeek( $date ){
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    $ts = (date('w', $ts) == 0) ? $ts : strtotime('next sunday', $ts);
    $ts = date("Ymd", $ts);
    return new Date1C($ts.'235959');
 }

/**
* Возращает конец дня для даты
*
* @param Date1C $date
* @return Date1C , конец дня для даты $date
*/ 
function EndOfDay( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").'235959');
}

/**
* Возращает конец часа для даты
*
* @param Date1C $date
* @return Date1C , конец дня для даты $date
*/
function EndOfHour( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").'5959');
}

/**
* Возращает конец минуты для даты
*
* @param Date1C $date
* @return Date1C , конец дня для даты $date
*/
function EndOfMinute( $date ){
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").'59');
} 

/**
* Возращает порядковый номер недели года в соответствии со стандартом ISO-8601 (недели начинаются с понедельника)
*
* @param Date1C $date
* @return int ,Порядковый номер недели года по 1C(1 или 2 января суббота или воскресенье это первая неделя)
*/
function WeekOfYear( $date ){
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
	$wd = WeekDay(BegOfYear($date));
	if($wd === 6){
		if(date_format($date->value,"m") === "01" && (date_format($date->value,"d") === "01" || date_format($date->value,"d") === "02"))  return 1;
		else return intval(date('W', $ts)) + 1;
	}
	elseif($wd === 7){
		if(date_format($date->value,"m") === "01" && date_format($date->value,"d") === "01") return 1;
		else return intval(date('W', $ts)) + 1;
	}
	else return intval(date('W', $ts));
}

/**
* Возращает порядковый номер дня в году (начиная с 1)
*
* @param Date1C $date
* @return Date1C , Порядковый номер дня в году начиная с 1 для $date
*/
function DayOfYear( $date ){
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    return date('z', $ts)+1;
}

/**
* Возращает порядковый номер дня в недели (1- понедельник ... 7-воскресенье)
*
* @param Date1C $date
* @return int  порядковый номер дня в недели для $date
*/
function WeekDay( $date ){
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    $ts = intval(date('w', $ts));
    if($ts === 0) $ts = 7;
    return $ts;

}

/**
* Добавляет несколько месяцев к дате 
*
* @param Date1C $date
* @param integer $int_month
* @return Date1C , добавляет несколько месяцев к дате $date и возвращает новый объект Date1C
*/
function AddMonth( $date, $int_month=0 ){

	$month = intval(date_format($date->value,"m"))+$int_month;
	$year = intval(date_format($date->value,"Y"));
	while($month <= 0){
		$year = $year - 1;
		$month = $month + 12;
	}
	while($month > 12){
		$year = $year + 1;
		$month = $month - 12;
	}

	$newdate = new Date1C(str_pad($year, 4, "0", STR_PAD_LEFT).str_pad($month, 2, "0", STR_PAD_LEFT).date_format($date->value,"d"));

	if( checkdate( $month, intval(date_format($date->value,"d")), $year) ){
		return new Date1C(str_pad($year, 4, "0", STR_PAD_LEFT).str_pad($month, 2, "0", STR_PAD_LEFT).date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").date_format($date->value,"s"));
	}
	else{	
	 	$newdate = new Date1C(str_pad($year, 4, "0", STR_PAD_LEFT).str_pad($month+1, 2, "0", STR_PAD_LEFT)."01", -1);
	 	return new Date1C(date_format($newdate->value,"Y").date_format($newdate->value,"m").date_format($newdate->value,"d").date_format($date->value,"H").date_format($date->value,"i").date_format($date->value,"s"));
	} 
}

