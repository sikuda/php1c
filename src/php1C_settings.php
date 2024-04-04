<?php
// Установка языка программирования
//- ru - default setting
//- en
if (!defined('Language1C')) define('Language1C', 'ru');

//true - Использовать только латинские переменные в PHP (false - переменные не переводятся)
const fEnglishVariable = true;

//true - Использовать только английские названия типов, false - не переводятся
const fEnglishTypes = true;

//true - Использовать повышенную точность вычислений как в 1С, false - стандартные вычисления php
const fPrecision1C = true;
const Scale1C = 27;

// Группировка 12 345.66 или 12345.66 
const Regional_grouping = false;


//Если неправильно определяется пояс - поставим вручную (для IIS который дает UTC)
const date_default_timezone = 'Europe/Moscow';
if (defined('date_default_timezone'))
    date_default_timezone_set(date_default_timezone);

