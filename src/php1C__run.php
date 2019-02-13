<?php
/**
* Основной модуль для запуска кода 1С
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
require_once('php1C_common.php');

/**
* Класс обработки потока кода 1С
*
* Основной класс обработки кода 1С. Выполняет код 1С
*/
class CodeStream {

	//array of token
    public $tokenStream = null;
    public $tokens = null;
    private $itoken = 0;
 
    //array of variable
    public $variable = array();
    public $lvariable = null;
    private $inFunction = null;
    //массивы начала и имен аргументов функций
    private $beginFunction = array();
    private $argsFunction = array();
    
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
		
	const LetterRus = array('А','Б','В','Г','Д','Е','Ё' ,'Ж' ,'З','И','Й' ,'К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х' ,'Ц','Ч' ,'Ш' ,'Щ'  ,'Ъ','Ы','Ь','Э' ,'Ю' ,'Я' ,'а','б','в','г','д','е','ё' ,'ж'  ,'з','и','й', 'к','л','м','н','о','п','р','с','т','у','ф','х' ,'ц','ч','ш' ,'щ'  ,'ъ','ы','ь','э' ,'ю' ,'я');
	const LetterEng = array('A','B','V','G','D','E','JO','ZH','Z','I','JJ','K','L','M','N','O','P','R','S','T','U','F','KH','C','CH','SH','SHH','' ,'Y','' ,'EH','YU','YA','a','b','v','g','d','e','jo','zh','z','i','jj','k','l','m','n','o','p','r','s','t','u','f','kh','c','ch','sh','shh','' ,'y','' ,'eh','yu','ya');
	
	const MAX_ITERRATOR_IN_CIRCLE = 100500; //Держите себы в руках - надо же ограничивать бесконечные циклы
		
	/**
	* Обработать один токен
	*/
	private function GetChar(){

		//Для отладки
		//if($this->itoken >= count($this->tokens)) throw new Exception('Выход за пределы массива токенов, индекс='.$this->itoken);

		//Для отладки
		//echo $this->Look.' ';

		$token = $this->tokens[$this->itoken];
		$this->Type  = $token->type; 
		$this->Look  = $token->context; 
		$this->Index = $token->index;
		//$this->row   = $token->row;
		//$this->col   = $token->col;
		$this->itoken++;
	}

	/**
	* Ввернуть управление на позицию $pos
	*/
	private function setPosition($pos){
		$this->itoken = $pos;
		$this->GetChar();
	}

