<?php
/**
* Модуль Русского языка для получения кода PHP из 1С
* 
* @author  sikuda admin@sikuda.ru
* @version 0.3
*/
namespace Php1c;

const php1C_lang = "ru";

const php1C_Identifiers = '/^[_A-Za-zА-Яа-яЁё][_0-9A-Za-zА-Яа-яЁё]*/u';

//Для преобразования имен в английский
const php1C_LetterLng = array('А','Б','В','Г','Д','Е', 'Ё', 'Ж','З','И','Й' ,'К','Л','М','Н','О','П','Р','С','Т','У','Ф', 'Х','Ц', 'Ч', 'Ш', 'Щ','Ъ','Ы','Ь', 'Э', 'Ю', 'Я' ,
							  'а','б','в','г','д','е', 'ё', 'ж','з','и','й', 'к','л','м','н','о','п','р','с','т','у','ф', 'х','ц', 'ч', 'ш', 'щ','ъ','ы','ь', 'э', 'ю', 'я');
const php1C_LetterEng = array('A','B','V','G','D','E','JO','ZH','Z','I','JJ','K','L','M','N','O','P','R','S','T','U','F','KH','C','CH','SH','SHH','','Y', '','EH','YU','YA',
							  'a','b','v','g','d','e','jo','zh','z','i','jj','k','l','m','n','o','p','r','s','t','u','f','kh','c','ch','sh','shh','','y', '','eh','yu','ya');

const php1C_Keywords = array(
	'НЕОПРЕДЕЛЕНО',    //keyword_undefined = 0
	'ИСТИНА',          //keyword_true   = 1;
	'ЛОЖЬ',            //keyword_false  = 2;
	'ЕСЛИ',            //keyword_if     = 3;
	'ТОГДА',           //keyword_then   = 4; 
	'ИНАЧЕЕСЛИ',       //keyword_elseif = 5;
	'ИНАЧЕ',           //keyword_else   = 6;
	'КОНЕЦЕСЛИ',       //keyword_endif  = 7; 
	'ПОКА',            //keyword_while  = 8;
	'ДЛЯ',             //keyword_for    = 9;
	'КАЖДОГО',         //keyword_foreach = 10;
	'ПО',              //keyword_to     = 11;
	'В',               //keyword_in     = 12; 
	'ИЗ',              //keyword_from   = 13;
	'ЦИКЛ',            //keyword_circle = 14;
	'КОНЕЦЦИКЛА',      //keyword_endcircle = 1
	'ПРЕРВАТЬ',        //keyword_break  = 16;
	'ПРОДОЛЖИТЬ',      //keyword_continue = 17
	'ФУНКЦИЯ',         //keyword_function = 18
	'ПРОЦЕДУРА',       //keyword_procedure = 1
	'КОНЕЦФУНКЦИИ',    //keyword_endfunction =
	'КОНЕЦПРОЦЕДУРЫ',  //keyword_endprocedure 
	'ВОЗВРАТ',         //keyword_return  = 22;
	'ПЕРЕМ',           //keyword_var     = 23;
	'СИМВОЛЫ',         //keyword_chars   = 24;
	'ЭКСПОРТ',         //keyword_export  = 25;
	'ЗНАЧ',            //keyword_val     =26;
    'NULL');           //keyword_null    =27;

/**
* Ключевое слово Новый
*/
const php1C_type_New = 'НОВЫЙ';

/**
* Массив названий русских типов для работы с коллекциями
* @return array of string - Массив названий функций работы с коллекциями.
*/
const php1C_types_Collection = array('МАССИВ','СТРУКТУРА','СООТВЕТСТВИЕ','ТАБЛИЦАЗНАЧЕНИЙ', 'ФИКСИРОВАННЫЙМАССИВ');
const php1C_types_File = array('ФАЙЛ', 'ЧТЕНИЕТЕКСТА', 'ЗАПИСЬТЕКСТА');

/**
* Массив общих русских функций для общей работы с 1С
*/
const php1C_functions_Com = array(
	'СООБЩИТЬ(', 
	'НАЙТИ(', 
	'ЗНАЧЕНИЕЗАПОЛНЕНО(', 
	'ТИП(', 
	'ТИПЗНЧ(',
	'СТРОКА(',
	'ЧИСЛО('
);

