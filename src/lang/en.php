<?php
/**
* Module English lang for get code PHP from 1С
* 
* @author  sikuda@yandex.ru
* @version 0.2
*/
namespace php1C;

const php1C_lang = "en";
const php1C_Identifiers = '/^[_A-Za-z][_0-9A-Za-z]*/u';

const php1C_LetterLng = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q', 'R','S','T','U','V','W','X','Y','Z',
	                          'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q', 'r','s','t','u','v','w','x','y','z');
const php1C_LetterEng = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q', 'R','S','T','U','V','W','X','Y','Z',
	                          'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q', 'r','s','t','u','v','w','x','y','z');

const php1C_Keywords = array(
	'UNDEFINED',		//keyword_undefined = 0;
	'TRUE',				//keyword_true   = 1;
	'FALSE',			//keyword_false  = 2;
	'IF',				//keyword_if     = 3;
	'THEN',             //keyword_then   = 4; 
	'ELSIF',		    //keyword_elseif = 5;
	'ELSE',             //keyword_else   = 6;
	'ENDIF',            //keyword_endif  = 7; 
	'WHILE',            //keyword_while  = 8;
	'FOR',              //keyword_for    = 9;
	'EACH',         	//keyword_foreach = 10;
	'TO',               //keyword_to     = 11;
	'IN',               //keyword_in     = 12; 
	'FROM',             //keyword_from   = 13;
	'DO',               //keyword_circle = 14;
	'ENDDO',            //keyword_endcircle = 15;
	'BREAK',            //keyword_break  = 16;
	'CONTINUE',         //keyword_continue = 17;
	'FUNCTION',         //keyword_function = 18
	'PROCEDURE',        //keyword_procedure = 19;  
	'ENDFUNCTION',      //keyword_endfunction = 20; 
	'ENDPROCEDURE',     //keyword_endprocedure = 21;
	'RETURN',           //keyword_return  = 22;
	'VAR',              //keyword_var     = 23;
	'CHARS',            //keyword_chars   = 24;
	'EXPORT',           //keyword_export  = 25; 
	'VAL');             //keyword_val     =26;

/**
* Ключевое слово Новый
* используется для oпределения нового типа
*/
const php1C_type_New = 'NEW';

/**
* Массив названий английских типов для работы с коллекциями
*/
const php1C_types_Collection = array('ARRAY','STRUCTURE','MAP','VALUETABLE');

/**
* Массив названий английских функций для общей работы с 1С. Соответствует элементам русским функций.
*/   
const php1C_functions_Com = array(
	'MESSAGE(',
	'FIND(',
	'VALUEISFIELDED(',
	'TYPE(',
	'TYPEOF(',
	'STRING(',
	'NUNBER(',
	'NEW('
);


/**
* Массив названий английских функций для работы со строками. Соответстует элементам русским функций.
*/
const php1C_functions_String = array('STRLEN(', 'TRIML(','TRIMR(','TRIMLR(','LEFT(','RIGHT(','MID(','STRFIND(','LOWER(','UPPER(','TITLE(','CHAR(','CHARCODE(', 'ISBLANKSTRING(','STRREPLACE(', 'STRLINECOUNT(', 'STRGETLINE(','STROCCURRENCECOUNT(','STRCOMPARE(', 'STRSTARTSWITH(', 'STRENDSWITH(','STRSPLIT(', 'STRCONCAT(');

/**
* Массив названий английских функций для работы с числами. Соответстует элементам русским функций.
*/   
const php1C_functions_Number = array('INT(','ROUND(','LOG(','LOG10(','SIN(','COS(','TAN(','ASIN(','ACOS(','ATAN(','EXP(','POW(','SQRT(','FORMAT(', 'NUMBERINWORDS(', 'NSTR(', 'PERIODPRESENTATION(', 'STRTEMPLATE(', 'STRINGWITHNUMBER(');

/**
* Массив названий английских функций для работы с датой. Соответствует элементам русским функций.
*/   
const php1C_functions_Date = array('DATE(','CURRENTDATE(', 'YEAR(', 'MONTH(','DAY(', 'HOUR(', 'MINUTE(', 'SECOND(', 'BEGOFYEAR(', 'BEGOFQUARTER(', 'BEGOFMONTH(', 'BEGOFWEEK(' ,'BEGOFDAY(' ,'BEGOFHOUR(' ,'BEGOFMINUTE(', 'ENDOFYEAR(','ENDOFQUARTER(', 'ENDOFMONTH(', 'ENDOFWEEK(', 'ENDOFDAY(','ENDOFHOUR(','ENDOFMINUTE(','WEEKOFYEAR(', 'DAYOFYEAR(', 'WEEKDAY(', 'ADDMONTH(');

const php1C_MonthsLang = array('January','February','March','April','May','June','July','August','September','October','November','December');
//$php1C_endOfYear = char(160).'y.';
/**
* Массив названий английских функций для работы с датой. Соответствует элементам русским функций.
*/   
const php1C_functions_Collections = array('UBOUND(', 'INSERT(', 'ADD(', 'COUNT(', 'FIND(', 'CLEAR(' , 'GET(', 'DEL(', 'SET(', 'PROPERTY(','LOADCOLUMN(', 'UNLOADCOLUMN(', 'FILLVALUES(', 'INDEXOF(','TOTAL(','FIND(','FINDROWS(', 'CLEAR(', 'GROUPBY(', 'MOVE(','COPY(','COPYCOLUMNS(','SORT(','DEL(');

/*
* Константы для Вывода как в 1С
*/
const php1C_Undefined = 'Undefined';
const php1C_strBool = "Boolean";
const php1C_Number =  "Number";
const php1C_String  = "String";
const php1C_Bool = array('Yes','No');
const php1C_double_quotes  = "\" (double quote)";
const php1C_single_quotes  = '\' (single quote)';

/*
* Логические операции 
*/
const php1C_OR = 'OR';
const php1C_AND = 'AND';
const php1C_NOT = 'NOT';

/*
* Представление ошибок на языке
*/
const php1C_error_UndefineOperator = 'Undefined operator';
const php1C_error_UndefineSymbol   = 'Undefined symbol';
const php1C_error_UndefineFunction = 'Undefined function';
const php1C_error_BadDateType      = 'Bad const type date';

const php1C_error_BadOperTypeEqual  = "Comparison operations are equivalent only for results of matching primitive types (Boolean, Number, String, Date)";