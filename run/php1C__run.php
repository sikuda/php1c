<?php
/**
* Основной модуль для разбора кода 1С
* 
* Модуль для работы с кодом 1С и его выполнения
* Выполнение кода или преобразование кода в код php
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


/**
* Подключаем базовый модуль работы с 1С и все остальные модули
*/
require_once('php1C__tokens.php');
//require_once('php1C_common_hp.php');
require_once('php1C_common.php');


/**
* Класс обработки потока кода 1С
*
* Основной класс обработки кода 1С. Выполняет код 1С
*/
class CodeStream {

	//array of token
    public $tokenStream = null;
    public $tokens = array();
    private $itoken = 0;

    //array of variable
    public $variable = array();

    //common 
	private $str = '';
	private $start = 0;
	private $pos = 0;

	//current token
	private $Type  = 0;
	private $Look  = '';
	private $Index = -1;

	//run code
	private $varstack = array();
	private $D0 = '';
		
	//pointer to handle error
	private $row = 1;
    private $col = 1;

    const LetterRus = array('А','Б','В','Г','Д','Е','Ё' ,'Ж' ,'З','И','Й' ,'К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х' ,'Ц','Ч' ,'Ш' ,'Щ'  ,'Ъ','Ы','Ь','Э' ,'Ю' ,'Я' ,'а','б','в','г','д','е','ё' ,'ж'  ,'з','и','й', 'к','л','м','н','о','п','р','с','т','у','ф','х' ,'ц','ч','ш' ,'щ'  ,'ъ','ы','ь','э' ,'ю' ,'я');
	const LetterEng = array('A','B','V','G','D','E','JO','ZH','Z','I','JJ','K','L','M','N','O','P','R','S','T','U','F','KH','C','CH','SH','SHH','' ,'Y','' ,'EH','YU','YA','a','b','v','g','d','e','jo','zh','z','i','jj','k','l','m','n','o','p','r','s','t','u','f','kh','c','ch','sh','shh','' ,'y','' ,'eh','yu','ya');
	
	public $functions_Common    = null;
	public $functionsPHP_Common = null;
	public $beginCommonFunc = -1, $endCommonFunc = -1;
	public $beginDateFunc = -1, $endDateFunc = -1;
	
	/**
	* Обработать один токен
	*/
	private function GetChar(){

		//Для отладки
		//if($this->itoken >= count($this->tokens)) throw new Exception('Выход за пределы массива токенов, индекс='.$this->itoken);

		$token = $this->tokens[$this->itoken];
		$this->Type = $token->type; 
		$this->Look = $token->context; 
		$this->Index = $token->index;
		$this->itoken++;

	}