/**
* Массив названий русских функций для работы со строками
*/
const php1C_functions_String = array('СТРДЛИНА(','СОКРЛ(','СОКРП(','СОКРЛП(','ЛЕВ(','ПРАВ(','СРЕД(','СТРНАЙТИ(','НРЕГ(','ВРЕГ(', 'ТРЕГ(', 'СИМВОЛ(','КОДСИМВОЛА(','ПУСТАЯСТРОКА(','СТРЗАМЕНИТЬ(','СТРЧИСЛОСТРОК(','СТРПОЛУЧИТЬСТРОКУ(','СТРЧИСЛОВХОЖДЕНИЙ(', 'СТРСРАВНИТЬ(','СТРНАЧИНАЕТСЯС(','СТРЗАКАНЧИВАЕТСЯНА(', 'СТРРАЗДЕЛИТЬ(', 'СТРСОЕДИНИТЬ(');

/**
* Массив названий русских функций для работы с числами
*/
const php1C_functions_Number = array('ЦЕЛ(','ОКР(', 'LOG(','LOG10(','SIN(','COS(','TAN(','ASIN(','ACOS(','ATAN(','EXP(','POW(','SQRT(','ФОРМАТ(', 'ЧИСЛОПРОПИСЬЮ(', 'НСТР(', 'ПРЕДСТАВЛЕНИЕПЕРИОДА(', 'СТРШАБЛОН(', 'СТРОКАСЧИСЛОМ('  );

/**
* Массив названий русских функций для работы с датой
*/
const php1C_functions_Date = array('ДАТА(','ТЕКУЩАЯДАТА(', 'ГОД(', 'МЕСЯЦ(','ДЕНЬ(', 'ЧАС(', 'МИНУТА(', 'СЕКУНДА(','НАЧАЛОГОДА(','НАЧАЛОКВАРТАЛА(','НАЧАЛОМЕСЯЦА(','НАЧАЛОНЕДЕЛИ(','НАЧАЛОДНЯ(','НАЧАЛОЧАСА(','НАЧАЛОМИНУТЫ(','КОНЕЦГОДА(','КОНЕЦКВАРТАЛА(','КОНЕЦМЕСЯЦА(','КОНЕЦНЕДЕЛИ(','КОНЕЦДНЯ(','КОНЕЦЧАСА(','КОНЕЦМИНУТЫ(','НЕДЕЛЯГОДА(', 'ДЕНЬГОДА(', 'ДЕНЬНЕДЕЛИ(', 'ДОБАВИТЬМЕСЯЦ(', 'ТЕКУЩАЯУНИВЕРСАЛЬНАЯДАТАВМИЛЛИСЕКУНДАХ(');

const php1C_MonthsLang = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
//$php1C_endOfYear = char(160).'г.';
/**
* Массив названий русских функций для работы с датой
*/
const php1C_functions_Collections = array('ВГРАНИЦА(', 'ВСТАВИТЬ(', 'ДОБАВИТЬ(', 'КОЛИЧЕСТВО(', 'НАЙТИ(', 'ОЧИСТИТЬ(','ПОЛУЧИТЬ(', 'УДАЛИТЬ(','УСТАНОВИТЬ(','СВОЙСТВО(','ЗАГРУЗИТЬКОЛОНКУ(','ВЫГРУЗИТЬКОЛОНКУ(', 'ЗАПОЛНИТЬЗНАЧЕНИЯ(','ИНДЕКС(', 'ИТОГ(', 'НАЙТИ(','НАЙТИСТРОКИ(','ОЧИСТИТЬ(','СВЕРНУТЬ(', 'СДВИНУТЬ(','СКОПИРОВАТЬ(', 'СКОПИРОВАТЬКОЛОНКИ(','СОРТИРОВАТЬ(','УДАЛИТЬ(');

