<?php
/**
*
* Модуль для работы с 1С 
* Преобразование кода в код php
* 
* @author  sikuda@yandex.ru
* @version 0.3
*/
namespace Sikuda\Php1c;
use Exception;
require_once( 'php1C__tokens.php');
require_once( 'php1C_common.php');

/**
* Класс обработки потока кода 1С
*
* Основной класс обработки кода 1С. Преобразует код в код php
*/
class CodeStream {

    //array of token
    public array $tokens  = array();
    private int $i_token = 0;

    //current token
	private int $Type  = 0;
	private string $Look  = '';
	private int $Index = -1;

	//make code
	private string $codePHP = '';
	private string $code = '';
	private array $codeStack = array();

	//Идентификаторы типов - type identification
	private array $keywords;
	//Идентификаторы функций - function identification
	private array $functions1C;

	/*
	** Вставляем текущий кусочек в результат 
	*/
	private function pushCode($someCode){
		$this->codePHP .= $someCode;	
	}

	/**
	* Обработать один токен, пока из массива
	*/
	private function GetChar(){

		//Для отладки
		//if($this->i_token >= count($this->tokens)) throw new Exception('Выход за пределы массива токенов, индекс='.$this->i_token);

		$token = $this->tokens[$this->i_token];
		while($token->type === TokenStream::type_newline) {
		  	$token = $this->tokens[++$this->i_token];
		  	$this->code .= chr(10);
		}
		while($token->type === TokenStream::type_space) {
		  	$token = $this->tokens[++$this->i_token];
		  	$this->code .= ' ';
		}
		while($token->type === TokenStream::type_tablespace) {
		  	$token = $this->tokens[++$this->i_token];
		  	$this->code .= chr(9);
		}
		$this->Type = $token->type;
		$this->Look = $token->context; 

		//echo '|'.$token->type.'v'.$token->context.'|';

		$this->Index = $token->index;
		$this->i_token++;
	}

