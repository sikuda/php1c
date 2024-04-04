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
namespace php1C;
//require_once( 'php1C_date.php'); //todo


class File1C {
	private string $fileName;
	function __construct($buffer = '') {
       if(is_string($buffer)) $this->fileName = $buffer;
    }

	/**
	* Получить короткое имя файла
	*
	* @return string 
	*/
	public function Name(): string
    {
		$pos = strrpos($this->fileName,'/');
		if(!$pos) return $this->fileName;
		return substr($this->fileName,$pos);	
	}

	/**
	* Получить короткое имя файла без расширения
	*
	* @return string 
	*/
	public function BaseName(): string
    {
		return strstr($this->Name(),'.', true);		
	}

	/**
	* Получить полное имя файла на сервере
	*
	* @return string 
	*/
	public function FullName(): string
    {
		return $this->fileName;		
	}

	/**
	* Получить полный путь на сервере
	*
	* @return string 
	*/
	public function Path(): string
    {
		return strstr($this->fileName,'.', true);		
	}

	/**
	* Получить расширение файла
	*
	* @return string 
	*/
	public function Extension(): string
    {
		return ltrim(strstr($this->fileName,'.'), '.');
	}

	/**
	* Существует файл на сервере
	*
	* @return bool 
	*/
	public function Exist(): bool
    {
		if(file_exists($this->fileName) == 1) return true;
		else return false;	
	}

	/**
	* Это файл на сервере
	*
	* @return bool 
	*/
	public function IsFile(): bool
    {
		if(is_file($this->fileName)) return true;
		else return false;	
	}

	/**
	* Это директория на сервере
	*
	* @return bool 
	*/
	public function IsDirectory(): bool
    {
		if(is_dir($this->fileName)) return true;
		else return false;		
	}

	//filemtime() 
	public function GetModificationTime(): string
    {
		return ''; //todo
	}
	public function GetModificationUniversalTime(): string
    {
		return ''; //todo	
	}
	//touch()
	public function SetModificationTime(): string
    {
		return ''; //todo		
	}
	public function SetModificationUniversalTime (): string
    {
		return ''; //todo			
	}
}

function File1C($name): File1C
{
	return new File1C( $name);
}