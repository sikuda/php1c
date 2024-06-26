// CodeMirror, copyright (c) by Marijn Haverbeke and others
// 1C - http://code1c.sikuda.ru/
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
"use strict";

CodeMirror.defineMode("1c", function(conf, parserConf) {
    var ERRORCLASS = 'error';

    function wordRegexp(words) {
        return new RegExp("^((" + words.join(")|(") + "))([\\(\\)\\[\\] ,;\\/\\n\\t]|$)", "i");
    }
 
    var singleOperators = new RegExp("^[\\+\\-\\*/%&<>=]");
    var singleDelimiters = new RegExp("^[\\(\\)\\[\\]\\{\\},:=;\\.]");
    var doubleOperators = new RegExp("^((<>)|(<=)|(>=))");
    var identifiers = new RegExp("^[_A-Za-zА-ЯЁа-яё][_A-Za-z0-9А-ЯЁа-яё]*");
    var stringPrefixes = '"';
    var stringNewLine  = '|';

    var doOpening = wordRegexp(['do', 'пока']);
    var opening = wordRegexp(['если', 'if', 'пока', 'while', 'для', 'for', 'процедура', 'procedure', 'функция', 'function', 'попытка', 'try']);
    var middle = wordRegexp(['иначе', 'else', 'иначеесли', 'elsif', 'исключение', 'except']);
    var closing = wordRegexp(['конецесли', 'endif', 'конеццикла', 'enddo', 'конецпроцедуры', 'endprocedure', 'конецфункции', 'endfunction', 'конецпопытки', 'endtry']);
    var types = wordRegexp(['перем', 'var', 'знач', 'val']);
    //var wordOperators = wordRegexp(['and', 'or', 'not', 'xor', 'in']);
    var keywords = wordRegexp(['новый', 'new', 'каждого', 'each', 'из', 'from', 'цикл', 'do', 'или', 'or', 'не', 'not', 'ложь', 'false', 'истина', 'true', 'и','and', 'возврат', 'return', 'тогда', 'then', 'экспорт', 'export', 'неопределено', 'undefined', 'продолжить', 'continue', 'прервать', 'break', 'перейти', 'goto', 'по', 'to' , 'null']);
    
    function indent(_stream, state) {
      state.currentIndent++;
    }

    function dedent(_stream, state) {
      state.currentIndent--;
    }
    // tokenizers
    function tokenBase(stream, state) {
        if (stream.eatSpace()) {
            return null;
        }

        var ch = stream.peek();

        // Handle Comments
        if (ch === "/") {
            var ch_next = stream.string.charAt(stream.pos+1) || undefined;
            if (ch_next === "/") {
                stream.skipToEnd();
                return "comment";
            }
         }

        //handle meta - &OnClient or #If
        if (ch === "&" || ch === "#") {
            stream.skipToEnd();
            return "meta";
        }

        //~nameLabel
        if (ch === "~"){
            stream.next();
            stream.match(identifiers);
            return 'label';
        }

        
        // Handle Number Literals
        if (stream.match(/^\.?[0-9]{1,}/i, false)) {
            var floatLiteral = false;
            // Floats
            if (stream.match(/^\d*\.\d+F?/i)) { floatLiteral = true; }
            else if (stream.match(/^\d+\.\d*F?/)) { floatLiteral = true; }
            else if (stream.match(/^\.\d+F?/)) { floatLiteral = true; }

            if (floatLiteral) {
                // Float literals may be "imaginary"
                stream.eat(/J/i);
                return 'number';
            }
            // Integers
            var intLiteral = false;
            // Hex
            if (stream.match(/^&H[0-9a-f]+/i)) { intLiteral = true; }
            // Octal
            else if (stream.match(/^&O[0-7]+/i)) { intLiteral = true; }
            // Decimal
            else if (stream.match(/^[0-9]\d*F?/)) {
                // Decimal literals may be "imaginary"
                stream.eat(/J/i);
                // TODO - Can you have imaginary longs?
                intLiteral = true;
            }
            // Zero by itself with no other piece of number.
            else if (stream.match(/^0(?![\dx])/i)) { intLiteral = true; }
            if (intLiteral) {
                // Integer literals may be "long"
                stream.eat(/L/i);
                return 'number';
            }
        }

        // Handle Strings
        if (stream.match(stringPrefixes)) {
            state.tokenize = tokenStringFactory(stringPrefixes);
            return state.tokenize(stream, state);
        }
        if (stream.match(stringNewLine)) {
            state.tokenize = tokenStringFactory(stringPrefixes);
            return state.tokenize(stream, state);
        }


        if (stream.match(doubleOperators) || stream.match(singleOperators)) {
            return 'operator';
        }
        if (stream.match(singleDelimiters)) {
            return 'delimiter';
        }
        if (stream.match(doOpening)) {
            indent(stream,state);
            state.doInCurrentLine = true;
            return 'keyword';
        }
        if (stream.match(opening)) {
            if (! state.doInCurrentLine)
              indent(stream,state);
            else
              state.doInCurrentLine = false;
            return 'keyword';
        }
        if (stream.match(middle)) {
            return 'keyword';
        }
        if (stream.match(closing)) {
            dedent(stream,state);
            return 'keyword';
        }
        if (stream.match(types)) {
            return 'keyword';
        }

        if (stream.match(keywords)) {
            return 'keyword';
        }

        if (stream.match(identifiers)) {
            return 'variable';
        }

        // Handle non-detected items
        stream.next();
        return ERRORCLASS;
    }

    function tokenStringFactory(delimiter) {
        var singleline = delimiter.length == 1;
        var OUTCLASS = 'string';

        return function(stream, state) {
            while (!stream.eol()) {
                stream.eatWhile(/[^'"]/);
                if (stream.match(delimiter)) {
                    state.tokenize = tokenBase;
                    return OUTCLASS;
                } else {
                    stream.eat(/['"]/);
                }
            }
            if (singleline) {
                if (parserConf.singleLineStringErrors) {
                    return ERRORCLASS;
                } else {
                    state.tokenize = tokenBase;
                }
            }
            return OUTCLASS;
        };
    }


    function tokenLexer(stream, state) {
        var style = state.tokenize(stream, state);
        var current = stream.current();

        var delimiter_index = '[({'.indexOf(current);
        if (delimiter_index !== -1) {
            indent(stream, state );
        }
        delimiter_index = '])}'.indexOf(current);
        if (delimiter_index !== -1) {
            if (dedent(stream, state)) {
                return ERRORCLASS;
            }
        }
        return style;
    }

    var external = {
        electricChars:"dDpPtTfFeE ",
        startState: function() {
            return {
              tokenize: tokenBase,
              lastToken: null,
              currentIndent: 0,
              nextLineIndent: 0,
              doInCurrentLine: false
          };
        },

        token: function(stream, state) {
            if (stream.sol()) {
              state.currentIndent += state.nextLineIndent;
              state.nextLineIndent = 0;
              state.doInCurrentLine = 0;
            }
            var style = tokenLexer(stream, state);
            state.lastToken = {style:style, content: stream.current()};
            return style;
        },

        indent: function(state, textAfter) {
            var trueText = textAfter.replace(/^\s+|\s+$/g, '') ;
            if (trueText.match(closing) || trueText.match(middle)) return conf.indentUnit*(state.currentIndent-1);
            if(state.currentIndent < 0) return 0;
            return state.currentIndent * conf.indentUnit;
        },

        lineComment: "'"
    };
    return external;
});

CodeMirror.defineMIME("text/x-1c", "1c");

});
