<?php
/**
* Модуль для разбора кода 1С в массив токенов
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/

/**
* Подключаем пространство имен
*/
namespace php1CTransfer;

/**
* Используем стандартные исключения
*/
use Exception;

/*
*  Для подключение всех списков функций подключаем 
*/
require_once( 'php1C_common.php');


/**
* Класс токена - элемент кода
*/
class Token {
	public $type = 0;
	public $context = '';
	public $index = -1;

	//pointer to handle error
	public $row = 0;
    public $col = 0;
	
	function __construct($type = 0, $context = '', $index=-1){
		$this->type = $type;
		$this->context = $context;
		$this->index   = $index;
	}
}


/**
* Класс обработки потока кода 1С в массив токенов
*/
class TokenStream {

	//array of token
    public $tokens = array();
    private $itoken = 0;

    //common 
	private $str = '';
	private $start = 0;
	private $pos = 0;

	//pointer to handle error
	public $row = 1;
    public $col = 1;

    //types
    const type_end_code  = -1; 	
    const type_undefined = 0;
    const type_newline   = 1;
    const type_comments  = 10;
    const type_meta      = 11;
    const type_number    = 12;
    const type_string    = 13;
    const type_date      = 14;
    const type_operator  = 15;
    const type_keyword   = 16;
    const type_identification = 17;
    const type_function       = 18;
    const type_extfunction    = 19;
    const type_variable       = 50;
    
    //opers
	const oper_undefined    = 0;
	const oper_openbracket  = 1;
	const oper_closebracket = 2; 
	const oper_plus         = 3;
	const oper_minus        = 4;
	const oper_div          = 5;
	const oper_mult         = 6;
	const oper_point        = 7;
	const oper_equal        = 8;
	const oper_semicolon    = 9;
	const oper_comma        = 10;
	const oper_less         = 11;
	const oper_lessequal    = 12;
	const oper_more         = 13;
	const oper_morequal     = 14;
	const oper_question     = 15;
	const oper_notequal     = 16;
	const oper_or           = 20;
	const oper_xor          = 21; //todo
	const oper_and          = 22;
	const oper_not          = 23;
	const oper_new          = 25;
	const oper_opensqbracket  = 26;
	const oper_closesqbracket = 27;
	
