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
//const fPrecision1C = true;
const Scale1C = 36;
const Scale1C_Int = 27;

// Группировка 12 345.66 или 12345.66 
const Regional_grouping = false;

//Если неправильно определяется пояс - поставим вручную (для IIS который дает UTC)
if (date_default_timezone_get() == 'UTC')
    date_default_timezone_set('Europe/Moscow');