    /**
     * Проверка совпадения оператора
     *
     * @param $subtype TokenStream::const индекс операции
     * @param $look string error::const строковое представление операции
     * @throws Exception
     */
	private function MatchOperation($subtype,
                                    string $look = '???')
    {
		if( $this->Type === TokenStream::type_operator && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else throw new Exception(php1C_error_ExpectedOperator.$look);
	}

    /**
     * @throws Exception
     */
    private function MatchOperation2($subtype, $code="", $look = '???')
    {
        if( $this->Type === TokenStream::type_operator && $this->Index === $subtype){
            $this->code = $code;
            $this->GetChar();
            $this->pushCode($this->code);
            $this->code  = '';
        }
        else throw new Exception(php1C_error_ExpectedOperator.$look);
    }

    /**
     * Проверка совпадения ключевого слова
     *
     * @param $subtype TokenStream::const индекс ключевого слова
     * @throws Exception
     */
	private function MatchKeyword($subtype){
		if( $this->Type === TokenStream::type_keyword && $this->Index === $subtype){ 
			$this->GetChar();
		}
		else{
			throw new Exception(php1C_error_Expected.php1C_Keywords[$subtype]);
		}	
	}

    /**
     * @throws Exception
     */
    private function MatchKeyword2($subtype, $code=""){
        if( $this->Type === TokenStream::type_keyword && $this->Index === $subtype){
            $this->code = $code;
            $this->GetChar();
            $this->pushCode($this->code);
            $this->code  = '';
        }
        else{
            throw new Exception(php1C_error_Expected.php1C_Keywords[$subtype]);
        }
    }

    /**
     * Получить символ из перечисления символов СИМВОЛЫ
     * @throws Exception
     */
	private function getCharSymbol(): string
    {
		$this->GetChar();
		$this->MatchOperation(TokenStream::operation_point, '.');
		if($this->Type === TokenStream::type_variable){
            switch ($this->Look) {
                case 'ВК':
                case 'CR':
                case 'VK':
                    return 'chr(13)';
                case 'ВТаб':
                case 'VTab':
                    return 'chr(11)';
                case 'НПП' :
                case 'NPP' :
                case 'NBSP':
                    return 'chr(160)';
                case 'ПС':
                case 'PS':
                case 'LF':
                    return 'chr(10)';
                case 'ПФ':
                case 'FF'  :
                    return 'chr(12)';
                case 'Таб':
                case 'PF':
                case 'Tab':
                     return 'chr(9)';
            }
            throw new Exception(php1C_error_NonSymbol.$this->Look);
		}
		else throw new Exception(php1C_error_NonSymbol2.$this->Look);
	}

    /**
     * Первичный преобразователь кода
     * @throws Exception
     */
	private function Factor(){
		
		//Обработка скобок и унарных операций 
		if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_open_bracket ){
			$this->GetChar();
			$this->code = $this->Expression7();
			$this->MatchOperation(TokenStream::operation_close_bracket, ')');
		}
		//Обработка оператора ?(if,value1,value2)
		elseif( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_question){
			$this->GetChar();
			$this->MatchOperation(TokenStream::operation_open_bracket);
			$condition = $this->Expression7();
			$this->MatchOperation(TokenStream::operation_comma);
			$first = $this->Expression7();
			$this->MatchOperation(TokenStream::operation_comma);
			$second = $this->Expression7();
			$this->code = $condition.' ? '.$first.' : '.$second;
			$this->MatchOperation(TokenStream::operation_close_bracket, ')');
		}
		else{
			
			//$this->codePHP .= 'f'.$this->Look.'-'.$this->Type.'f';
			//$this->pushCode($this->code);
			$this->code = $this->Look;
			if($this->Type === TokenStream::type_variable || $this->Type === TokenStream::type_identification){
                $key = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
				$this->code = "$".$key; 
			}	
			if ($this->Type === TokenStream::type_string) $this->code = '"'.$this->Look.'"';
			if ($this->Type === TokenStream::type_date) $this->code = 'php1C\Date1C("'.$this->Look.'")';
            //fPrecision1C==true
			//if($this->Type=== TokenStream::type_number) $this->code = 'php1C\Number1C("'.$this->Look.'")';
            if($this->Type=== TokenStream::type_number) $this->code = $this->Look;

			if($this->Type === TokenStream::type_keyword){
				switch ($this->Index) {
					case TokenStream::keyword_val:
						$this->code = '';
						return;
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
			if( $this->Type === TokenStream::type_extinction){
				//$func = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
				$func = $this->Look;
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
     *
     * @param $type TokenStream::const тип предыдущего токена
     * @param $look string представление предыдущего токена
     * @param $index int индекс предыдущего токена
     * @throws Exception
     */
	private function ForwardOperation($type, string $look, int $index = -1)
    {
		if($type === TokenStream::type_operator){
			//Унарный минус
			if($index === TokenStream::operation_minus){
				$this->Factor();
				$this->code = '-'.$this->code;
			}elseif($index === TokenStream::operation_plus) {
				$this->Factor();
			}elseif($index === TokenStream::operation_not) {
				$this->Factor();
				$this->code = '!'.$this->code;
			}
			//Оператор Новый и тип
			elseif($index === TokenStream::operation_new) {
				if( $this->Type === TokenStream::type_identification){
					$this->code = $this->getNewType();
					//$this->GetChar();
				} 
				else throw new Exception(php1C_error_ExpectedIdentType.$this->Look);
			}
			elseif( $this->Type === TokenStream::type_operator && ( $index === TokenStream::operation_multi || $index === TokenStream::operation_div )){
				throw new Exception(php1C_error_DoubleOper.$this->code);
			}	
		}
		elseif($type === TokenStream::type_variable || $type === TokenStream::type_identification){
			//$key = $look;
            $key = str_replace(php1C_LetterLng, php1C_LetterEng, $look);
			$this->code = "$".$key;
			//Обработка свойств и функций объекта
		    while( $this->Type === TokenStream::type_operator &&
                ($this->Index === TokenStream::operation_point || $this->Index === TokenStream::operation_open_sq_bracket) ){
				
		    	//Обработка квадратных скобок
				if( $this->Index === TokenStream::operation_open_sq_bracket){
					$this->GetChar();
					$this->code = '$'.$key.'->GET('.$this->Expression7().')';
                    $this->MatchOperation(TokenStream::operation_close_sq_bracket, ']');
				}
				//Обработка точки - свойств или функций
				else{
			    	$this->GetChar();
			    	//функции объекта
			    	if( $this->Type === TokenStream::type_function ){
			    		$this->code = $this->code.'->'.$this->splitFunction( $key, $this->Look, $this->Index);
			    	}
			    	//функции объекта неопределенная
			    	elseif( $this->Type === TokenStream::type_extinction ){
			    		$this->code = $this->code.'->'.$this->splitFunction( $key, $this->Look, $this->Index);
			    	}
			    	//свойства объекта	
					elseif($this->Type === TokenStream::type_variable){
                        $look = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
						$this->code = $this->code.'->Get("'.$look.'")';
						$this->GetChar();
					}	
					elseif($this->Type === TokenStream::type_number) throw new Exception(php1C_error_BadConstTypeNumber.$this->Look);
					else throw new Exception(php1C_error_ExpectedFunctionObject.$this->Look);
				}
			}
					
		}	
	}

    /**
     * Выдать код нового объекта по индексу со всеми параметрами
     * @throws Exception
     */
	private function getNewType(): string
    {
		//определяем параметры конструктора
		$index = $this->Index;
		$look = $this->Look;
		//$args = '(';
		//Количество переменных не определено - засовываем переменные в массив 
		$args = '(array(';
		$this->GetChar();
		if($this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_open_bracket){
			$this->MatchOperation(TokenStream::operation_open_bracket, '(');
			$fNoFirst = false;
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::operation_close_bracket ){
				if($fNoFirst){
					if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::operation_comma) throw new Exception(php1C_error_ExpectedComma);
					$args .= ',';
					$this->GetChar();
				}
				else $fNoFirst = true;
				$this->code = $this->Expression7();
				$args .= $this->code;
				$this->code = '';
			}
			$this->MatchOperation(TokenStream::operation_close_bracket, ')');
		}
		$args .= '))';
		if($index>=0) return 'php1C\\'.$this->keywords['php'][$index].$args;
		else throw new Exception(php1C_error_UndefineType.$look);
	}

    /**
     * Обработка 7 уровней операторов
     * @throws Exception
     */
	public function Expression7($level=7): string
    {
		if($level > 2) $this->Expression7($level-1);
		switch ($level) {
			case 2: // Базовые операции
				$this->Factor();
				break;
			case 3: // Умножение или деление (* /)
		        while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::operation_multi || $this->Index === TokenStream::operation_div)){
		        	$this->codeStack[] = $this->code;
		        	$index = $this->Index;
		        	$this->GetChar();
					$this->Expression7(2);
					if( $index === TokenStream::operation_multi ){
						$this->code = 'php1C\mul1C('.array_pop($this->codeStack).','.$this->code.')';
					}else{
						$this->code = 'php1C\div1C('.array_pop($this->codeStack).','.$this->code.')';
					}
				}
				break;
			case 4: //Сложение или вычитание (+ -)
				while( $this->Type === TokenStream::type_operator && ($this->Index === TokenStream::operation_plus || $this->Index === TokenStream::operation_minus)){
					$this->codeStack[] = $this->code;
					$index = $this->Index;
					$this->GetChar();
					$this->Expression7(3);
					if( $index === TokenStream::operation_plus ){
						$this->code = 'php1C\add1C('.array_pop($this->codeStack).','.$this->code.')';
					}else{
						$this->code = 'php1C\sub1C('.array_pop($this->codeStack).','.$this->code.')';
					}	
				}
				break;
			case 5: //Больше меньше или равно (< <= = <> > >=)
				while( $this->Type === TokenStream::type_operator && 
					   ($this->Index === TokenStream::operation_less || $this->Index === TokenStream::operation_less_equal
                           || $this->Index === TokenStream::operation_equal || $this->Index === TokenStream::operation_notequal
                           || $this->Index === TokenStream::operation_more || $this->Index === TokenStream::operation_more_equal)){
					$this->codeStack[] = $this->code;
					$index = $this->Index; 
					$this->GetChar();
					$this->Expression7(4);
					switch ($index) {
						case TokenStream::operation_less:
							$this->code = 'php1C\less1C('.array_pop($this->codeStack).','.$this->code.')';
							break;
						case TokenStream::operation_less_equal:
							$this->code = 'php1C\less_equal1C('.array_pop($this->codeStack).','.$this->code.')';
							break;
						case TokenStream::operation_equal:
							$this->code = 'php1C\equal1C('.array_pop($this->codeStack).','.$this->code.')';
							break;
						case TokenStream::operation_notequal:
							$this->code = 'php1C\not_equal1C('.array_pop($this->codeStack).','.$this->code.')';
							break;	
						case TokenStream::operation_more:
							$this->code = 'php1C\more1C('.array_pop($this->codeStack).','.$this->code.')';
							break;
						case TokenStream::operation_more_equal:
							$this->code = 'php1C\more_equal1C('.array_pop($this->codeStack).','.$this->code.')';
							break;		
						default:
						 	throw new Exception(php1C_error_OperBadLevel.$this->Look);
					}
				}
                break;
			case 6: //И
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_and){
					$this->codeStack[] = $this->code;
					$this->GetChar();
					$this->Expression7(5);
					$this->code = 'php1C\and1C('.array_pop($this->codeStack).','.$this->code.')';
				}
				break;
			case 7: //ИЛИ
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_or){
					$this->codeStack[] = $this->code;
					$this->GetChar();
					$this->Expression7(6);
					$this->code = 'php1C\or1C('.array_pop($this->codeStack).','.$this->code.')';
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
     * @param $context - имя переменной контекста
     * @param $func string название функции
     * @param $index int индекс функции в таблице распознанных функций
     * @throws Exception
     */
	public function splitFunction($context, string $func, int $index=-1): string
    {
		$args = ''; 
		$this->GetChar();
		//разбор аргументов функции		
		if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::operation_close_bracket){
			$this->code = $this->Expression7();
			if($this->Type === TokenStream::type_keyword && $this->Index === TokenStream::keyword_val){
				$this->GetChar();
				$this->code = $this->Expression7();
			} 
			$args .= $this->code;
			$this->code = '';
				
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::operation_close_bracket ){
				if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::operation_comma) throw new Exception('Ожидается запятая , ');
				$this->GetChar();
				$this->code = $this->Expression7();
				if($this->Type === TokenStream::type_keyword && $this->Index === TokenStream::keyword_val){
					$this->GetChar();
					$this->code = $this->Expression7();
				} 
				$args .= ','.$this->code;
				$this->code = '';	
			}
		}
		//$args .= ')';
		$this->MatchOperation(TokenStream::operation_close_bracket, ')');
		
