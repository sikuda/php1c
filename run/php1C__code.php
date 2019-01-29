<?php
/**
* Дополнительный модуль для получения кода 1С
* 
* Модуль для работы с 1С 
* Преобразование кода в код php
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
	* Первичный преобразователь кода
	*/
	private function Factor(){
		
		//Обработка скобок - первый приоритет
		if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_openbracket){
			$this->GetChar();
			$this->code = $this->Expression7();
			$this->MatchOper(TokenStream::oper_closebracket, ')');
		}
		else{
			
			$this->code = $this->Look;
			if($this->Type === TokenStream::type_variable){
				$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
				$this->code = "$".$key;	 
			}	
			if ($this->Type === TokenStream::type_date) $this->code = 'Date1C("'.$this->Look.'")';
						
			if($this->Type === TokenStream::type_keyword){
				if($this->Index == TokenStream::keyword_undefined) $this->code = null;
				if($this->Index == TokenStream::keyword_true) $this->code = "true";
				if($this->Index == TokenStream::keyword_false) $this->code = "false";
			}

			if( $this->Type === TokenStream::type_function ){
				$func = $this->functionsPHP_Common[$this->Index];
			 	$this->code = $this->splitFunction( null, $func, $this->Index);
			 	return;
			}
			if( $this->Type === TokenStream::type_extfunction){
				$func = str_replace(self::LetterRus, self::LetterEng, $this->Look);
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
			}
			//Оператор Новый и тип
			elseif($index === TokenStream::oper_new) {
				if( $this->Type === TokenStream::type_identification){
					$this->code = $this->identypes['codePHP'][$this->Index].'()';
					$this->GetChar();
				} 
				else throw new Exception('Ожидается идентификатор типа');
			}
			elseif( $this->Type === TokenStream::type_operator && ( $index === TokenStream::oper_mult || $index === TokenStream::oper_div )){
				throw new Exception('Двойной оператор '.$this->getOperator($this->code));	
			}	
		}
		elseif($type === TokenStream::type_variable){
			$key = str_replace(self::LetterRus, self::LetterEng, $look);
			$this->code = "$".$key;
			//Обработка свойств и функций объекта
		    while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_point){
		    	$this->GetChar();
		    	//функции объекта
		    	if( $this->Type === TokenStream::type_function ){
		    		$func = $this->functionsPHP_Common[$this->Index];
		    		$this->splitFunction( $this->code, $func, $this->Index);
		    	}
		    	//свойства объекта	
				elseif($this->Type === TokenStream::type_variable) throw new Exception('Свойства объекта пока не работают '.$this->Look);
				elseif($this->Type === TokenStream::type_number) throw new Exception('Неправильная константа типа число '.$this->Look);
				else throw new Exception('Предполагается функция объекта '.$this->Look);
			}	
		    //}
		}	
	}

    //Выдать идентификатор типа по названию или индексу
	private function getNewType(){
		switch ($this->Index) {
			case 'МАССИВ': return 'Array1C()';
			case 'ФАЙЛ': return 'File1C()';
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
						$this->code = 'mul1C('.array_pop($this->codestack).','.$this->code.')';
					}else{
						$this->code = 'div1C('.array_pop($this->codestack).','.$this->code.')';
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
						$this->code = 'add1C('.array_pop($this->codestack).','.$this->code.')';
					}else{
						$this->code = 'sub1C('.array_pop($this->codestack).','.$this->code.')';
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
							$this->code = 'less1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_lessequal:
							$this->code = 'lessequal1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_equal:
							$this->code = 'equal1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_notequal:
							$this->code = 'notequal1C('.array_pop($this->codestack).','.$this->code.')';
							break;	
						case TokenStream::oper_more:
							$this->code = 'more1C('.array_pop($this->codestack).','.$this->code.')';
							break;
						case TokenStream::oper_morequal:
							$this->code = 'morequal1C('.array_pop($this->codestack).','.$this->code.')';
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
					$this->code = 'and1C('.array_pop($this->codestack).','.$this->code.')';
				}
				break;
			case 7: //ИЛИ
				while( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_or){
					array_push($this->codestack, $this->code);
					$this->GetChar();
					$this->Expression7(6);
					$this->code = 'or1C('.array_pop($this->codestack).','.$this->code.')';
				}
				break;
			default:
				break;
		}
		return $this->code; 
	}

	/**
	* Получение кода функции с аргументами
	*/
	public function splitFunction($context=null, $func, $index=-1){
		$args = ''; 
		$this->GetChar();
				
		if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket){
			$this->code = $this->Expression7();
			$args .= $this->code;
			$this->code = '';
				
			while( $this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_closebracket ){
				if($this->Type !== TokenStream::type_operator || $this->Index !== TokenStream::oper_comma) throw new Exception('Ожидается запятая , ');
				$this->GetChar();
				$this->code = $this->Expression7();
				$args .= $this->code;
				$this->code = '';	
			}
		}
		$this->MatchOper(TokenStream::oper_closebracket, ')');
		$this->MatchOper(TokenStream::oper_semicolon, ';');
					
		if($this->beginCommonFunc < $index && $this->endCommonFunc > $index){
			$array = functionsPHP_Com();
			return $array[$index].$args.");";
		}
		
		if($this->beginDateFunc < $index && $this->endDateFunc > $index){
			$array = functionsPHP_Date();
			return $array[$index-1-$this->beginDateFunc].$args.");";	
		}
		return $key.$args.");";	
	}

	/*
	** Make code php 
	**
	** $handle - expected keyword to handle
	** $other - old result of handle
	*/
	public function continueCode($handle=-1, $other=false){	

		while($this->Type !== TokenStream::type_end_code){
			switch ($this->Type) {
				case TokenStream::type_newline: 
					$this->codePHP .= "\n";
					$this->GetChar();
					break;
				case TokenStream::type_variable:
					$key = str_replace(self::LetterRus, self::LetterEng, $this->Look);
					array_push($this->codestack, $key);
					$this->GetChar();
					if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_equal){
				 		//Оператор присвоения переменной
				 		$this->GetChar();
						$value = $this->Expression7();
						if( $this->Type === TokenStream::type_operator && $this->Index === TokenStream::oper_semicolon){
							$this->code = '$'.$key."=".$value.';';
							$this->MatchOper(TokenStream::oper_semicolon, ';');
							$this->codePHP .= $this->code;
						}
						elseif ($this->Type === TokenStream::type_end_code) {
								$this->variable[$key] = $value;
						}
						else throw new Exception('Ожидается ;');
					}
					else throw new Exception('Неизвестный оператор после переменной '.$key);
					break;
				case TokenStream::type_function:
				case TokenStream::type_extfunction:
					$this->codePHP .= $this->Expression7();
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
					 		$iterator = str_replace(self::LetterRus, self::LetterEng, $this->Look);
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
							//$this->code = '';
							$this->MatchKeyword(TokenStream::keyword_circle);
					 		$this->codePHP .= $this->code . '$'.$iterator.'++){';
					 		//$this->code = '';
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
					 	default:
					 		throw new Exception('Нет соответствия ключевому слову '.TokenStream::keywords['code'][$this->Index]);
							break;	
					}
					break;
				default:
					throw new Exception('Неопознанный оператор '.$this->Look);
					break;
			}
		}
	}

	/**
	* Начало обработки получения кода PHP
	*
	* @param string $buffer строка код для преобразоания
	*/
	function makeCode($buffer){

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
			$this->code = '';
			$this->codePHP = '';
			$this->GetChar();
			if($this->Type !== TokenStream::type_end_code){
				$this->continueCode();
				return $this->codePHP;
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
* Запуск получения кода PHP
*
* @param string $buffer строка код для преобразоания
* @param string $name_var имя переменной для вывода результата выполнения кода
*/
function makeCode($buffer, $name_var=null){
	$stream = new CodeStream();
	try{
		$result = $stream->makeCode($buffer);
	}
	catch (Exception $e) {
		return ("{(".$this->row.")}: ".$e->getMessage()."\n"); //стиль ошибки 1С
	}
	if($name_var!==null){
	 	$output_array = array();
	 	$result = "Пока не реализовано";
	 	//$name = str_replace(self::LetterRus, self::LetterEng, $name_var);
	 	//$result .= 'echo '.$name;
	 	//exec($result, $output_array);
	 	//return $output_array[0];
	 	return $result;
	}
	else return $result;
}