const php1C_functions_File = array('СУЩЕСТВУЕТ(','ЭТОФАЙЛ(','ЭТОКАТАЛОГ(','РАЗМЕР(','ОТКРЫТЬ(', 'ЗАКРЫТЬ(', 'ПРОЧИТАТЬ(', 'ПРОЧИТАТЬСТРОКУ(', 'ЗАПИСАТЬ(','ЗАПИСАТЬСТРОКУ(');

/*
* Для Вывода как в 1С
*/
const php1C_Undefined = "Неопределено";
const php1C_strBool = "Булево";
const php1C_Number = "Число";
const php1C_Date = "Дата";
const php1C_String = "Строка";
const php1C_Bool           = array("Да","Нет");
const php1C_double_quotes  = "\" (двойная кавычка)";
const php1C_single_quotes  = '\' (одинарная кавычка)';

/*
* Логические операции 
*/
const php1C_OR = 'ИЛИ';
const php1C_AND = 'И';
const php1C_NOT = 'НЕ';


/*
* Представление ошибок на языке
*/
const php1C_error_BadConstTypeNumber = 'Неправильная константа типа число ';
const php1C_error_BadDateType      = 'Неправильная константа типа Дата';
const php1C_error_BadNonOperAfterVar ='Неизвестный не оператор после переменной ';
const php1C_error_BadOperTypeEqual = "Операции сравнения равно допустима только для значений совпадающих примитивных типов (Булево-Число, Строка, Дата)";
const php1C_error_DoubleOper = 'Двойной оператор ';
const php1C_error_LostSymbol = 'Пропущен символ ';
const php1C_error_Expected = "Ожидается -";
const php1C_error_ExpectedComma = 'Ожидается запятая , ';
const php1C_error_ExpectedConstructionIfThen = 'Ожидается конструкция Если ... Тогда';
const php1C_error_ExpectedConstructionIfThenElseIf = 'Ожидается конструкция Если ... Тогда(ИначеЕсли)';
const php1C_error_ExpectedConstructionWhileDo = 'Ожидается конструкции Пока(Для) ... Цикл';
const php1C_error_ExpectedIdentType = 'Ожидается идентификатор типа, а не ';
const php1C_error_ExpectedFunctionObject = 'Предполагается функция объекта ';
const php1C_error_ExpectedNameVar = 'Ожидается имя переменной';
const php1C_error_ExpectedNameFunction ='Ожидается название функции или процедуры';
const php1C_error_ExpectedOperator = "Ожидается оператор";
const php1C_error_NonKeyword = 'Нет соответствия ключевому слову ';
const php1C_error_NonSymbol = "Неопределенный символ "; //Из Массива Символы
const php1C_error_NonSymbol2  = 'Ожидается символ из перечисления, а не ';
const php1C_error_OperBadLevel  = 'Операция не принадлежит этому уровню ';
const php1C_error_UndefineFunction = 'Непонятная функция';
const php1C_error_UndefineOperator = 'Неопознанный оператор ';
const php1C_error_UndefineSymbol   = 'Непонятный символ';
const php1C_error_UndefineType   = 'Пока тип не определен ';

const php1C_error_ConvertToNumberBad  = "Преобразование значения к типу Число не может быть выполнено";
const php1C_error_ConvertToDateBad  = 'Преобразование значения к типу Дата не может быть выполнено';

const php1C_error_DivideByZero = 'Деление на ноль';

//----------------------------- Collections ----------------------------------------
const php1C_strFixedArray1C ="ФиксированныйМассив";
const php1C_strArray1C = "Массив";

const php1C_strFixedStructure1C ="ФиксированнаяСтруктура";
const php1C_strStructure1C = "Структура";

const php1C_strFixedMap1C ="ФиксированноеСоответствие";
const php1C_strMap1C = "Соответствие";

const php1C_strValueTable1C = "ТаблицаЗначений";

const php1C_strColumnsValueTable1C = "КоллекцияКолонокТаблицыЗначений";

const php1C_strColumnValueTable1C = "КолонкаТаблицыЗначений";

const php1C_strRowValueTable1C = "КолонкаТаблицыЗначений";

const php1C_strIndexesCollection1C = "ИндексыКоллекции";
const php1C_strIndexCollection1C = "ИндексКоллекции";