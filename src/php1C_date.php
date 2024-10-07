<?php

/**
* Модуль работы датой 1С
* 
* Модуль для работы самой датой 1С и функциями для работы с датой
* 
* @author  sikuda@yandex.ru
* @version 0.3
*/
namespace Sikuda\Php1c;
use Exception;
use Datetime;
use DateInterval;

/**
* Массив названий английских функций для работы с датой - соответствует элементам русским функций.
* @return string[] Массив названий функций работы с датой.
*/
const php1C_functionsPHP_Date = array('Date(','CurrentDate(', 'Year(', 'Month(','Day(', 'Hour(', 'Minute(', 'Second(', 'BegOfYear(', 'BegOfQuarter(',  'BegOfMonth(', 'BegOfWeek('   ,'BegOfDay(' ,'BegOfHour(' ,'BegOfMinute(', 'EndOfYear(','EndOfQuarter(', 'EndOfMonth(', 'EndOfWeek(',  'EndOfDay(','EndOfHour(','EndOfMinute(','WeekOfYear(', 'DayOfYear(', 'WeekDay(',   'AddMonth(', 'CurrentUniversalDateMilliseconds(');

const php1C_Months = array('January','February','March','April','May','June','July','August','September','October','November','December');

// ----------------------------------------------------------------------------------------------------------------------

/**
* Класс для работы с датой 1С
*
* Основной класс для работы с датой 1С.
*/
class Date1C {
	/**
	* @var DateTime внутреннее хранение даты-времени
	*/
	public DateTime  $value;

    /**
     * Конструктор объекта Date1C
     * @param string|DateTime $str_val Получение даты из строки типа (19171107) или (19700101000000)
     * @param int $seconds
     * @throws Exception
     */
	function __construct($str_val, int $seconds=0) {
		if(is_string($str_val)){
			if(strlen($str_val)==8) $this->value = DateTime::createFromFormat("YmdHis", $str_val . "000000");
			elseif(strlen($str_val)==12) $this->value = DateTime::createFromFormat("YmdHis", $str_val . "00");
	        else $this->value = DateTime::createFromFormat("YmdHis", $str_val);	//strlen($str)==14
		}
        else  $this->value = $str_val;
		if($seconds!==0) $this->add($seconds);
	}	

	/**
    * Преобразовать дату в строку шаблона "Дата. Месяц. Год Час: Минута: Секунда"
    * @return string "d.m.Y H:i:s"
    */
	function __toString(){
		return $this->value->format("d.m.Y H:i:s");
	}

    /**
     * Преобразовать дату по формату
    */
	function toFormat($str): string
    {
		return str_replace(
            php1C_Months,
            php1C_MonthsLang,
             $this->value->format($str));
	}

    /**
     * Добавить к дате количество секунд, возвращает текущий объект.
     * @param int $seconds Секунды, которые надо добавить к дате.
     * @return Date1C текущий объект
     * @throws Exception
     */
	public function add(int $seconds): Date1C
    {
		if($seconds>0) $this->value = $this->value->add( new DateInterval('PT'.$seconds.'S'));
		else $this->value = $this->value->sub( new DateInterval('PT'.-$seconds.'S'));
		return $this;
	}

    /**
     * Отнимает от даты количество секунд, возвращает текущий объект.
     * @param int $seconds Секунды, которые надо отнять от даты.
     * @return Date1C текущий объект
     * @throws Exception
     */
	public function sub(int $seconds): Date1C
    {
		if($seconds>0) $this->value = $this->value->sub( new DateInterval('PT'.$seconds.'S'));
		else $this->value = $this->value->add( new DateInterval('PT'.-$seconds.'S'));
		return $this;
	}
}

/**
 * Основная функция создания даты из строки или из последовательности чисел
 *
 * @param string|Date1C $str - год
 * @param int|Number1C $month - номер месяца с 1
 * @param int|Number1C $day - дата в месяце
 * @param int|Number1C $hour - час
 * @param int|Number1C $minute - минуты
 * @param int|Number1C $second - секунды
 *
 * @return Date1C Возвращает класс Date1C или вызывается исключение
 * @throws Exception
 */
