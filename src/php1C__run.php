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

namespace php1C;

use Exception;
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
    
    //current token
	private $Type  = 0;
	private $Look  = '';
	private $Index = -1;

	//run code
	private $varstack = array();
	private $D0 = '';
	
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
		$this->itoken++;
	}

	/**
	* Ввернуть управление на позицию $pos
	*
	* @param $pos int индекс в массиве токенов
	*/
	private function setPosition($pos){
		$this->itoken = $pos;
		$this->GetChar();
	}

	/**
	* Проверка совпадения оператора
	*
	* @param $subtype TokenStream::const индекс операции
	* @param $look string error::const строковое представление операции
	*/
	private function MatchOper($subtype, $look='???'){
		if( $this->Type === TokenStream::type_operator && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else  throw new Exception('Ожидается оператор '.$look);
	}

	/**
	* Проверка совпадения ключевого слова
	*
	* @param $subtype TokenStream::const индекс ключевого слова
	*/
	private function MatchKeyword($subtype){
		if( $this->Type === TokenStream::type_keyword && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else throw new Exception('Ожидается '.TokenStream::keywords['code'][$subtype]);
	}

	/**
	* Получить символ из перечисления символов СИМВОЛЫ
	*/
	private function getCharSymbol(){
		$this->GetChar();
		$this->MatchOper(TokenStream::oper_point, '.');
		if($this->Type === TokenStream::type_variable){
			if( TokenStream::fEnglishVariable )
				switch ($this->Look) {
					case 'VK'  : //
					case 'CR'  : return chr(13);
					case 'VTab': //
					case 'VTab': return chr(11);
					case 'NPP' : //
					case 'NBSP': return chr(160);
					case 'PS'  : //''
					case 'LF'  : return chr(10);
					case 'PF'  : //
					case 'FF'  : return chr(12);
					case 'Tab'  : //Т
					case 'TAB'  : return chr(9);
				}	
			else	
				switch ($this->Look) {
					case 'ВК'  : //
					case 'CR'  : return chr(13);
					case 'ВТаб': //
					case 'VTab': return chr(11);
					case 'НПП' : //
					case 'NBSP': return chr(160);
					case 'ПС'  : //''
					case 'LF'  : return chr(10);
					case 'ПФ'  : //
					case 'FF'  : return chr(12);
					case 'Таб'  : //Т
					case 'TAB'  : return chr(9);
				}
			new Exception('Неопределенный символ '.$this->Look);
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
		//Обработка ?(,,) 
		elseif( $this->Type === TokenStream::type_operator  && $this->Index === TokenStream::oper_question){
			$this->GetChar();
			$this->MatchOper(TokenStream::oper_openbracket);
			$condition = $this->Expression7();
			$this->MatchOper(TokenStream::oper_comma);
			$first = $this->Expression7();
			$this->MatchOper(TokenStream::oper_comma);
			$second = $this->Expression7();
			if( $condition ) $this->D0 = $first;
			else $this->D0 = $second;
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		else{
			
			$this->D0 = $this->Look;
			if($this->Type === TokenStream::type_variable){
				$this->D0 = $this->getContext($this->D0); 
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
				$func = $this->tokenStream->functions1С['php'][$this->Index];
				$this->D0 = $this->callFunction( null, $func, $this->Index);
			 	return;
			}
			if( $this->Type === TokenStream::type_extfunction){
				$func = str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $this->Look);
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
	*
	*
	* @param $type TokenStream::const тип предыдущего токена
	* @param $look string тектовое представление предыдущего токена
	* @param $index TokenStream::const индекс предыдущего токена
	*/
	private function ForwardOperation($type, $look, $index=-1){
		if($type === TokenStream::type_operator){
			//Унарный минус
			if($index === TokenStream::oper_minus){
				$this->Factor();
				$this->D0 = - $this->D0;
			}elseif($index === TokenStream::oper_plus) {
				$this->Factor();
			}elseif($index === TokenStream::oper_not) {
				$this->Factor();
				$this->D0 = !($this->D0);	
			}
			//Оператор Новый и тип
			elseif($index === TokenStream::oper_new) {
				if( $this->Type === TokenStream::type_identification){
					$this->D0 =  $this->getNewType();
				} 
				else throw new Exception('Ожидается идентификатор типа, а не '.$this->Look);
			}
			elseif( $this->Type === TokenStream::type_operator && ( $index === TokenStream::oper_mult || $index === TokenStream::oper_div )){
				throw new Exception('Двойной оператор ');	
			}	
		}
		elseif($type === TokenStream::type_variable){
			//Обработка свойств и функций объекта
			$this->D0 = $this->getProperty($look);
		}	
	}

	/**
	*  Получение свойств или функциий у объекта в цикла [] или .
	*
	* @param $look string имя переменной контекта
	* @param $lastkey string последнее свойство выбираемое в строке 
	* @param $lastcontext any последний контект выбираемый в строке
	*/
	private function getProperty($look, &$lastkey=null, &$lastcontext=null){
		$context = $this->getContext($look);
	    while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_point || $this->Index === TokenStream::oper_opensqbracket) ){
		
	    	$lastcontext = $context;

			//Обработка квадратных скобок
			if($this->Index === TokenStream::oper_opensqbracket){
				$this->GetChar();
				$lastkey = $this->Expression7();
				$context = $context->GET($lastkey);
				$this->MatchOper(TokenStream::oper_closesqbracket, ']');
			}
			//Обработка точки
			else{
		    	$this->GetChar();
		    	//функции объекта
		    	if( $this->Type === TokenStream::type_function ){
		    		$func = $this->tokenStream->functions1С['php'][$this->Index];
		    		$context = $this->callFunction( $context, $func, $this->Index);		    		
		    	}
		    	//свойства объекта	
				elseif($this->Type === TokenStream::type_variable){
					$lastkey = $this->Look;
					$args = array( 0 => $lastkey);
					$context = callCollectionFunction($context, 'Get(', $args);
					$this->GetChar();
				}	
				else throw new Exception('Предполагается функция или свойство '.$this->Look.' объекта '.$look);
			}
		}
		return $context;
	} 

	/**
	* Выдать идентификатор типа по названию или индексу
	*/
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
		
		//Построитель типов
		if($index < $this->tokenStream->indexTypesColl){
			return callCollectionType($this->tokenStream->identypes['php'][$index], $arguments);
		}
		throw new Exception('Пока тип не определен '.$look);
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

	/**
	* Возвращает в параметре $args массив аргументов функций
	*
	* @param $args array outout массив переменных функции
	* @return true
	*/
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
	* Получение текущего контекста по ключу переменной 
	*
	* @param $key string имя переменной контекста
	* @return any текущий контекст по ключу или D0
	*/
	private function getContext($key=''){
		if(!empty($key)){
			if($this->variable[$key] === null && !empty($this->inFunction)){
				if($this->lvariable[$key] === null )throw new Exception('Не определена  переменная '.$key);
				else return $this->lvariable[$key];
			} 
		    else return $this->variable[$key];
	    }
	    else return $this->D0;
	}

	/**
	* Установка переменных по ключу (глобальной или локальной)
	*
	* @param $key string имя переменной контекста
	* @param $value any значение переменной контекста
	*/
	private function setContext($key, $value=null){
		if( isset($this->inFunction) && array_key_exists($key, $this->variable) === false ){
			$this->lvariable[$key] = $value;
		} 
	    else $this->variable[$key] = $value;
	}

	/**
	* Получение свойства объекта через точку 
	*
	* @param $key string имя переменной контекста
	* @param $func string имя название функции
	* @param $index int индекс функции в таблице распознаных функций
	*/
	private function callProperty($key){

		$context = $this->getContext($key);
		if(isset($context)){
			$args = array( 0 => $this->Look);
		   	$this->D0 = callCollectionFunction($context, 'Get(', $args);
		   	return $this->D0;
	    }
	    else throw new Exception("Неизвестный контекст вызова свойства ".$this->Look);
	}

	/**
	* Разбор аргументов функции и выполнение кода функции 
	*
	* @param $context string имя переменной контекста
	* @param $func string название функции
	* @param $index int индекс функции в таблице распознаных функций
	*/
	public function callFunction($context=null, $func, $index=-1){
		$args = array();
		$this->splitArguments($args);
		
		if( $index >= 0){
			if(is_string($context)) $context = $this->getContext($context);
			
	    	if($index < $this->tokenStream->indexFuncCom){
				return callCommonFunction($context, $func, $args);
			}
			if($index < $this->tokenStream->indexFuncStr){
				return callStringFunction($func, $args);
			}
			if($index < $this->tokenStream->indexFuncNum){
				return callNumberFunction($func, $args);
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
			$this->inFunction = $func;
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
	** Проверка на выполнение кода внутри описания функции или процедуры... 
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
					//Работа с переменными	
					case TokenStream::type_variable:
						$key = $this->Look;
						$context = null;
						$func = '';
						$this->GetChar();
						if( $this->Type === TokenStream::type_operator){

							if($this->Index === TokenStream::oper_point){
								$this->getProperty($key, $func, $context);
							}

						    if($this->Index === TokenStream::oper_equal){
						 		//Оператор присвоения переменной
						 		$this->GetChar();
								$value = $this->Expression7();
								if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_semicolon){
									if(isset($context))  $context->SET($func, $value);
									else{
										$this->setContext($key, $value);	
									} 
									$this->MatchOper(TokenStream::oper_semicolon, ';');
								}
								else throw new Exception('Ожидается ;');
							}
						}
						else throw new Exception('Неизвестный не оператор после переменной ');
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
							 		
							 		//Шаблона Для каждого перем ИЗ Чего-то Цикл ... КонецЦикла;
							 		if($this->Type !== TokenStream::keyword_foreach){
							 			$this->GetChar();
							 			if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается имя переменной');
							 			$iterator = $this->Look;
							 			$this->GetChar();
							 			$this->MatchKeyword(TokenStream::keyword_from);
							 			if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается имя переменной');
							 			$array = $this->getContext($this->Look);
							 			if(!method_exists($array, 'toArray'))
							 				throw new Exception('Нельзя из этого элемента выбрать итерации');
							 			$array = $array->toArray();
							 			$this->setContext($iterator, current($array));
							 			//$this->lvariable[] = ;
							 			$this->GetChar();	
							 			$this->MatchKeyword(TokenStream::keyword_circle);
							 			$startpos = $this->itoken;
							 			$it = 0;
							 			while($this->getContext($iterator)!==false){
							 				if($this->continueCode(TokenStream::keyword_circle, false)){
								 				$this->setPosition($startpos); //move back to code
								 				$this->setContext($iterator, next($array));
								 		    }
							 				else $this->setContext($iterator, false);
							 				if(++$it > self::MAX_ITERRATOR_IN_CIRCLE) throw new Exception('Я отработал максимальное значение циклов '.self::MAX_ITERRATOR_IN_CIRCLE);	
							 			}
							 		}
							 		//Шаблона Для перем=Нач По Кон Цикл ... КонецЦикла;
							 		else{
								 		if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается имя переменной');
								 		$iterator = $this->Look;
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
						 			$key = $this->Look;
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
						 			$func = str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $this->Look);
						 			if($skip) throw new Exception('Вложенных функций не допускается');
						 			//инициализация переменных функции в массив $this->argsFunction[$func]
						 			$this->argsFunction[$func] = array();
						 			$this->GetChar();
						 			if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
										
										//разбор переменных
										if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается переменная функции или процедуры'.$this->Look);
										$this->argsFunction[$func][] = $this->Look;
										$this->GetChar();
										while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
											if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
											$this->GetChar();
											if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается переменная функции или процедуры'.$this->Look);
											$this->argsFunction[$func][] = $this->Look;
											$this->GetChar();
										}
									}
									$this->MatchOper(TokenStream::oper_closebracket, ')');
									$this->beginFunction[$func] = $this->itoken;
						 			$this->continueCode(TokenStream::keyword_function, true, true);
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
						 	case TokenStream::keyword_export:
						 		$this->GetChar();
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
				//Пропуск выполнения кода для циклов и конструкций Если Иначе
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
				$name = strtoupper($name_var); 
				if( TokenStream::fEnglishVariable ) $name = str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $name);
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