	//Russian Letters 
	const LetterRusLower = array('а','б','в','г','д','е','ё' ,'ж' ,'з','и','й', 'к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	const LetterRusUpper = array('А','Б','В','Г','Д','Е','Ё' ,'Ж' ,'З','И','Й' ,'К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
	const str_Identifiers = '/^[_A-Za-zА-Яа-яЁё][_0-9A-Za-zА-Яа-яЁё]*/u';

	//Ключевые слова - type_keyword
	const keywords = array( 
		"code"    => array('НЕОПРЕДЕЛЕНО', 'ИСТИНА','ЛОЖЬ', 'ЕСЛИ', 'ТОГДА', 'ИНАЧЕЕСЛИ', 'ИНАЧЕ',   'КОНЕЦЕСЛИ','ПОКА',  'ДЛЯ', 'КАЖДОГО', 'ПО','В', 'ЦИКЛ','КОНЕЦЦИКЛА', 'ПРЕРВАТЬ','ПРОДОЛЖИТЬ', 'ФУНКЦИЯ', 'ПРОЦЕДУРА', 'КОНЕЦФУНКЦИИ', 'КОНЕЦПРОЦЕДУРЫ', 'ВОЗВРАТ', 'ПЕРЕМ', 'СИМВОЛЫ'),
		"codePHP" => array('Undefined',    'true',  'false','if(',  '){',    '} elseif {','} else {','}',        'while(','for(','foreach(','',  'in','){',            '}',         'break',   'continue',   'function', 'function', '}',            '}',              'return',  'var'  , 'CHARS'),
	);
	const keyword_undefined = 0; const keyword_true = 1; const keyword_false = 2; const keyword_if = 3; const keyword_then = 4; const keyword_elseif = 5; const keyword_else = 6; const keyword_endif = 7; const keyword_while = 8; const keyword_for = 9; const keyword_foreach = 10; const keyword_to = 11; const keyword_in = 12; const keyword_circle = 13; const keyword_endcircle = 14; const keyword_break = 15;  const keyword_continue = 16; const keyword_function = 17; const keyword_procedure = 18; const  keyword_endfunction = 19; const keyword_endprocedure = 20; const keyword_return=21; const keyword_var=22; const keyword_chars=23;

	//Индентификаторы типов - type_identification
	public $identypes = array(
		"code"    => array('МАССИВ','ФАЙЛ'),
		"codePHP" => array('Array1C', 'File1C'),
	);
	
	public $functions1С = array(
		"rus" => array(),  // функции по русски в верхнем регистре для поиска
		"eng" => array(),  // функции по английски в вернем регистре для поиска
		"clear" => array() // функции по английски как будет в коде 
	);
	public $indexFuncCom = -1;
	public $indexFuncStr = -1;
	public $indexFuncDate = -1;
	public $indexFuncColl = -1;

	/**
	* Конструктор класса
	* Заполняет массив функций для распознания.
	*/
	function __construct($str = ''){
		//Копирование строки кода
		$this->str = $str;

		//Добавление в таблицы общих функций 
		$this->indexFuncCom  = $this->AddModule( functions_Com(),  functionsPHP_Com());
		$this->indexFuncStr  = $this->AddModule( functions_String(),  functionsPHP_String());
		$this->indexFuncDate = $this->AddModule( functions_Date(), functionsPHP_Date());
		$this->indexFuncColl = $this->AddModule( functions_Collections(), functionsPHP_Collections());
	}

	/**
	* Добавление функций модуля в общую таблицу функций
	*
    * @param $funcRus array массив русских названий функций
    * @param $funcEng array массив английских названий функций
    * @return int возвращаем верхнюю границу модуля в общем списке 
	*/
	private function AddModule( $funcRus, $funcEng ){
		if(is_array($funcRus) && is_array($funcEng) ){
			foreach ($funcRus as $value) {
				array_push($this->functions1С['rus'], str_replace(self::LetterRusLower, self::LetterRusUpper, $value));
			}
			$array = functionsPHP_Com();
			foreach ($funcEng as $value) {
				array_push($this->functions1С['eng'], strtoupper($value));
			}
			foreach ($funcEng as $value) {
				array_push($this->functions1С['clear'], $value);
			}
		}
		return count($this->functions1С['clear']);
	}

	/**
	* Функции для разбора кода в токен
	*/
    public function eol(){ return $this->pos >= strlen($this->str); }
	public function current(){ 
		if($this->pos==$this->start) return '';
		else return substr($this->str,$this->start,$this->pos-$this->start); 
	}
	public function future(){ 
		return substr($this->str,$this->pos); 
	}
	private function prev() { 
		if($this->pos>=0) return substr($this->str,$this->pos-1,1);
		else return ''; 
	}
	private function curr() {
		if ($this->pos <= strlen($this->str)) return substr($this->str, $this->pos, 1);
		else return '';	
	}
	private function next() {
		if ($this->pos < strlen($this->str)) return substr($this->str, $this->pos+1, 1);
		else return '';	
	}
	private function move($count=1) {
		$this->pos+= $count;
	}
	
	private function match($pattern){
		$text = $this->future();
		if( preg_match($pattern, $text) === 1 ) return true;
		else return false;
	}
	private function matchMove($pattern){
		$text = $this->future();
		if( preg_match($pattern, $text, $matches) === 1 ){
			$this->move(strlen($matches[0]));
			return true;
		}
		else return false;
	}
	private function eatWhile($pattern) {
		$start = $this->pos;
		while ( preg_match($pattern, $this->curr())) { $this->pos++; }
		return $this->pos > $start;
	}
	private function eatSymbols($pattern) {
		$start = $this->pos;
		$pattern = '/['.$pattern.']/';
		while ( preg_match($pattern, $this->curr())) { $this->pos++; }
		return $this->pos > $start;
	}

	private function eatTo($pattern) {
		while ( !$this->eol() ){
			$ch = $this->curr();
			$strpos = strpos( $pattern, $ch); 
			if( $strpos === false) $this->pos++;
			else {
				$this->pos++;
				return true;	
			}	
		}
		return false;
	}
	private function skipToEndLine() { 
		return $this->eatTo("\n");
	}
	
	/**
	* Прочитать очередной токен из строки
	*/
	private function readToken(){

		if($this->eol()) return new Token(self::type_end_code, self::type_undefined);

		if( $this->eatSymbols(" \t\r") ){
		    $this->col += ($this->pos - $this->start);
			$this->start = $this->pos;
		    if($this->eol()) return new Token(self::type_end_code, self::type_undefined);
		}

		//Обработка новой строки
		if( $this->eatSymbols("\n") ){
		    $this->row+= ($this->pos - $this->start);
		 	$this->col = 1;
		 	return new Token(self::type_newline, $this->current());
		 }
				
		$ch = $this->curr();
		$prev = $this->prev(); 

        //Обработка комментариев
		if ($ch === '/') {
			if ($this->next() === '/') {
				$this->skipToEndLine();
				return new Token(self::type_comments, $this->current());
			}
		}

	    //Обработка метасимволов & или #
		if ($ch === '&' || $ch === '#') {
			$this->skipToEndLine();
			return new Token(self::type_meta, $this->current());
		}

		//Обработка чисел с одной точкой и лидирующей цифрой.
		if( is_numeric($ch) ){
			$this->eatWhile('/[0-9]/');
			if($this->curr() === '.'){
				$this->move();
				$this->eatWhile('/[0-9]/');	
			} 
			return new Token(self::type_number, $this->current());
		}

	    //Обработка строк
		if($ch === '"') {
			$this->move();
			if( $this->eatTo($ch) ) return new Token(self::type_string, trim($this->current(),'\"'));
			else throw new Exception("Пропущен символ '\"' (двойная кавычка)");
		}

		//Обработка дат (всяких неправильных дат типа '19090101-000000' или '19591015T00:00:00') 
		if ($ch === "'") {
			$this->move();
			$ch = $this->curr();
			$value = '';
			while( !$this->eol() ){
				$this->move();
				if($ch === "'"){
					//только правильные даты 191711070000 или 194506240000 или 19450509000000
					if( strlen($value) == 14 || strlen($value) == 12 || strlen($value) == 8){
						if( !checkdate(substr($value, 4, 2), substr($value, 6, 2),substr($value, 0, 4))){
							throw new Exception("Неправильная константа типа Дата");	
						}
						return new Token(self::type_date, $value);
					}
					else throw new Exception("Неправильная константа типа Дата");	
				}
				if(is_numeric($ch))	$value .= $ch;
				$ch = $this->curr();
			}
			throw new Exception('Пропущен символ \' (одинарная кавычка)');
		}

		//обработка операторов
		if ( strpos("()+-/*.=;,<>?[]", $ch) !== false){
			$this->move();
			if($ch=='<'){
				if( $this->curr()=='>'){
				$this->move();
				return new Token(self::type_operator,'<>',self::oper_notequal);
				}
				elseif($this->curr()=='='){
					$this->move();
					return new Token(self::type_operator,'<=',self::oper_lessequal);
				}
			}
			if($ch=='>' && $this->curr()=='='){
				$this->move();
				return new Token(self::type_operator,'>=',self::oper_morequal);
			}	

			switch($ch){
				case '(': return new Token(self::type_operator, '(', self::oper_openbracket);
				case ')': return new Token(self::type_operator, ')', self::oper_closebracket);
				case '+': return new Token(self::type_operator, '+', self::oper_plus);
				case '-': return new Token(self::type_operator, '-', self::oper_minus);
				case '/': return new Token(self::type_operator, '/', self::oper_div);
				case '*': return new Token(self::type_operator, '*', self::oper_mult);
				case '.': return new Token(self::type_operator, '.', self::oper_point);
				case '=': return new Token(self::type_operator, '=', self::oper_equal);
				case ';': return new Token(self::type_operator, ';', self::oper_semicolon);
				case ',': return new Token(self::type_operator, ',', self::oper_comma);
				case '<': return new Token(self::type_operator, '<', self::oper_less);
				case '>': return new Token(self::type_operator, '>', self::oper_more);
				case '?': return new Token(self::type_operator, '?', self::oper_question);
				case '[': return new Token(self::type_operator, '[', self::oper_opensqbracket);
				case ']': return new Token(self::type_operator, ']', self::oper_closesqbracket);
			}
			throw new Exception('Неизвестный оператор '.$ch);	
		}
	    
		//Обработка переменных, ключевых слов или функций
		if( $this->matchMove(self::str_Identifiers)){
			//$current = mb_strtoupper($this->current());
			$current = str_replace(self::LetterRusLower, self::LetterRusUpper, $this->current());
			if($current == 'НОВЫЙ' ) return new Token(self::type_operator, 'НОВЫЙ', self::oper_new);
			elseif($current == 'ИЛИ' ) return new Token(self::type_operator, 'ИЛИ', self::oper_or);
			elseif($current == 'И' ) return new Token(self::type_operator, 'И', self::oper_and);
			elseif($current == 'НЕ' ) return new Token(self::type_operator, 'НЕ', self::oper_not);	
			elseif($this->curr() == '('){
				//Идентификатор типа  с аргументами
				$key = array_search($current, $this->identypes['code']);
				if( $key !== false ) return new Token(self::type_identification, $current, $key);
				else{
					$this->move();
					//Общая функция на русском
					$key = array_search($current.'(', $this->functions1С['rus']);
					if( $key !== false ) return new Token(self::type_function, $current, $key); 
					else{
						$current = strtoupper($current);
						//Общая функция на английском
						$key = array_search($current.'(', $this->functions1С['eng']);
						if( $key !== false ) return new Token(self::type_function, $current, $key); 
						else return new Token(self::type_extfunction, $current);	
					} 
				}
		    } 
			else{
				//Ключевое слово
				$key = array_search($current, self::keywords['code']);
				if( $key !== false ) return new Token(self::type_keyword, $current,$key);
				else{
					//Идентификатор типа без скобок
					$key = array_search($current, $this->identypes['code']);
					if( $key !== false ) return new Token(self::type_identification, $current, $key);
					//Переменная
					else return new Token(self::type_variable, $this->current()); 
				} 
			}
		}
	    $this->move();
	    throw new Exception('Непонятный символ ('.$ch.')');
	}

	/**
	* Записать массив токенов из строки кода
	*/
	public function CodeToTokens(){

		unset($this->tokens);
		$this->tokens = array();
		$this->start = 0;
		$this->pos = 0;
		$token = $this->readToken(); 
		$key = 0;
		while( $token->type !== self::type_end_code ){
			$token->col = $this->col;
			$token->row = $this->row;
			if($token->type !== self::type_newline) $this->col += ($this->pos - $this->start);
			$this->start = $this->pos;
			$this->tokens[$key] = $token;
			$token = $this->readToken(); 
			$key++;
		}
		$this->tokens[$key] = new Token(self::type_end_code);//,'',-1,$this->col,$this->row);
		$this->tokens[$key]->col = $this->col;
		$this->tokens[$key]->row = $this->row;
		$this->start = 0;
		$this->pos = 0;
		$this->itoken = 0;
	}
}