	/**
	* Проверка совпадения оператора
	*/
	private function MatchOper($subtype, $look='???'){
		if( $this->Type === TokenStream::type_operator && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else{
		  //$this->itoken;		
		  throw new Exception('Ожидается оператор '.$look);
	}
	}

	/**
	* Проверка совпадения ключевого слова
	*/
	private function MatchKeyword($subtype){
		if( $this->Type === TokenStream::type_keyword && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else{
			throw new Exception('Ожидается '.TokenStream::keywords['code'][$subtype]);
		}	
	}

	/**
	* Получить символ из перечисления символов СИМВОЛЫ
	*/
	private function getCharSymbol(){
		$this->GetChar();
		$this->MatchOper(TokenStream::oper_point, '.');
		if($this->Type === TokenStream::type_variable){
			switch ($this->Look) {
				case 'ВК'  :
				case 'CR'  : return chr(13);
				case 'ВТаб':
				case 'VTab': return chr(11);
				case 'НПП' : 
				case 'NBSp': return chr(160);
				case 'ПС'  :
				case 'LF'  : return char(10);
				case 'ПФ'  :
				case 'FF'  : return chr(12);
				case 'Таб'  : 
				case 'Tab'  : return chr(9);
				default:
					throw new Exception('Неопределенный символ '.$this->Look);
					break;
			}
		}
		else throw new Exception('Ожидается перечисление символ, а не '.$this->Look);
	}


	/**
	* Первичный выполнятор примитивных выражений 
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
				if($this->variable[$key] === null ){
					if($this->lvariable[$key] === null )throw new Exception('Не определена переменная '.$this->Look);
					else $this->D0 = $this->lvariable[$key];
				} 
		    	else $this->D0 = $this->variable[$key]; 
			}
			if ($this->Type === TokenStream::type_date) $this->D0 = Date1C($this->D0);
			if ($this->Type === TokenStream::type_number) $this->D0 = toNumber1C($this->D0);
			
			if($this->Type === TokenStream::type_keyword){
				switch ($this->Index) {
				 	case TokenStream::keyword_undefined:
				 		$this->D0 = null;
				 		break;
					case TokenStream::keyword_true: 
					    $this->D0 = true; 
					    break;
                    case TokenStream::keyword_false:
                        $this->D0 = false;
                        break;
                    //Специальные ключевые слова Символы
                    case TokenStream::keyword_chars:
                    	$this->D0 = $this->getCharSymbol();
                    	$this->GetChar();
                    	return;	    
				 } 
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
				$func = $this->tokenStream->functions1С['clear'][$this->Index];
			 	$this->D0 = $this->callFunction( null, $func, $this->Index);
			 	return;
			}
			if( $this->Type === TokenStream::type_extfunction){
				$func = str_replace(self::LetterRus, self::LetterEng, $this->Look);
				$this->D0 = $this->callFunction( null, $func);
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
		    		$func = $this->tokenStream->functions1С['clear'][$this->Index];
		    		$this->D0 = $this->callFunction( $this->D0, $func, $this->Index);
		    	}
		    	//свойства объекта	
				elseif($this->Type === TokenStream::type_variable) throw new Exception('Свойства объекта пока не работают '.$this->Look);
				else throw new Exception('Предполагается функция или свойство объекта '.$look);
			}
			//Обработка квдратных скобок
			if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_opensqbracket){
				$this->GetChar();
				$value = $this->Expression7();
				$this->D0 = $this->variable[$key]->GET($value);
				$this->MatchOper(TokenStream::oper_closesqbracket, ']');
			}	
		}	
	}

	//Выдать идентификатор типа по названию или индексу
	private function getNewType(){
		$index = $this->Index;
		$look = $this->Look;
		$arguments = array();
		$this->GetChar();
		if($this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_openbracket){
			$this->MatchOper(TokenStream::oper_openbracket, '(');
			$notfirst = false;
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($notfirst){
					if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
					$this->GetChar();
				}
				else $notfirst = true;	
				$arguments[] = $this->Expression7();
			}
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		
		switch ($look) {
			case 'МАССИВ': return Array1C($arguments);
			case 'ФАЙЛ': return File1C($arguments);
			default: 
			    throw new Exception('Пока тип не определен '.$look);
			    break;
		}
	}

	/**
	* Обработка 7 уровней операторов, а точнее 6
	* работает со стэком varstack и возвращает результат в D0
	*
	* @param $level int уровень операции
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
					$this->Expression7(6);
					$this->D0 = or1C(array_pop($this->varstack), $this->D0);
				}
				break;
			default:
				break;
		}
		return $this->D0;
	}

	private function splitArguments(&$args){
		$this->GetChar();
		if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
			$args[] = $this->Expression7();
			
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
				$this->GetChar();
				$args[] = $this->Expression7();	
			}
		}
		$this->MatchOper(TokenStream::oper_closebracket, ')');
		return true;
	}

	/**
	* Разбор аргументов функции и выполнение кода функции 
	*
	* @param $context null or object - контекст вызова функции
	* @param $func string название функции
	* @param $index int индекс функции в таблице распознаных функций
	*/
	public function callFunction($context=null, $func, $index=-1){
		$args = array();
		$this->splitArguments($args);
		
		if( $index >= 0){
			//echo $index;
			if($index < $this->tokenStream->indexFuncCom){
				return callCommonFunction($context, $func, $args);
			}
			if($index < $this->tokenStream->indexFuncStr){
				return callStringFunction($func, $args);
			}
			if($index < $this->tokenStream->indexFuncDate){
				return callDateFunction($func, $args);
			}
			if($index < $this->tokenStream->indexFuncColl){
				return callCollectionFunction($context, $func, $args);
			}
			throw new Exception("Неизвестный модуль для вызова функции ".$func); //."и ".$index);		
		}
		$key = array_key_exists($func, $this->beginFunction);
		if( $key === true ){
			//вызов функции определенной в модуле
			$lvariable = $this->lvariable;
			$this->lvariable = array_combine($this->argsFunction[$func], $args);
			if($this->lvariable === false) throw new Exception("Неправильное количество аргументов функции ");
			$startpos = $this->itoken-1;
			$this->itoken = $this->beginFunction[$func]-1;
			$this->GetChar();
			$this->continueCode(TokenStream::keyword_function);
			$this->lvariable = $lvariable;
			$this->itoken = $startpos;
			$this->GetChar();
			//unset($lvariable);
		}
		else throw new Exception("Неизвестная функция ".$func); // ."и ".$index);
	}

	/*
	** Проверка на выполнение кода внутри конструкции if then ... 
	**
	** $handle - token_type(TokenStream) обрабатываемая структура кода 
	*/
	private function isIfOperation($handle){
		if($handle === TokenStream::keyword_then && 
			 ($this->Index === TokenStream::keyword_if || $this->Index === TokenStream::keyword_elseif || $this->Index === TokenStream::keyword_else || $this->Index === TokenStream::keyword_endif)) return true;
		return false;
	}

	/*
	** Проверка на выполнение кода внутри конструкции while for ... 
	**
	** $handle - token_type(TokenStream) обрабатываемая структура кода 
	*/
	private function isCircleOperation($handle){
		if( ($handle === TokenStream::keyword_circle && $this->Index === TokenStream::keyword_endcircle) || $this->Index === TokenStream::keyword_while  || $this->Index === TokenStream::keyword_for) return true;	
		return false;
	}

	/*
	** Проверка на описание функции или процедуры... 
	**
	** $handle - token_function(TokenStream) обрабатываемая структура кода 
	*/
	private function isSubFuncOperation($handle){
		if( $handle === TokenStream::keyword_function && ($this->Index === TokenStream::keyword_endfunction) || $this->Index === TokenStream::keyword_endprocedure) return true;	
		return false;
	}

	/*
	** Основная функция выполнения кода php 
	**
	** $handle - token_type(TokenStream) конструкция внутри которой работает код(keyword_circle, keyword_then)
	** $skip   - bool флаг невыполнения кода и пропуска 
	** $done   - bool Флаг выполения условия в конструкциях ИначеЕсли Иначе, в 1С исполняется только первое по условию
	*/
	public function continueCode($handle=-1, $skip= false, $done=false){	

		//основной цикл обработки токенов
		while($this->Type !== TokenStream::type_end_code){

			//Проверка на необходимость выполнения кода
			if( !$skip || ($skip && $this->Type===TokenStream::type_keyword && 
				           	( $this->isIfOperation($handle) || $this->isCircleOperation($handle) || $this->isSubFuncOperation($handle) )))
			{
				switch ($this->Type) {
					case TokenStream::type_newline: 
						$this->GetChar();
						break;
					//Пустые операторы	
					case TokenStream::type_operator:
						if($this->Index === TokenStream::oper_semicolon) $this->GetChar(); 
						else throw new Exception('Неопознанный оператор '.$this->Look);
						break;
					case TokenStream::type_variable:
						$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
						$this->GetChar();
						if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal){
					 		//Оператор присвоения переменной
					 		$this->GetChar();
							$value = $this->Expression7();
							if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_semicolon){
								if($this->inFunction) $this->lvariable[$key] = $value;
								else $this->variable[$key] = $value;
								$this->MatchOper(TokenStream::oper_semicolon, ';');
							}
							else throw new Exception('Ожидается ;');
						}
						else throw new Exception('Неизвестный оператор после переменной ');
						break;
					case TokenStream::type_function:
					case TokenStream::type_extfunction:
						$this->Expression7();
						//echo 'type='.$this->Type.';look='.$this->Look;
						//$this->MatchOper(TokenStream::oper_semicolon, ';');
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
						 		if($skip) $this->continueCode(TokenStream::keyword_then, true, true);
						 		else{
						 			$key = $this->Expression7();
						 			$this->MatchKeyword(TokenStream::keyword_then);
						 			$this->continueCode(TokenStream::keyword_then, !$key, $key);
								}
						 		break;
						 	case TokenStream::keyword_elseif:
						 		if($handle === TokenStream::keyword_then){
						 			$this->MatchKeyword(TokenStream::keyword_elseif);
						 			if($done) $skip = true;
						 			else{
							 				$key = $this->Expression7();
							 				$this->MatchKeyword(TokenStream::keyword_then);
							 				if($key){
							 					$skip = false;
							 					$done = true;
							 				}
							 		}
							 	}
								else throw new Exception('Ожидается конструкция Если ... Тогда');
								break;
						 	case TokenStream::keyword_else:
						  		if($handle === TokenStream::keyword_then){ 
						 			$this->MatchKeyword(TokenStream::keyword_else);
						 			if($done!==$skip) $skip = !$skip;
						 			$done = true;
						 		}	
						 		else throw new Exception('Ожидается конструкция Если ... Тогда(ИначеЕсли)');
						 		break;
						 	case TokenStream::keyword_endif:
						 		if($handle === TokenStream::keyword_then){
						 			$this->MatchKeyword(TokenStream::keyword_endif);
						 			$this->MatchOper(TokenStream::oper_semicolon, ';');
						 			return true;
						 		}
						 		else throw new Exception('Ожидается конструкции Если ... Тогда(ИначеЕсли,Иначе)');
						 		break;
						 	//Циклы
						 	case TokenStream::keyword_while:
						 		$startpos = $this->itoken;
							 	$this->MatchKeyword(TokenStream::keyword_while);
							 	if(!$skip){
							 		$key = $this->Expression7();
							 		$this->MatchKeyword(TokenStream::keyword_circle);
							 		$it = 0;
							 		while($key){
						 				if($this->continueCode(TokenStream::keyword_circle, false)){
							 				$this->setPosition($startpos); //move back to code
							 				$key = $this->Expression7();	
							 			    $this->MatchKeyword(TokenStream::keyword_circle);
							 			}
						 				else $key = false;
						 				if(++$it > self::MAX_ITERRATOR_IN_CIRCLE) throw new Exception('Я отработал максимальное значение циклов '.self::MAX_ITERRATOR_IN_CIRCLE); 
						 			}
						 		}
						 		$this->continueCode(TokenStream::keyword_circle, true);
						 		break;
						 	case TokenStream::keyword_for:
						 		$this->MatchKeyword(TokenStream::keyword_for);
						 		if(!$skip){	
							 		//Пока только шаблона Для перем=
							 		if($this->Type !== TokenStream::type_variable) 
							 			throw new Exception('Ожидается имя переменной');
							 		$iterator = str_replace(self::LetterRus, self::LetterEng, $this->Look);
									$this->GetChar();
									if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal ){
										$this->GetChar();
										$value = $this->Expression7();
						 				$this->lvariable[$iterator] = $value;
									}
									else throw new Exception('Ожидается символ =');
									$startpos = $this->itoken;
									$this->MatchKeyword(TokenStream::keyword_to);
							 		$key = $this->lvariable[$iterator] <= $this->Expression7();
							 		$this->MatchKeyword(TokenStream::keyword_circle);
							 		$it = 0;
						 			while($key){
						 				if($this->continueCode(TokenStream::keyword_circle, false)){
							 				$this->setPosition($startpos); //move back to code
							 				$this->lvariable[$iterator]++;
							 				$key = $this->lvariable[$iterator] <= $this->Expression7();	
							 			    $this->MatchKeyword(TokenStream::keyword_circle);
						 			    }
						 				else $key = false;
						 				if(++$it > self::MAX_ITERRATOR_IN_CIRCLE) throw new Exception('Я отработал максимальное значение циклов '.self::MAX_ITERRATOR_IN_CIRCLE); 
						 			}
					 			}
							 	$this->continueCode(TokenStream::keyword_circle, true);
								break;	
						 	case TokenStream::keyword_endcircle:
						 		if($handle === TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_endcircle);
						 			$this->MatchOper(TokenStream::oper_semicolon,';');
						 			return true;	
						 		}
						 		else throw new Exception('Ожидается начало конструкции Пока(Для) Цикл');
						 		break;
						 	case TokenStream::keyword_break:
						 		if($handle === TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_break);
						 			return false; 	
						 		}
						 		throw new Exception('Оператор Прервать (Break) может употребляться только внутри цикла');	
						 		break;	
						 	case TokenStream::keyword_continue:
						 		if($handle===TokenStream::keyword_circle){
						 			$this->MatchKeyword(TokenStream::keyword_continue);
						 			return true;
						 		}
						 		throw new Exception('Оператор Продолжить (Continue) может употребляться только внутри цикла');	
						 		break;
						 	//Объявление переменных ПЕРЕМ	
						 	case TokenStream::keyword_var:
						 		$this->GetChar();
						 		if($this->Type === TokenStream::type_variable){
						 			$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
						 			$this->GetChar();
						 			$this->MatchOper(TokenStream::oper_semicolon, ';'); 
									$this->variable[$key] = null;
								}
						 		else throw new Exception('Ожидается имя переменной');
							 	break;
							 	//Разбор описание функции или процедуры	
							 	case TokenStream::keyword_function:
							 	case TokenStream::keyword_procedure:
							 		$this->GetChar();
							 		if($this->Type === TokenStream::type_extfunction){
							 			$func = str_replace(self::LetterRus, self::LetterEng, $this->Look);
							 			if($skip) throw new Exception('Вложенных функций не допускается');
							 			//TODO инициализация переменных функции в массив $this->argsFunction[$func]
							 			$this->inFunction = $func;
							 			$this->argsFunction[$func] = array();
							 			$this->GetChar();
							 			if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
											
											if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается переменная функции или процедуры'.$this->Look);
											$this->argsFunction[$func][] = str_replace(self::LetterRus, self::LetterEng, $this->Look);
											$this->GetChar();
											while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
												if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
												$this->GetChar();
												if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается переменная функции или процедуры'.$this->Look);
												$this->argsFunction[$func][] = str_replace(self::LetterRus, self::LetterEng, $this->Look);
												$this->GetChar();
											}
										}
										$this->MatchOper(TokenStream::oper_closebracket, ')');
										$this->beginFunction[$func] = $this->itoken;
							 			$this->continueCode(TokenStream::keyword_function, true, true);
							 			//unset($func);
									}
							 		else throw new Exception('Ожидается название функции или процедуры');
							 		break;
							 	case TokenStream::keyword_return:
							 		if(!$skip){
							 			$this->inFunction = null;
							 			return $this->D0;
							 		} 
							  	 	break;	
							 	case TokenStream::keyword_endfunction:
							 	case TokenStream::keyword_endprocedure:
							 		$this->GetChar();
							 		$this->inFunction = null;
							 		if($skip) return;
							 		else return $this->D0;
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
			}else{ 
				//Пропуск выполнения кода для циклов и конструкций Если
				//echo '{'; 
				$this->GetChar(); 
				//echo '}';
			}
		}
		switch ($handle) {
			case TokenStream::keyword_then:
			throw new Exception("Ожидается ключевое слово 'КонецЕсли'('EndIf')");
			break;
			case TokenStream::keyword_circle:
			throw new Exception("Ожидается ключевое слово 'КонецЦикла'");
			break;	
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
			$this->tokenStream = new TokenStream($buffer);
			$this->tokenStream->CodeToTokens();
			$this->tokens = &$this->tokenStream->tokens;
		}
		catch (Exception $e) {
			return ("{(".$this->tokenStream->row.",".$this->tokenStream->col.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
		}

		//Блок выполнения
		try{
			$this->D0 = 0;
			$this->GetChar();
			if($this->Type !== TokenStream::type_end_code) {
				$this->continueCode();
				$name = str_replace(self::LetterRus, self::LetterEng, $name_var);
				if(isset($name_var)) return toString1C($this->variable[$name]);
			}	
			else return "\n Нет кода для выполнения \n";
		}
		catch (Exception $e) {
			$token = $this->tokens[$this->itoken-1];
    		return ("{(".$token->row.",".$token->col.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
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