function Date1C($str, $month=0,  $day=0, $hour=0, $minute=0, $second=0): Date1C
{
    if ($str instanceof Date1C) return $str;

    if($str instanceof Number1C || is_numeric($str)){
        if(is_string($str))
            if(mb_strlen($str)==8 || mb_strlen($str)==12 || mb_strlen($str)==14) return new Date1C($str);

        if($str instanceof Number1C) $str = intval($str->getValue());
        if($month instanceof Number1C) $month = intval($month->getValue());
        if($day instanceof Number1C) $day = intval($day->getValue());
        if($hour instanceof Number1C) $hour = intval($hour->getValue());
        if($minute instanceof Number1C) $minute = intval($minute->getValue());
        if($second instanceof Number1C) $second = intval($second->getValue());

        $check_date = checkdate( $month, $day, $str ) && ($hour >= 0) && ($hour < 60) && ($minute >=0) && ($minute < 60) && ($second >= 0) && ($second < 60);
        $str = str_pad($str,4,"0",STR_PAD_LEFT).str_pad($month,2,"0",STR_PAD_LEFT).str_pad($day,2,"0",STR_PAD_LEFT).str_pad($hour,2,"0",STR_PAD_LEFT).str_pad($minute,2,"0",STR_PAD_LEFT).str_pad($second,2,"0",STR_PAD_LEFT);

        if( $check_date ) return new Date1C($str);
        throw new Exception(php1C_error_ConvertToDateBad);
    }
    throw new Exception(php1C_error_ConvertToDateBad);
}

/**
 * Возвращает текущую дату в объекте Date1C
 *
 * @return Date1C
 * @throws Exception
 */
function CurrentDate(): Date1C
{
    return new Date1C(new DateTime("now"));
}

/**
* Возвращает год от даты
*
* @param Date1C $date
* @return int, Год из даты $date
*/
function Year(Date1C $date ): int
{
    return intval(date_format($date->value,"Y"));
}

/**
* Возвращает месяц от даты
*
* @param Date1C $date
* @return int, Порядковый номер месяца из даты $date (1-12)
*/
function Month(Date1C $date ): int
{
	return intval(date_format($date->value,"n"));
}

/**
* Возвращает месяц от даты
*
* @param Date1C $date
* @return int, День месяца без ведущего нуля (от 1 до 31)
*/
function Day(Date1C $date ): int
{
	return intval(date_format($date->value,"j"));
}

/**
* Возвращает час от даты
*
* @param Date1C $date
* @return int, Часы в 24-часовом формате без ведущего нуля (от 0 до 24)
*/
function Hour(Date1C $date ): int
{
	return intval(date_format($date->value,"G"));
}

/**
* Возвращает минуты от даты
*
* @param Date1C $date
* @return int, Минуты без ведущего нуля типа 01 (от 0 до 59)
*/
function Minute(Date1C $date ): int
{
	return intval(date_format($date->value,"i"));
}

/**
* Возвращает секунды от даты
*
* @param Date1C $date
* @return int, секунды без ведущего нуля типа 01 (от 0 до 59)
*/
function Second(Date1C $date ): int
{
	return intval(date_format($date->value,"s"));
}

/**
 * Возвращает начало года для даты
 *
 * @param Date1C $date
 * @return Date1C , начало года для даты $date
 * @throws Exception
 */
function BegOfYear(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").'0101000000');
}

/**
 * Возвращает начало квартала для даты
 *
 * @param Date1C $date
 * @return Date1C , начало квартала для даты $date
 * @throws Exception
 */
function BegOfQuarter(Date1C $date ): Date1C
{
	$month = 3*intdiv(intval(date_format($date->value,"m"))-1,3)+1;
    /** @var int $month */
    return Date1C(intval(date_format($date->value,"Y")), $month, 1);
}

/**
 * Возвращает начало месяца для даты
 *
 * @param Date1C $date
 * @return Date1C , начало квартала для даты $date
 * @throws Exception
 */
function BegOfMonth(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").'01000000');
}

/**
 * Возвращает начало недели (с понедельника)
 *
 * @param Date1C $date
 * @return Date1C , начало недели с понедельника для даты $date
 * @throws Exception
 */
function BegOfWeek(Date1C $date ): Date1C
{
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    $ts = (date('w', $ts) == 1) ? $ts : strtotime('last monday', $ts);
    return new Date1C(date("YmdHis", $ts));
}

/**
 * Возвращает начало дня для даты
 *
 * @param Date1C $date
 * @return Date1C , начало дня с полночи для даты $date
 * @throws Exception
 */
function BegOfDay(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").'000000');
}

/**
 * Возвращает начало часа для даты
 *
 * @param Date1C $date
 * @return Date1C , начало часа для даты $date
 * @throws Exception
 */
function BegOfHour(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").'0000');
}