	/**
	* Проверка совпадения оператора
	*/
	private function MatchOper($subtype, $look='???'){
		if( $this->Type === TokenStream::type_operator && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else throw new Exception('Ожидается оператор '.$look);
	}

	/**
	* Проверка совпадения ключевого слова
	*/
	private function MatchKeyword($subtype){
		if( $this->Type === TokenStream::type_keyword && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else{
			throw new Exception('Ожидается  '.TokenStream::keywords['code'][$subtype]);
		}	
	}

	/**
	* Выводит данные в представлении 1С (на русском)
	* @param stirng $arg число как строка
	* @return (string or float) Возвращем значение числа как в 1С (string - для чисел повышенной точности, float - если повышенная точность не важна*/  
	function toNumber1C(){

		if (false) return $this->D0;
		else return floatval($this->D0);
	}

	/**
	* Первичный выполнятор кода
	*/
	private function Factor(){
		
		//Обработка скобок - первый приоритет
		if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_openbracket){
			$this->MatchOper(TokenStream::oper_openbracket);
			$this->D0 = $this->Expression7();
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		else{
			
			$this->D0 = $this->Look;
			if($this->Type === TokenStream::type_variable){
				$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
				if( $this->variable[$key] === null ) throw new Exception('Не определена переменная '.$this->Look);
		    	else $this->D0 = $this->variable[$key]; 
			}
			if ($this->Type === TokenStream::type_date) $this->D0 = Date1C($this->D0);
			if ($this->Type === TokenStream::type_number) $this->D0 = $this->toNumber1C();
			
			if($this->Type === TokenStream::type_keyword){
				if($this->Index == TokenStream::keyword_undefined) $this->D0 = null;
				if($this->Index == TokenStream::keyword_true) $this->D0 = true;
				if($this->Index == TokenStream::keyword_false) $this->D0 = false;
			}

			if($this->Type === TokenStream::type_operator){

				if($this->Index === TokenStream::oper_point) throw new Exception('Неправильная константа типа Число '.$this->Look);

				if( $this->Index !== TokenStream::oper_plus && 
			    	$this->Index !== TokenStream::oper_minus && 
			    	$this->Index !== TokenStream::oper_new &&
			    	$this->Index !== TokenStream::oper_equal &&
			        $this->Index !== TokenStream::oper_semicolon) throw new Exception("Не унарный оператор ".$this->Look);
			}
			if( $this->Type === TokenStream::type_function ){
				$func = $this->functionsPHP_Common[$this->Index];
			 	$this->D0 = $this->splitFunction( null, $func, $this->Index);
			 	return;
			}
			if( $this->Type === TokenStream::type_extfunction){
				$func = str_replace($this->LetterRus, $this->LetterEng, $this->Look);
				$this->D0 = $this->splitFunction( null, $func);
				return;
			}	
			$type = $this->Type;
			$look = $this->Look;
			$index = $this->Index;
			$this->GetChar();
			$this->ForwardOperation($type, $look, $index);	
		} 
	}

	/**
	* Выполнение кода для зависящих от дальнейших данных(унарные операции и свойства и функции объекта)
	*/
	private function ForwardOperation($type, $look, $index=-1){
		if($type === TokenStream::type_operator){
			//Унарный минус
			if($index === TokenStream::oper_minus){
				$this->Factor();
				$this->D0 = - $this->D0;
			}elseif($index === TokenStream::oper_plus) {
				$this->Factor();
			}
			//Оператор Новый и тип
			elseif($index === TokenStream::oper_new) {
				if( $this->Type === TokenStream::type_identification){
					$this->D0 =  $this->getNewType();
					$this->GetChar();
				} 
				else throw new Exception('Ожидается идентификатор типа');
			}
			elseif( $this->Type === TokenStream::type_operator && ( $index === TokenStream::oper_mult || $index === TokenStream::oper_div )){
				throw new Exception('Двойной оператор ');	
			}	
		}
		elseif($type === TokenStream::type_variable){
			$key = str_replace(self::LetterRus, self::LetterEng, $look);
			//Обработка свойств и функций объекта
		    while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_point){
		    	$this->GetChar();
		    	//функции объекта
		    	if( $this->Type === TokenStream::type_function ){
		    		$func = $this->functionsPHP_Common[$this->Index];
		    		$this->D0 = $this->splitFunction( $this->D0, $func, $this->Index);
		    	}
		    	//свойства объекта	
				elseif($this->Type === TokenStream::type_variable) throw new Exception('Свойства объекта пока не работают '.$this->Look);
				else throw new Exception('Предполагается функция или свойство объекта '.$look);
			}	
		}	
	}

	//Выдать идентификатор типа по названию или индексу
	private function getNewType(){
		switch ($this->Index) {
			case 'МАССИВ': return Array1C();
			case 'ФАЙЛ': return File1C();
			default: 
			    throw new Exception('Пока тип не определен '.$this->Look);
			    break;
		}
	}

	/**
	* Обработка 7 уровней операторов, а точнее 6
	*/
	public function Expression7($level=7){

		if($level > 2) $this->Expression7($level-1);
		switch ($level) {
			case 2: 
			    $this->Factor();
				break;
			case 3: // Умножение или деление (* /)
		        while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_mult || $this->Index === TokenStream::oper_div)){
		        	array_push($this->varstack, $this->D0);
		        	$index = $this->Index;
		        	$this->GetChar();
					$this->Expression7(2);
					if( $index === TokenStream::oper_mult ) $this->D0 = mul1C(array_pop($this->varstack), $this->D0);
					else $this->D0 = div1C(array_pop($this->varstack),$this->D0);
				}
				break;
			case 4: //Сложение или вычитание (+ -)
				while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_plus || $this->Index === TokenStream::oper_minus)){
					array_push($this->varstack, $this->D0);
		        	$index = $this->Index;
		        	$this->GetChar();
					$this->Expression7(3);
					if( $index === TokenStream::oper_plus )	$this->D0 = add1C(array_pop($this->varstack), $this->D0);
					else $this->D0 = sub1C(array_pop($this->varstack), $this->D0);
				}
				break;
			case 5: //Больше меньше или равно (< <= = <> > >=)
				while( $this->Type === TokenStream::type_operator && 
					   ($this->Index === TokenStream::oper_less || $this->Index === TokenStream::oper_lessequal || $this->Index === TokenStream::oper_equal || 
					   	$this->Index === TokenStream::oper_notequal || $this->Index === TokenStream::oper_more || $this->Index === TokenStream::oper_morequal)){
					array_push($this->varstack, $this->D0);
		        	$index = $this->Index;
		        	$this->GetChar();
					$this->Expression7(4);
					switch ($index) {
						case TokenStream::oper_less:
							$this->D0 = less1C(array_pop($this->varstack), $this->D0);
							break;
						case TokenStream::oper_lessequal:
							$this->D0 = lessequal1C(array_pop($this->varstack), $this->D0);
							break;
						case TokenStream::oper_equal:
							$this->D0 = equal1C(array_pop($this->varstack), $this->D0);
							break;
						case TokenStream::oper_notequal:
							$this->D0 = notequal1C(array_pop($this->varstack), $this->D0);
							break;	
						case TokenStream::oper_more:
							$this->D0 = more1C(array_pop($this->varstack), $this->D0);
							break;
						case TokenStream::oper_moreequal:
							$this->D0 = moreequal1C(array_pop($this->varstack), $this->D0);
							break;		
						default:
							break;
					}
				}
				break;
			case 6: //И
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_and){
					array_push($this->varstack, $this->D0); 
					$this->GetChar();
					$this->Expression7(5);
					$this->D0 = and1C(array_pop($this->varstack), $this->D0);
				}
				break;
			case 7: //ИЛИ
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_or){
					array_push($this->varstack, $this->D0); 
					$this->GetChar();
					$this->Expression7(5);
					$this->D0 = or1C(array_pop($this->varstack), $this->D0);
				}
				break;
			default:
				break;
		}
		return $this->D0;
	}

	/**
	* Выполнение кода функции с аргументами
	*/
	public function splitFunction($context=null, $func, $index=-1){
		$arguments = array();
		$this->GetChar();
		if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
			$arguments[] = $this->Expression7();
			
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
				$this->GetChar();
				$arguments[] = $this->Expression7();	
			}
		}
		$this->MatchOper(TokenStream::oper_closebracket, ')');
		$this->MatchOper(TokenStream::oper_semicolon, ';');
			 
		if($this->beginCommonFunc < $index && $this->endCommonFunc > $index){
			return callCommonFunction($context, $func, $arguments);
		}

		if($this->beginDateFunc < $index && $this->endDateFunc > $index){
			return callDateFunction($func, $arguments);
		} 

		throw new Exception("Неизвестная функция ".$func."");
	}

	private function isIfOperation($handle){
		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
			if($this->Index === TokenStream::keyword_elseif || $this->Index === TokenStream::keyword_else || $this->Index === TokenStream::keyword_endif) 
				return true;
		}	
		if($handle === TokenStream::keyword_else && $this->Index === TokenStream::keyword_endif) return true;	
		return false;
	}

	private function isCircleOperation($handle){
		return false;
	}

	/*
	** Выполнение 1С Кода и циклические вызовы
	**
	**$handle - expected keyword to handle
	**$other - old result of handle
	*/
	public function continueCode($handle=-1, $skip= false, $done=false){	

		while($this->Type !== TokenStream::type_end_code){

			//Проверка на необходимость выполнения кода
			if( !$skip || ($skip && $this->Type===TokenStream::type_keyword && 
				           	( $this->isIfOperation($handle) || $this->isCircleOperation($handle) )))
			{
				switch ($this->Type) {
					case TokenStream::type_newline: 
						$this->GetChar();
						break;
					case TokenStream::type_variable:
						$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
						$this->GetChar();
						if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal){
					 		//Оператор присвоения переменной
					 		$this->GetChar();
							$value = $this->Expression7();
							if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_semicolon){
								$this->variable[$key] = $value;
								$this->MatchOper(TokenStream::oper_semicolon, ';');
							}
							elseif ($this->Type === TokenStream::type_end_code) {
								$this->variable[$key] = $value;
							}
							else throw new Exception('Ожидается ;');
						}
						else throw new Exception('Неизвестный оператор после переменной ');
						break;
					case TokenStream::type_function:
					case TokenStream::type_extfunction:
						$this->Expression7();
						break;
					case TokenStream::type_comments:
						$this->GetChar();
						break;		
					//Ключевые слова
					case TokenStream::type_keyword:
						switch($this->Index){
							//Если Тогда Иначе
						 	case TokenStream::keyword_if:
						 		$this->GetChar();
						 		$key = $this->Expression7();
						 		$this->MatchKeyword(TokenStream::keyword_then);
						 		if($key) $this->continueCode(TokenStream::keyword_then, false, true);
						 		else $this->continueCode(TokenStream::keyword_then, true, false);
						 		break;
						 	case TokenStream::keyword_elseif:
						 		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
						 			$this->MatchKeyword(TokenStream::keyword_elseif);
						 			$key = $this->Expression7();
							 		$this->MatchKeyword(TokenStream::keyword_then);
							 		if($done) $skip = true;
						 			elseif($key){ $skip = false; $done=true; }
						 			return $this->continueCode(TokenStream::keyword_elseif, $skip, $done);
							 	}
								else throw new Exception('Ожидается конструкция Если ... Тогда');
						 	case TokenStream::keyword_else:
						  		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
						 			$this->MatchKeyword(TokenStream::keyword_else);
						 			if($done) $skip = true;
						 			else{ $skip = false; $done=true; } 
						 			return $this->continueCode(TokenStream::keyword_else, $skip, $done);
						 		}	
						 		else throw new Exception('Ожидается конструкция Если ... Тогда(ИначеЕсли)');
						 	case TokenStream::keyword_endif:
						 		if($handle===TokenStream::keyword_then || $handle === TokenStream::keyword_elseif || $handle===TokenStream::keyword_else){
						 			$this->MatchKeyword(TokenStream::keyword_endif);
						 			$this->MatchOper(TokenStream::oper_semicolon, ';');
						 			$done = true;
						 		}
						 		else throw new Exception('Ожидается конструкции Если ... Тогда(ИначеЕсли,Иначе)');
						 		break;
						 	//Циклы
						 	case TokenStream::keyword_while:
						 		$startpos = $this->itoken;
						 		$this->MatchKeyword(TokenStream::keyword_while);
						 		$key = $this->Expression7();
						 		$this->MatchKeyword(TokenStream::keyword_circle);
					 			while($key){
					 				if($this->continueCode(TokenStream::keyword_circle, true)){
						 				$this->setPosition($startpos); //move back to code
						 				$key = $this->Expression7();	
						 			    $this->MatchKeyword(TokenStream::keyword_circle);
					 				}
					 				else $key = false;
					 			}
						 		$this->continueCode(TokenStream::keyword_circle, false);
							 	break;
						 	case TokenStream::keyword_for:
						 		$this->MatchKeyword(TokenStream::keyword_for);
						 		//Пока только шаблона Для перем=
						 		if($this->Type !== TokenStream::type_variable) 
						 			throw new Exception('Ожидается имя переменной');
						 		$iterator = str_replace(self::LetterRus, self::LetterEng, $this->Look);
								$this->Factor();
								if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal ){
									$this->GetChar();
									$value = $this->Expression7();
					 				$this->variable[$iterator] = $value;
								}
								else throw new Exception('Ожидается символ =');
								$startpos = $this->itoken;
								$this->MatchKeyword(TokenStream::keyword_to);
						 		$key = $this->variable[$iterator] <= $this->Expression7();
						 		$this->MatchKeyword(TokenStream::keyword_circle);
					 			while($key){
					 				if($this->continueCode(TokenStream::keyword_circle, true)){
						 				$this->setPosition($startpos); //move back to code
						 				$this->variable[$iterator]++;
						 				$key = $this->variable[$iterator] <= $this->Expression7();	
						 			    $this->MatchKeyword(TokenStream::keyword_circle);
					 			    }
					 				else $key = false;
					 			}
							 	$this->continueCode(TokenStream::keyword_circle, $false);
								break;	
						 	case TokenStream::keyword_endcircle:
						 		if($handle===TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_endcircle);
						 			$this->MatchOper(TokenStream::oper_semicolon,';');
						 			return true;	
						 		}
						 		else throw new Exception('Ожидается начало конструкции Пока(Для) Цикл');
						 		break;
						 	case TokenStream::keyword_break:
						 		if($handle===TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_break);
						 			if($other){
						 				return false; 	
						 			}
						 		}	
						 		break;	
						 	case TokenStream::keyword_continue:
						 		if($handle===TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_continue);
						 			if($other) return true;
						 		}	
						 		break;	
						 	default:
						 		throw new Exception('Нет соответствия ключевому слову '.TokenStream::keywords['code'][$this->Index]);
								break;	
						}
						break;
					default:
						throw new Exception('Неопознанный оператор '.$this->Look);
						break;
				}	
			//skip token	
			}else $this->GetChar();
		}
		return $this->D0;
	}

	/**
	* Начало обработки выполенния кода
	*
	* @param string $buffer строка код для выполнения
	* @param string $name_var имя переменной для вывода 
	*/
	public function runCode($buffer='', $name_var=null){
		
		//Блок разбора по токеном
		try{
			$tokenStream = new TokenStream($buffer);
			$tokenStream->CodeToTokens();
			$this->tokens = &$tokenStream->tokens;
			
			$this->functions_Common    = &$tokenStream->functions_Common;
			$this->functionsPHP_Common = &$tokenStream->functionsPHP_Common;
			$this->beginCommonFunc = $tokenStream->beginCommonFunc; 
			$this->endCommonFunc = $tokenStream->endCommonFunc;
			$this->beginDateFunc = $tokenStream->beginDateFunc;
			$this->endDateFunc = $tokenStream->endDateFunc;
		}
		catch (Exception $e) {
			return ("{(".$this->row.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
		}

		//Блок выполнения
		try{
			$this->D0 = 0;
			$this->GetChar();
			if($this->Type !== TokenStream::type_end_code) {
				$this->continueCode();
				$name = str_replace(self::LetterRus, self::LetterEng, $name_var);
				if(isset($name_var)) return toString1C($this->variable[$name]);
				//else return toString1C($this->D0);
			}	
			else return "\n Нет кода для выполнения \n";
		}
		catch (Exception $e) {
			$token = $this->tokens[$this->itoken-1];
    		return ("{(".$this->row.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
		}
	}
}


/**
* Запуск выполнения кода 1С и возврат результата
*
* @param string $buffer строка код для выполнения
* @param string $name_var имя переменной для вывода
*/
function runCode($buffer, $name_var=null){

	$stream = new CodeStream();
	return $stream->runCode($buffer, $name_var);
}


