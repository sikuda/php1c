<?php
// Установка языка програмирования 
//- ru - default setting
//- en
if (!defined('Language1C')) define('Language1C', 'ru');

//true - Использовать только латинские переменные в PHP (false - переменные не переводятся)
define('fEnglishVariable', true);

//true - Использовать только английские названия типов, false - не переводятся
define('fEnglishTypes', false);

//true - Использовать повышенную точность вычислений как в 1С, false - стандартные вычисления php
define('fPrecision1C', false);
define('Scale1C', 27);

// Группировка 12 345.66 или 12345.66 
define('Regionalset_grouping', false);

?>