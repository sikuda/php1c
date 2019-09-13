<?php
/**
* Модуль Английского языка для получения кода PHP из 1С
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/
namespace php1C;

const php1C_Identifiers = '/^[_A-Za-z][_0-9A-Za-z]*/u';

const php1C_LetterLng = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q', 'R','S','T','U','V','W','X','Y','Z',
	                          'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q', 'r','s','t','u','v','w','x','y','z');
const php1C_LetterEng = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q', 'R','S','T','U','V','W','X','Y','Z',
	                          'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q', 'r','s','t','u','v','w','x','y','z');

const php1C_Keywords = array(
	'Undefined',		//keyword_undefined = 0;
	'true',				//keyword_true   = 1;
	'false',			//keyword_false  = 2;
	'if(',				//keyword_if     = 3;
	'){',               //keyword_then   = 4; 
	'} elseif {',		//keyword_elseif = 5;
	'} else {',         //keyword_else   = 6;
	'}',                //keyword_endif  = 7; 
	'while(',           //keyword_while  = 8;
	'for(',             //keyword_for    = 9;
	'foreach(',         //keyword_foreach = 10;
	'to',               //keyword_to     = 11;
	'in',               //keyword_in     = 12; 
	'in',               //keyword_from   = 13;
	'){',               //keyword_circle = 14;
	'}',                //keyword_endcircle = 15;
	'break',            //keyword_break  = 16;
	'continue',         //keyword_continue = 17;
	'function',         //keyword_function = 18
	'function',         //keyword_procedure = 19;  
	'}',                //keyword_endfunction = 20; 
	'}',                //keyword_endprocedure = 21;
	'return',           //keyword_return  = 22;
	'var',              //keyword_var     = 23;
	'chars',            //keyword_chars   = 24;
	'export',           //keyword_export  = 25; 
	'VAL');             //keyword_val     =26;

/**
* Массив названий английских типов для работы с коллекциями
*/
const php1C_types_Collection = array('Array','Structure','ValueTable');

/**
* Массив названий английских функций для общей работы с 1С. Соответстует элементам русским функций.
*/   
const php1C_functions_Com = array(
	'Message(',
	'Find(',
	'ValueIsFilled(',
	'Type(',
	'TypeOf('
);


/**
* Массив названий английских функций для работы со строками. Соответстует элементам русским функций.
*/
const php1C_functions_String = array('StrLen(',  'TrimL(','TrimR(','TrimLR(','Left(','Right(','Mid(','StrFind(','Lower(','Upper(','Title(','Char(','CharCode(',   'IsBlankString(','StrReplace(', 'StrLineCount(', 'StrGetLine(','StrOccurrenceCount(','StrCompare(', 'StrStartsWith(', 'StrEndsWith(','StrSplit(', 'StrConcat(');

/**
* Массив названий английских функций для работы с числами. Соответстует элементам русским функций.
*/   
const php1C_functions_Number = array('Int(','Round(','Log(','Log10(','Sin(','Cos(','Tan(','ASin(','ACos(','ATan(','Exp(','Pow(','Sqrt(','Format(', 'NumberInWords(', 'NStr(', 'PeriodPresentation(', 'StrTemplate(', 'StringWithNumber(');

/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
*/   
const php1C_functions_Date = array('Date(','CurrentDate(', 'Year(', 'Month(','Day(', 'Hour(', 'Minute(', 'Second(', 'BegOfYear(', 'BegOfQuarter(',  'BegOfMonth(', 'BegOfWeek('   ,'BegOfDay(' ,'BegOfHour(' ,'BegOfMinute(', 'EndOfYear(','EndOfQuarter(', 'EndOfMonth(', 'EndOfWeek(',  'EndOfDay(','EndOfHour(','EndOfMinute(','WeekOfYear(', 'DayOfYear(', 'WeekDay(',   'AddMonth(');

/**
* Массив названий английских функций для работы с датой. Соответстует элементам русским функций.
*/   
const php1C_functions_Collections = array('UBound(',   'Insert(',   'Add(',      'Count(',      'Find(',  'Clear('  , 'Get(',      'Del(',    'Set(',       'Property(','LoadColumn(',     'UnloadColumn(',      'FillValues(',      'IndexOf(','Total(','Find(','FindRows(',    'Clear(',   'GroupBy(',  'Move(',    'Copy(',       'CopyColumns(',          'Sort(',       'Del(');

//Строковые представления некоторых типов
const php1C_Undefined = "Undefined";
const php1C_Bool = array("Yes","No");

?>