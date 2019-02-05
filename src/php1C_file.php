<?php
/**
* Модуль работы с файлами в 1С
* 
* Модуль для работы с файлами, записями в файлы и чтение из файлов
* 
* TO DO! 
*
* @author  sikuda admin@sikuda.ru
* @version 0.1
*/
//require_once( '.\php1C_date.php'); //todo

class File1C {
	private $fileName;
	function __construct($buffer = '') {
       if(is_string($buffer)) $this->fileName = $buffer;
    }

	/**
	* Получить короткое имя файла
	*
	* @return string 
	*/
	public function Name(){
		$pos = strrpos($this->fileName,'/');
		if($pos==false) return $this->fileName;
		return substr($this->fileName,$pos);	
	}

	/**
	* Получить короткое имя файла без расширения
	*
	* @return string 
	*/
	public function BaseName(){
		return strstr($this->Name(),'.', true);		
	}

	/**
	* Получить полное имя файла на сервере
	*
	* @return string 
	*/
	public function FullName(){
		return $this->fileName;		
	}

	/**
	* Получить полный путь на сервере
	*
	* @return string 
	*/
	public function Path(){
		return strstr($this->fileName,'.', true);		
	}

	/**
	* Получить расширение файла
	*
	* @return string 
	*/
	public function Extension(){
		return ltrim(strstr($this->fileName,'.'), '.');	;		
	}

	/**
	* Существует файл на сервере
	*
	* @return bool 
	*/
	public function Exist(){
		if(file_exists($this->fileName) == 1) return true;
		else return false;	
	}

	/**
	* Это файл на сервере
	*
	* @return bool 
	*/
	public function IsFile(){
		if(is_file($this->fileName)) return true;
		else return false;	
	}

	/**
	* Это директория на сервере
	*
	* @return bool 
	*/
	public function IsDirectory(){
		if(is_dir($this->fileName)) return true;
		else return false;		
	}

	//filemtime() 
	public function GetModificationTime(){
		return ''; //todo
	}
	public function GetModificationUniversalTime(){
		return ''; //todo	
	}
	//touch()
	public function SetModificationTime(){
		return ''; //todo		
	}
	public function SetModificationUniversalTime (){
		return ''; //todo			
	}
}

function File1C($name){
	return new File1C( $name);
}