		if($index!=-1){
			$func = $this->functions1C['php'][$index];
			//$this->codePHP .= 's'.$context.'->'.$func.'s';
			switch ($func) {
				//обработка совпадения функций
				case 'Date(': return 'php1C\Date1C('.$args.')';
				case 'StrLen(': return 'php1C\StrLength('.$args.')';
				default:
					if(isset($context)) return $this->functions1C['php'][$index].$args.")";
					else return 'php1C\\'.$this->functions1C['php'][$index].$args.")";
			}
		} 
		else return $func.'('.$args.")";
	}

	/*
	** Основная функция получения кода на php 
	**
	** $handle - token_type(TokenStream) ожидаемое ключевое слово
	*/
    /**
     * @throws Exception
     */
    public function continueCode($handle=-1){

		while($this->Type !== TokenStream::type_end_code){
			switch ($this->Type) {
                case TokenStream::type_newline:
					$this->pushCode(chr(10));
					$this->GetChar();
					break;
                case TokenStream::type_tablespace:
                case TokenStream::type_space:
					$this->pushCode(chr(9));
					$this->GetChar();
					break;
                //Пустые операторы
				case TokenStream::type_operator:
                    $this->code = ';';
                    $this->MatchOperation(TokenStream::operation_semicolon, ';');
                    $this->pushCode($this->code);
					break;
				//Переменная или идентификатор - переменная + присвоение или функция
				case TokenStream::type_variable:
                case TokenStream::type_identification:
					$key = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
					$context = '$'.$key;
					$curr = '';
					$this->GetChar();
                    if( $this->Type === TokenStream::type_operator ){

                        //Обработка присвоения элемента массива
                        if( $this->Index === TokenStream::operation_open_sq_bracket){
                                $this->GetChar();
                                $curr = $this->Expression7();
                                $this->MatchOperation(TokenStream::operation_close_sq_bracket, ']');
                        }
                        else
                            while($this->Index === TokenStream::operation_point){
                                if(!empty($curr)) $context .= '->'.$curr;
                                $this->GetChar();
                                //функция объекта
                                if( $this->Type === TokenStream::type_function ){
                                    $curr = $this->splitFunction($key, $this->Look, $this->Index);
                                }
                                //свойства объекта
                                elseif($this->Type === TokenStream::type_variable){
                                    $curr = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
                                    $this->GetChar();
                                }
                                $key = ''; //переходи к текущему контексту
                            }

						if($this->Index === TokenStream::operation_equal){
					 		//Оператор присвоения переменной
					 		$this->GetChar();
					 		$value = $this->Expression7();
							//$this->codePHP .= 'v'.$value.'v';
							if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_semicolon){
								if($curr !== ''){
                                    if( substr($curr, 0, 1)=== '$')
									    $this->pushCode($context.'->SET('.$curr.', '.$value.')');
                                    else
                                        $this->pushCode($context.'->SET("'.$curr.'", '.$value.')');
								}
								else{
									$this->code = '$'.$key."=".$value.';';
									$this->MatchOperation(TokenStream::operation_semicolon, ';');
									$this->pushCode($this->code);
								}
							}
							else throw new Exception(php1C_error_Expected.';');
						}
						else $this->pushCode($context.'->'.$curr);
					}
					else throw new Exception(php1C_error_BadNonOperAfterVar.$key);
					break;
				case TokenStream::type_function:
				case TokenStream::type_extinction:
					$this->pushCode($this->Expression7());
					$this->MatchOperation(TokenStream::operation_semicolon, ';');
					$this->pushCode(';');
					break;
				case TokenStream::type_comments:
					$this->pushCode($this->Look);
					$this->GetChar();
					break;			
				//Ключевые слова
				case TokenStream::type_keyword:
					switch($this->Index){
						//Если Тогда Иначе
					 	case TokenStream::keyword_if:
					 		$this->MatchKeyword2(TokenStream::keyword_if, 'if(');
					 		//->pushCode('if(');
					 		$this->code = $this->Expression7();
					 		$this->MatchKeyword2(TokenStream::keyword_then, $this->code.'){');
					 		//$this->pushCode($this->code . '){');
					 		$this->continueCode(TokenStream::keyword_then);
					 		break;
					 	case TokenStream::keyword_elseif:
					 		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
					 			$this->MatchKeyword(TokenStream::keyword_elseif);
					 			$this->pushCode("elseif(");
					 			$this->Expression7();
                                $this->MatchKeyword(TokenStream::keyword_then);
						 		$this->pushCode($this->code . "){");
					 		    //$this->code = '';
					 			$this->continueCode(TokenStream::keyword_elseif);
						 	}
							else throw new Exception(php1C_error_ExpectedConstructionIfThen);
                            break;
                        case TokenStream::keyword_else:
					  		if($handle === TokenStream::keyword_then || $handle === TokenStream::keyword_elseif){
					 			$this->MatchKeyword(TokenStream::keyword_else);
					 			$this->pushCode("}else{");
					 			$this->continueCode(TokenStream::keyword_else);
					 		}	
					 		else throw new Exception(php1C_error_ExpectedConstructionIfThenElseIf);
                            break;
                        case TokenStream::keyword_endif:
					 		if($handle===TokenStream::keyword_then || $handle === TokenStream::keyword_elseif || $handle===TokenStream::keyword_else){
					 			$this->MatchKeyword2(TokenStream::keyword_endif, '}');
					 			$this->MatchOperation2(TokenStream::operation_semicolon, '', ';');
					 			//$this->pushCode("}");
					 		}
					 		else throw new Exception(php1C_error_ExpectedConstructionIfThenElseIf);
					 		break;
					 	//Циклы
					 	case TokenStream::keyword_while:
					 		$this->MatchKeyword2(TokenStream::keyword_while, 'while(');
					 		//$this->pushCode("while(");
					 		//$this->code = $this->Expression7();
					 		$this->MatchKeyword2(TokenStream::keyword_circle, $this->Expression7().'){');
					 		//$this->pushCode($this->code . "){");
					 		//$this->code = '';
					 		$this->continueCode(TokenStream::keyword_circle);
					 		break;
					 		//Для перем=value1 по value2 цикл КонецЦикла;
					 	case TokenStream::keyword_for:
					 		$this->MatchKeyword(TokenStream::keyword_for);
					 		if($this->Type == TokenStream::type_keyword && $this->Index == TokenStream::keyword_foreach){
					 			$this->GetChar();
					 			//Шаблона Для каждого перем ИЗ Чего-то Цикл ... КонецЦикла;
					 			if($this->Type !== TokenStream::type_variable) throw new Exception(php1C_error_ExpectedNameVar);
                                $iterator = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
					 			//$iterator = $this->Look;
					 			$this->GetChar();
							 	$this->MatchKeyword(TokenStream::keyword_from);
							 	if($this->Type !== TokenStream::type_variable) throw new Exception(php1C_error_ExpectedNameVar);
							 	$array = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
							 	$this->GetChar();
							 	$this->MatchKeyword(TokenStream::keyword_circle);
							 	$this->pushCode("foreach( $".$array."->toArray() as $".$iterator." ){");
                            }else{
								//Шаблона Для перем=Нач По Кон Цикл ... КонецЦикла;
								$this->pushCode('for(');
						 		if($this->Type !== TokenStream::type_variable) throw new Exception(php1C_error_ExpectedNameVar);
						 		$iterator = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
								$this->GetChar();
								if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::operation_equal ){
									$this->pushCode('$'.$iterator.'=');
									$this->GetChar();
									$this->code = $this->Expression7();
					 			}
								else throw new Exception(php1C_error_LostSymbol.'=');
								$this->pushCode($this->code . ';');
								$this->MatchKeyword(TokenStream::keyword_to);
						 		$this->code = $this->Expression7();
                                $this->code = '$'.$iterator.'<='.$this->code. ';';
                                $this->pushCode($this->code);
						 		$this->code = '$'.$iterator.'=php1C\add1C($'.$iterator.',1)){';
						 		$this->MatchKeyword(TokenStream::keyword_circle);
                                $this->pushCode($this->code);
                            }
                            $this->continueCode(TokenStream::keyword_circle);
                            break;
					 	case TokenStream::keyword_end_circle:
					 		//if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword2(TokenStream::keyword_end_circle, '}');
					 			$this->MatchOperation2(TokenStream::operation_semicolon,'', ';');
					 			//$this->pushCode("}");
					 			return;	
					 		//}
                            //break;
					 		//else throw new Exception(php1C_error_ExpectedConstructionWhileDo);
					 	case TokenStream::keyword_break:
					 		if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword(TokenStream::keyword_break);
					 			$this->pushCode('break;');
					 		}	
					 		break;	
					 	case TokenStream::keyword_continue:
					 		//if($handle===TokenStream::keyword_circle){
					 			$this->MatchKeyword(TokenStream::keyword_continue);
					 			$this->pushCode('continue;');
                                $this->MatchOperation(TokenStream::operation_semicolon, ';');
					  		//}
					 		break;
					 	case TokenStream::keyword_var:
					 		$this->GetChar();
					 		if($this->Type === TokenStream::type_variable){ 
					 			$key = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
					 			$this->GetChar();
					 			$this->MatchOperation(TokenStream::operation_semicolon, ';');
								$this->pushCode('$'.$key.' = null;');
							}
					 		else throw new Exception(php1C_error_ExpectedNameVar );
					 		break;
					 	case TokenStream::keyword_function:
					 	case TokenStream::keyword_procedure:
					 		$this->GetChar();
					 		if($this->Type === TokenStream::type_extinction){
					 			$key = str_replace(php1C_LetterLng, php1C_LetterEng, $this->Look);
								//$this->GetChar();
								$this->pushCode('function '.$this->splitFunction(null, $key, -1).'{');
							}
					 		else throw new Exception(php1C_error_ExpectedNameFunction);
					 		break;
					 	case TokenStream::keyword_return:
					 		$this->pushCode('return ');
					 		$this->GetChar();
					 		//не пустой возврат
					 		if($this->Type !==  TokenStream::type_operator || $this->Index !== TokenStream::operation_semicolon) $this->pushCode($this->Expression7().';');
							$this->MatchOperation(TokenStream::operation_semicolon, ';');
							$this->pushCode(';');
							break;	
					 	case TokenStream::keyword_end_function:
					 	case TokenStream::keyword_end_procedure:
					 		$this->GetChar();
					 		$this->pushCode('}');
					 		break;
					 	case TokenStream::keyword_export:
					 		$this->GetChar();
					 		break;		
					 	default:
					 		throw new Exception(php1C_error_NonKeyword.php1C_Keywords[$this->Index]);
					}
                    break;
                default:
					throw new Exception(php1C_error_UndefineOperator); //Подобно 1С никаких лишних $this->Look;
			}
		}
	}

	/**
	* Начало обработки получения кода PHP
	*
	* @param string $buffer строка код для преобразования
	*/
	function makeCode(string $buffer, $name_var=null){

		$tokenStream = new TokenStream($buffer);
		$resToken = $tokenStream->CodeToTokens();
		if ($resToken !== true) {
			return $resToken; //возврат ошибки разбора
		}
		$this->functions1C = $tokenStream->functions1C;
		$this->keywords = $tokenStream->idTypes;
		$this->tokens = $tokenStream->tokens;

		//var_dump($this->tokens);
		//return "";
		
		//Блок преобразования в код php
		try{
			$this->code = '';
			$this->codePHP = '';
			$this->GetChar();
			if($this->Type !== TokenStream::type_end_code){

				$this->continueCode();

				//Вывод результата переменной
				if(isset($name_var)){
					if(fEnglishVariable) $name_var = str_replace(php1C_LetterLng, php1C_LetterEng, $name_var);
					$name_var = mb_strtoupper($name_var);
                    eval($this->codePHP);
                    return ${$name_var};
				}
				else return $this->codePHP;
			}  
			else return ""; //стиль 1С нет ошибки
		}
		catch (Exception $e) {
			$token = $this->tokens[$this->i_token-1];
    		return (" {(".$token->row.",".$token->col.")}: ".$e->getMessage()); //стиль ошибки 1С
		}
 	}
}

/**
* Основная функция получения кода PHP
*
* @param string $buffer строка код для преобразования
* @param string|null $name_var имя переменной для вывода результата выполнения кода
*/
function makeCode(string $buffer, string $name_var = null){
	$stream = new CodeStream();
    return $stream->makeCode($buffer, $name_var);
}
