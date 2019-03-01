<?php
/**
* Дополнительный модуль для получения кода PHP из 1С
* 
* Модуль для работы с 1С 
* Преобразование кода в код php
* 
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/

namespace php1C;
use Exception;

/**
* Подключаем разбора и базовый модуль работы с 1С
*/
require_once( 'php1C__tokens.php');
require_once( 'php1C_common.php');

/**
* Класс обработки потока кода 1С
*
* Основной класс обработки кода 1С. Преобразует код в код php
*/
class CodeStream {

    //array of token
    public $tokenStream = null;
    public $tokens = array();
    private $itoken = 0;

    //current token
	private $Type  = 0;
	private $Look  = '';
	private $Index = -1;

	//make code
	private $codePHP = '';
	private $code = '';
	private $codestack = array();

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
			throw new Exception('Ожидается -'.TokenStream::keywords['code'][$subtype]);
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
				case 'CR'  : return 'chr(13)';
				case 'ВТаб': //
				case 'VTAB': return 'chr(11)';
				case 'НПП' : //
				case 'NBSP': return 'chr(160)';
				case 'ПС'  : //''
				case 'LF'  : return 'chr(10)';
				case 'ПФ'  : //
				case 'FF'  : return 'chr(12)';
				case 'Таб' : //
				case 'TAB' : return 'chr(9)';
				default:
					throw new Exception('Неопределенный символ '.$this->Look);
					break;
			}
		}
		else throw new Exception('Ожидается перечисление символ, а не '.$this->Look);
	}

	/**
	* Первичный преобразователь кода
	*/
	private function Factor(){
		
		//Обработка скобок и унарных операций 
		if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_openbracket ){
			// $index = $this->Index;
			// $code = $this->code; 
			// $this->GetChar();
			// $this->code = $this->Expression7();
			// switch ($index) {
			// 	case TokenStream::oper_openbracket:
			// 		$this->MatchOper(TokenStream::oper_closebracket, ')');
			// 		break;
			// 	default:
			// 		throw new Exception('Неизвестный унарный оператор '.$this->getOperator($code));
			// 		break;
			// }
			//&& $this->Index === TokenStream::oper_openbracket){
			$this->GetChar();
			$this->code = $this->Expression7();
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		//Обработка ?(,,) 
		elseif( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_question){
			$this->GetChar();
			$this->MatchOper(TokenStream::oper_openbracket);
			$condition = $this->Expression7();
			$this->MatchOper(TokenStream::oper_comma);
			$first = $this->Expression7();
			$this->MatchOper(TokenStream::oper_comma);
			$second = $this->Expression7();
			$this->code = $condition.' ? '.$first.' : '.$second;
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		else{
			
			//$this->codePHP .= 'f'.$this->Look.'-'.$this->Type.'f';
			$this->code = $this->Look;
			if($this->Type === TokenStream::type_variable){
				$key = $this->Look;
				$this->code = "$".$key; 
			}	
			if ($this->Type === TokenStream::type_string) $this->code = '"'.$this->Look.'"';
			if ($this->Type === TokenStream::type_date) $this->code = 'php1C\Date1C("'.$this->Look.'")';
						
			if($this->Type === TokenStream::type_keyword){
				switch ($this->Index) {
				 	case TokenStream::keyword_undefined:
				 		$this->code = 'null';
				 		break;
					case TokenStream::keyword_true: 
					    $this->code = 'true'; 
					    break;
                    case TokenStream::keyword_false:
                        $this->code = 'false';
                        break;
                    //Специальные ключевые слова Символы
                    case TokenStream::keyword_chars:
                    	$this->code = $this->getCharSymbol();
                    	$this->GetChar();
                    	return;	    
				 } 
			}
			if( $this->Type === TokenStream::type_function ){
				$this->code = $this->splitFunction( null, $this->Look, $this->Index);
			 	return;
			}
			if( $this->Type === TokenStream::type_extfunction){
				$func = str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $this->Look);
				$this->code = $this->splitFunction( null, $func);
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
				$this->code = '-'.$this->code;
			}elseif($index === TokenStream::oper_plus) {
				$this->Factor();
			}elseif($index === TokenStream::oper_not) {
				$this->Factor();
				$this->code = '!'.$this->code;
			}
			//Оператор Новый и тип
			elseif($index === TokenStream::oper_new) {
				if( $this->Type === TokenStream::type_identification){
					$this->code = $this->getNewType();
					//$this->GetChar();
				} 
				else throw new Exception('Ожидается идентификатор типа, а не '.$this->Look);
			}
			elseif( $this->Type === TokenStream::type_operator && ( $index === TokenStream::oper_mult || $index === TokenStream::oper_div )){
				throw new Exception('Двойной оператор '.$this->getOperator($this->code));	
			}	
		}
		elseif($type === TokenStream::type_variable){
			$key = $look;
			$this->code = "$".$key;
			//Обработка свойств и функций объекта
		    while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_point || $this->Index === TokenStream::oper_opensqbracket) ){
				
		    	//Обработка квадратных скобок
				if( $this->Index === TokenStream::oper_opensqbracket){
					$this->GetChar();
					$this->code = '$'.$key.'->GET('.$this->Expression7().')';
					$this->MatchOper(TokenStream::oper_closesqbracket, ']');
				}
				//Обработка точки
				else{
			    	$this->GetChar();
			    	//функции объекта
			    	if( $this->Type === TokenStream::type_function ){
			    		//$this->codePHP .= '+'.$this->Look.$this->Index.'+';
			    		$this->code = $this->code.'->'.$this->splitFunction( $key, $this->Look, $this->Index);
			    		return;
			    	}
			    	//функции объекта неопределенная
			    	elseif( $this->Type === TokenStream::type_extfunction ){
			    		//$this->codePHP .= '+'.$this->Look.$this->Index.'+';
			    		$this->code = $this->code.'->'.$this->splitFunction( $key, $this->Look, $this->Index);
			    		return;
			    	}
			    	//свойства объекта	
					elseif($this->Type === TokenStream::type_variable){
						$this->code = $this->code.'->Get('.$this->Look.')';
						$this->GetChar();
					}	
					elseif($this->Type === TokenStream::type_number) throw new Exception('Неправильная константа типа число '.$this->Look);
					else throw new Exception('Предполагается функция объекта '.$this->Look);
				}
			}
					
		}	
	}

    /**
    * Выдать код нового объекта по индексу со всеми параметрами
	*/
	private function getNewType(){
		//определяем параметры конструктора
		$index = $this->Index;
		$look = $this->Look;
		//$args = '(';
		//Количество переменных не определено - засовываем переменные в массив 
		$args = '(array(';
		$this->GetChar();
		if($this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_openbracket){
			$this->MatchOper(TokenStream::oper_openbracket, '(');
			$notfirst = false;
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($notfirst){
					if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
					$args .= ',';
					$this->GetChar();
				}
				else $notfirst = true;	
				$this->code = $this->Expression7();
				$args .= $this->code;
				$this->code = '';
			}
			$this->MatchOper(TokenStream::oper_closebracket, ')');	
		}
		$args .= '))';
		if($index>=0) return 'php1C\\'.$this->tokenStream->identypes['php'][$index].$args; 
		else throw new Exception('Пока тип не определен '.$look);
	}

	/**
	* Обработка 7 уровней операторов
	*/
	public function Expression7($level=7){
		if($level > 2) $this->Expression7($level-1);
		switch ($level) {
			// case 1: // Базовые операции
			// 	$this->Factor();
			// 	break;
			case 2: // Базовые операции
				$this->Factor();
				break;
			case 3: // Умножение или деление (* /)
		        while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_mult || $this->Index === TokenStream::oper_div)){
		        	array_push($this->codestack, $this->code);
		        	$index = $this->Index;
		        	$this->GetChar();
					$this->Expression7(2);
					if( $index === TokenStream::oper_mult ){
						$this->code = 'php1C\mul1C('.array_pop($this->codestack).','.$this->code.')';
					}else{
						$this->code = 'php1C\div1C('.array_pop($this->codestack).','.$this->code.')';
					}
				}
				break;
			case 4: //Сложение или вычитание (+ -)
				while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::oper_plus || $this->Index === TokenStream::oper_minus)){
					array_push($this->codestack, $this->code);
					$index = $this->Index;
					$this->GetChar();
					$this->Expression7(3);
					if( $index === TokenStream::oper_plus ){
						$this->code = 'php1C\add1C('.array_pop($this->codestack).','.$this->code.')';
					}else{
						$this->code = 'php1C\sub1C('.array_pop($this->codestack).','.$this->code.')';
					}	
				}
				break;
			case 5: //Больше меньше или равно (< <= = <> > >=)
				while( $this->Type === TokenStream::type_operator && 
					   ($this->Index === TokenStream::oper_less || $this->Index === TokenStream::oper_lessequal || $this->Index === TokenStream::oper_equal || $this->Index === TokenStream::oper_notequal || $this->Index === TokenStream::oper_more || $this->Index === TokenStream::oper_morequal)){
					array_push($this->codestack, $this->code);
					$index = $this->Index; 
					$this->GetChar();
					$this->Expression7(4);
					switch ($index) {
						case TokenStream::oper_less:
							$this->code = 'php1C\less1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_lessequal:
							$this->code = 'php1C\lessequal1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_equal:
							$this->code = 'php1C\equal1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_notequal:
							$this->code = 'php1C\notequal1C('.array_pop($this->codestack).','.$this->code.')';
							break;	
						case TokenStream::oper_more:
							$this->code = 'php1C\more1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_morequal:
							$this->code = 'php1C\morequal1C('.array_pop($this->codestack).','.$this->code.')';
							break;		
						default:
						 	throw new Exception('Операция не принадлежит этому уровню '.$this->Look);
						 	break;
					}
				}
				break;
			case 6: //И
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_and){
					array_push($this->codestack, $this->code);
					$this->GetChar();
					$this->Expression7(5);
					$this->code = 'php1C\and1C('.array_pop($this->codestack).','.$this->code.')';
				}
				break;
			case 7: //ИЛИ
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_or){
					array_push($this->codestack, $this->code);
					$this->GetChar();
					$this->Expression7(6);
					$this->code = 'php1C\or1C('.array_pop($this->codestack).','.$this->code.')';
				}
				break;
			default:
				break;
		}
		return $this->code; 
	}

	/**
	* Разбор аргументов функции и ее возврат строки вызова функции
	*
	* @param $context string имя переменной контекста( типа Массив.Добавить())
	* @param $func string название функции
	* @param $index int индекс функции в таблице распознаных функций
	*/
	public function splitFunction($context=null, $func, $index=-1){
		$args = ''; 
		//$args = 'array(';
		$this->GetChar();
		//разбор аргументов функции		
		if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
			$this->code = $this->Expression7();
			$args .= $this->code;
			$this->code = '';
				
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
				$this->GetChar();
				$this->code = $this->Expression7();
				$args .= ','.$this->code;
				$this->code = '';	
			}
		}
		//$args .= ')';
		$this->MatchOper(TokenStream::oper_closebracket, ')');
		
		if($index!=-1){
			$func = $this->tokenStream->functions1С['php'][$index];
			//$this->codePHP .= 's'.$context.'->'.$func.'s';
			switch ($func) {
				//обработка совпадения функций
				case 'Date(': return 'php1C\Date1C('.$args.')';
				case 'StrLen(': return 'php1C\StrLength('.$args.')';
				default:
					if(isset($context)) return $this->tokenStream->functions1С['php'][$index].$args.")";
					else return 'php1C\\'.$this->tokenStream->functions1С['php'][$index].$args.")";
				break;
			}
		} 
		else return $func.'('.$args.")";
	}

	/*
	** Основная функция получения кода на php 
	**
	** $handle - token_type(TokenStream) ожидаемое ключевое слово
	** $other  - устаревший параметр
	*/
	public function continueCode($handle=-1, $other=false){	

		while($this->Type !== TokenStream::type_end_code){
			switch ($this->Type) {
				case TokenStream::type_newline: 
					$this->codePHP .= "\n";
					$this->GetChar();
					break;
				//Пустые операторы	
				case TokenStream::type_operator:
						if($this->Index === TokenStream::oper_semicolon){
							$this->codePHP .= ';';
							$this->GetChar(); 
						} 
						else throw new Exception('Неопознанный оператор '.$this->Look);
						break;
				//Переменная - присвоение или функция			
				case TokenStream::type_variable:
					$key = $this->Look;
					$context = '$'.$key;
					$curr = '';
					$this->GetChar();
					if( $this->Type === TokenStream::type_operator){

						while($this->Index === TokenStream::oper_point){
							if(!empty($curr)) $context .= '->'.$curr;
							$this->GetChar();
							//функция объекта
							if( $this->Type === TokenStream::type_function ){
								$curr = $this->splitFunction($key, $this->Look, $this->Index);   		
							}
	    					//свойства объекта	
							elseif($this->Type === TokenStream::type_variable){
								$curr = $this->Look;
								$this->GetChar();
							}
							$key = ''; //переходи к текущему контексту
						}	

						if($this->Index === TokenStream::oper_equal){
					 		//Оператор присвоения переменной
					 		$this->GetChar();
					 		$value = $this->Expression7();
							//$this->codePHP .= 'v'.$value.'v';
							if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_semicolon){
								if(!empty($curr)){
									$this->codePHP .= $context.'->SET('.$curr.', '.$value.')';
								}
								else{ 
									$this->code = '$'.$key."=".$value.';';
									$this->MatchOper(TokenStream::oper_semicolon, ';');
									$this->codePHP .= $this->code;
								}
							}
							//elseif ($this->Type === TokenStream::type_end_code) {
							//		$this->variable[$key] = $value;
							//}
							else throw new Exception('Ожидается ;');
						}
						// elseif ($this->Index === TokenStream::oper_point) {
						// 	$this->codePHP .= '$'.$key.'->';
						// 	$this->GetChar();
						// 	if($this->Type === TokenStream::type_function){
						// 		$this->codePHP .=$this->splitFunction($key, $this->Look, $this->Index);
						// 	}
						// 	else throw new Exception('Ожидается функция поле точки');
						// }
						// else throw new Exception('Неизвестный оператор после переменной ');
						else $this->codePHP .= $context.'->'.$curr;
					}	
					else throw new Exception('Неизвестный не оператор после переменной '.$key);
					break;
				case TokenStream::type_function:
				case TokenStream::type_extfunction:
					$this->codePHP .= $this->Expression7();
					$this->MatchOper(TokenStream::oper_semicolon, ';');
					$this->codePHP .= ";";
					break;
				case TokenStream::type_comments:
					$this->codePHP .= $this->Look;
					$this->GetChar();
					break;			
				//Ключевые слова
				case TokenStream::type_keyword:
					switch($this->Index){
						//Если Тогда Иначе
					 	case TokenStream::keyword_if:
					 		$this->MatchKeyword(TokenStream::keyword_if);
					 		$this->codePHP .= "if(";
					 		$this->code = $this->Expression7();
					 		$this->MatchKeyword(TokenStream::keyword_then);
					 		$this->codePHP .= $this->code . "){";
					 		//$this->code = '';
					 		$this->continueCode(TokenStream::keyword_then);
					 		break;
					 	case TokenStream::keyword_elseif:
					 		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
					 			$this->MatchKeyword(TokenStream::keyword_elseif);
					 			$this->codePHP .= "elseif(";
					 			$key = $this->Expression7();
						 		$this->MatchKeyword(TokenStream::keyword_then);
						 		$this->codePHP .= $this->code . "){";
					 		    //$this->code = '';
					 			return $this->continueCode(TokenStream::keyword_elseif);
						 	}
							else throw new Exception('Ожидается конструкция Если ... Тогда');
					 	case TokenStream::keyword_else:
					  		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
					 			$this->MatchKeyword(TokenStream::keyword_else);
					 			$this->codePHP .= "}else{";
					 			return $this->continueCode(TokenStream::keyword_else);
					 		}	
					 		else throw new Exception('Ожидается конструкция Если ... Тогда(ИначеЕсли)');
					 	case TokenStream::keyword_endif:
					 		if($handle===TokenStream::keyword_then || $handle === TokenStream::keyword_elseif || $handle===TokenStream::keyword_else){
					 			$this->MatchKeyword(TokenStream::keyword_endif);
					 			$this->MatchOper(TokenStream::oper_semicolon, ';');
					 			$this->codePHP .= "}";
					 			return;
					 		}
					 		else throw new Exception('Ожидается конструкции Если ... Тогда(ИначеЕсли,Иначе)');
					 		break;
					 	//Циклы
					 	case TokenStream::keyword_while:
					 		$this->MatchKeyword(TokenStream::keyword_while);
					 		$this->codePHP .= "while(";
					 		$this->code = $this->Expression7();
					 		$this->MatchKeyword(TokenStream::keyword_circle);
					 		$this->codePHP .= $this->code . "){";
					 		//$this->code = '';
					 		$this->continueCode(TokenStream::keyword_circle);
					 		break;
					 		//Для перем=.. по .. цикл КонецЦикла;
					 	case TokenStream::keyword_for:
					 		$this->MatchKeyword(TokenStream::keyword_for);
					 		$this->codePHP .= "for(";
					 		//Пока только шаблона Для перем=
					 		if($this->Type !== TokenStream::type_variable) throw new Exception('Ожидается имя переменной');
					 		$iterator = $this->Look;
							$this->GetChar();
							if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal ){
								$this->codePHP .= '$'.$iterator.'=';
								$this->GetChar();
								$this->code = $this->Expression7();
				 			}
							else throw new Exception('Ожидается символ =');
							$this->codePHP .= $this->code . ';';
							$this->MatchKeyword(TokenStream::keyword_to);
					 		$this->code = $this->Expression7();
					 		$this->codePHP .= '$'.$iterator.'<='.$this->code. ';';
							$this->MatchKeyword(TokenStream::keyword_circle);
					 		$this->codePHP .= $this->code . '$'.$iterator.'++){';
					 		$this->continueCode(TokenStream::keyword_circle);
					 		break;	
					 	case TokenStream::keyword_endcircle:
					 		if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword(TokenStream::keyword_endcircle);
					 			$this->MatchOper(TokenStream::oper_semicolon, ';');
					 			$this->codePHP .= "}";
					 			return;	
					 		}
					 		else throw new Exception('Ожидается конструкции Пока(Для) ... Цикл');
					 		break;
					 	case TokenStream::keyword_break:
					 		if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword(TokenStream::keyword_break);
					 			$this->codePHP .= 'break;';
					 		}	
					 		break;	
					 	case TokenStream::keyword_continue:
					 		if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword(TokenStream::keyword_continue);
					 			$this->codePHP .= 'continue;';
					  		}	
					 		break;
					 	case TokenStream::keyword_var:
					 		$this->GetChar();
					 		if($this->Type === TokenStream::type_variable){ 
					 			$key = $this->Look;
					 			$this->GetChar();
					 			$this->MatchOper(TokenStream::oper_semicolon, ';');
								$this->codePHP .= '$'.$key.' = null;';
							}
					 		else throw new Exception('Ожидается имя переменной');
					 		break;
					 	case TokenStream::keyword_function:
					 	case TokenStream::keyword_procedure:
					 		$this->GetChar();
					 		if($this->Type === TokenStream::type_extfunction){
					 			$key = str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $this->Look);
								//$this->GetChar();
								$this->codePHP .= 'function '.$this->splitFunction(null, $key, -1).'{';
							}
					 		else throw new Exception('Ожидается название функции или процедуры');
					 		break;
					 	case TokenStream::keyword_return:
					 		$this->codePHP .= 'return ';
					 		$this->GetChar();
					 		//не пустой возврат
					 		if($this->Type !==  TokenStream::type_operator || $this->Index !== TokenStream::oper_semicolon) $this->codePHP .= $this->Expression7().';';
							$this->MatchOper(TokenStream::oper_semicolon, ';');
							$this->codePHP .= ';';
							break;	
					 	case TokenStream::keyword_endfunction:
					 	case TokenStream::keyword_endprocedure:
					 		$this->GetChar();
					 		$this->codePHP .= '}';
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
					throw new Exception('Неопознанный символ '.$this->Look);
					break;
			}
		}
	}

	/**
	* Начало обработки получения кода PHP
	*
	* @param string $buffer строка код для преобразоания
	*/
	function makeCode($buffer, $name_var=null){

		//Блок разбора по токеном
		try{
			//php1C'.'\\'.'
			if(isset($name_var)) $buffer .= chr(10).'Сообщить('.$name_var.');';

			$this->tokenStream = new TokenStream($buffer);
			$this->tokenStream->CodeToTokens();
			$this->tokens = &$this->tokenStream->tokens;
		}
		catch (Exception $e) {
			return ("{(".$this->tokenStream->row.",".$this->tokenStream->col.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
		}

		//Блок выполнения
		try{
			$this->code = '';
			$this->codePHP = '';
			$this->GetChar();
			if($this->Type !== TokenStream::type_end_code){

				$this->continueCode();

				$name = strtoupper(str_replace(TokenStream::LetterRus, TokenStream::LetterEng, $name_var));
				if(isset($name_var)){
					eval($this->codePHP);
				 	return '';
				}
				else return $this->codePHP;
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
* Запуск получения кода PHP
*
* @param string $buffer строка код для преобразоания
* @param string $name_var имя переменной для вывода результата выполнения кода
*/
function makeCode($buffer, $name_var=null){
	$stream = new CodeStream();
	$result = $stream->makeCode($buffer, $name_var);
	return $result;
}



