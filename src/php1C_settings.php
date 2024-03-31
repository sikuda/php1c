<?php
// Установка языка программирования
//- ru - default setting
//- en
if (!defined('Language1C')) define('Language1C', 'ru');

//true - Использовать только латинские переменные в PHP (false - переменные не переводятся)
const fEnglishVariable = true;

//true - Использовать только английские названия типов, false - не переводятся
const fEnglishTypes = false;

//true - Использовать повышенную точность вычислений как в 1С, false - стандартные вычисления php
const fPrecision1C = false;
const Scale1C = 27;

// Группировка 12 345.66 или 12345.66 
const Regional_grouping = false;

