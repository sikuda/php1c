function (){

	var token = function(){
		this.type = 0;
		this.context = '';
		this.index = -1;
		this.row = 1;
		this.col = 1;
	}

	var TokenStream = function(str=''){

		this.tokens = array();
		this.itoken = 0;

	    //common 
	    this.str = str;
	    this.start = 0;
	    this.pos = 0;

		//pointer to handle error
		this.row = 1;
		this.col = 1;

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
		
		//Russian Letters 
		const LetterRusLower = array('а','б','в','г','д','е','ё' ,'ж' ,'з','и','й', 'к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
		const LetterRusUpper = array('А','Б','В','Г','Д','Е','Ё' ,'Ж' ,'З','И','Й' ,'К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		const str_Identifiers = '/^[_A-Za-zА-Яа-яЁё][_0-9A-Za-zА-Яа-яЁё]*/u';

		//Ключевые слова - type_keyword
		const keywordsRus = array('НЕОПРЕДЕЛЕНО', 'ИСТИНА','ЛОЖЬ', 'ЕСЛИ', 'ТОГДА', 'ИНАЧЕЕСЛИ', 'ИНАЧЕ',   'КОНЕЦЕСЛИ','ПОКА',  'ДЛЯ', 'КАЖДОГО', 'ПО','В', 'ЦИКЛ','КОНЕЦЦИКЛА','ПРЕРВАТЬ','ПРОДОЛЖИТЬ'),
		const keywordsEng = array('Undefined',    'true',  'false','if(',  '){',    '} elseif {','} else {','}',        'while(','for(','foreach(','',  'in','){',  '}',         'break',   'continue'),
		const keyword_undefined = 0; const keyword_true = 1; const keyword_false = 2; const keyword_if = 3; const keyword_then = 4; const keyword_elseif = 5; const keyword_else = 6; const keyword_endif = 7; const keyword_while = 8; const keyword_for = 9; const keyword_foreach = 10; const keyword_to = 11; const keyword_in = 12; const keyword_circle = 13; const keyword_endcircle = 14; const keyword_break = 15;  const keyword_continue = 16; 

		//Индентификаторы типов - type_identification
		this.identypesRus = array('МАССИВ','ФАЙЛ'),
		this.identypesEng = array('Array1C', 'File1C'),
			
		this.functions1СRus = array();  // функции по русски в верхнем регистре для поиска
		this.functions1СEng = array(),  // функции по английски в вернем регистре для поиска
		this.functions1СClear = array() // функции по английски как будет в коде 
		this.indexFuncColl = -1;
		this.indexFuncDate = -1;
		this.indexFuncComm = -1;

	}
}();