/**
 * Возвращает начало минуты для даты
 *
 * @param Date1C $date
 * @return Date1C , начало минуты для даты $date
 * @throws Exception
 */
function BegOfMinute(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").'00');
}

/**
 * Возвращает конец года для даты
 *
 * @param Date1C $date
 * @return Date1C , конец года для даты $date
 * @throws Exception
 */
function EndOfYear(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").'1231235959');
}

/**
 * Возвращает конец квартала для даты
 *
 * @param Date1C $date
 * @return Date1C , конец квартала для даты $date
 * @throws Exception
 */
function EndOfQuarter(Date1C $date ): Date1C
{
	$month = 3*intdiv(intval(date_format($date->value,"m"))-1,3)+4;
	return new Date1C(date_format($date->value,"Y").str_pad($month, 2, "0", STR_PAD_LEFT)."01", -1);
}

/**
 * Возвращает конец месяца для даты
 *
 * @param Date1C $date
 * @return Date1C , конец месяца для даты $date
 * @throws Exception
 */
function EndOfMonth(Date1C $date ): Date1C
{
	$month = intval(date_format($date->value,"m"))+1;
	return new Date1C(date_format($date->value,"Y").str_pad($month, 2, "0", STR_PAD_LEFT)."01", -1);	
}

/**
 * Возвращает конец недели для даты
 *
 * @param Date1C $date
 * @return Date1C , конец недели для даты $date
 * @throws Exception
 */
function EndOfWeek(Date1C $date ): Date1C
{
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    $ts = (date('w', $ts) == 0) ? $ts : strtotime('next sunday', $ts);
    $ts = date("Ymd", $ts);
    return new Date1C($ts.'235959');
 }

/**
 * Возвращает конец дня для даты
 *
 * @param Date1C $date
 * @return Date1C , конец дня для даты $date
 * @throws Exception
 */
function EndOfDay(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").'235959');
}

/**
 * Возвращает конец часа для даты
 *
 * @param Date1C $date
 * @return Date1C , конец дня для даты $date
 * @throws Exception
 */
function EndOfHour(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").'5959');
}

/**
 * Возвращает конец минуты для даты
 *
 * @param Date1C $date
 * @return Date1C , конец дня для даты $date
 * @throws Exception
 */
function EndOfMinute(Date1C $date ): Date1C
{
	return new Date1C(date_format($date->value,"Y").date_format($date->value,"m").date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").'59');
}

/**
 * Возвращает порядковый номер недели года в соответствии со стандартом ISO-8601 (недели начинаются с понедельника)
 *
 * @param Date1C $date
 * @return int Порядковый номер недели года по 1C(1 или 2 января суббота или воскресенье это первая неделя)
 * @throws Exception
 */
function WeekOfYear(Date1C $date ): int
{
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
* Возвращает порядковый номер дня в году (начиная с 1)
* Date1C $date - дата
* @return int , Порядковый номер дня в году начиная с 1 для $date
*/
function DayOfYear(Date1C $date ): int
{
	$ts = strtotime(date_format($date->value,"Y").'-'.date_format($date->value,"m").'-'.date_format($date->value,"d"));
    return date('z', $ts)+1;
}

/**
* Возвращает порядковый номер дня в недели (1- понедельник ... 7-воскресенье)
*
* @param Date1C $date
* @return int  порядковый номер дня в недели для $date
*/
function WeekDay(Date1C $date ): int
{
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
 * @throws Exception
 */
function AddMonth(Date1C $date, int $int_month=0 ): Date1C
{

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

	if( checkdate( $month, intval(date_format($date->value,"d")), $year) ){
		return new Date1C(str_pad($year, 4, "0", STR_PAD_LEFT).str_pad($month, 2, "0", STR_PAD_LEFT).date_format($date->value,"d").date_format($date->value,"H").date_format($date->value,"i").date_format($date->value,"s"));
	}
	else{	
	 	$date1C = new Date1C(str_pad($year, 4, "0", STR_PAD_LEFT).str_pad($month+1, 2, "0", STR_PAD_LEFT)."01", -1);
	 	return new Date1C(date_format($date1C->value,"Y").date_format($date1C->value,"m").date_format($date1C->value,"d").date_format($date->value,"H").date_format($date->value,"i").date_format($date->value,"s"));
	} 
}

/*
 * Функция для расчета времени выполнения
 */
function CurrentUniversalDateMilliseconds(): float
{
    return hrtime(true)/1e+6;
}

