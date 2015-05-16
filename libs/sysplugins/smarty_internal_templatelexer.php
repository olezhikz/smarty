<?php
/**
 * Smarty Internal Plugin Templatelexer
 * This is the lexer to break the template source into tokens
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty_Internal_Templatelexer
 * This is the template file lexer.
 * It is generated from the smarty_internal_templatelexer.plex file
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
class Smarty_Internal_Templatelexer
{
    /**
     * Source
     *
     * @var string
     */
    public $data;
    /**
     * byte counter
     *
     * @var int
     */
    public $counter;
    /**
     * token number
     *
     * @var int
     */
    public $token;
    /**
     * token value
     *
     * @var string
     */
    public $value;
    /**
     * current line
     *
     * @var int
     */
    public $line;
    /**
     * tag start line
     *
     * @var
     */
    public $taglineno;
    /**
     * flag if parsing php script
     *
     * @var bool
     */
    public $is_phpScript = false;
   /**
     * php code type
     *
     * @var string
     */
    public $phpType = '';
    /**
     * escaped left delimiter
     *
     * @var string
     */
    public $ldel = '';
    /**
     * escaped left delimiter length
     *
     * @var int
     */
    public $ldel_length = 0;
    /**
     * escaped right delimiter
     *
     * @var string
     */
    public $rdel = '';
    /**
     * escaped right delimiter length
     *
     * @var int
     */
    public $rdel_length = 0;
    /**
     * state number
     *
     * @var int
     */
    public $state = 1;
    /**
     * Smarty object
     *
     * @var Smarty
     */
    public $smarty = null;
    /**
     * compiler object
     *
     * @var Smarty_Internal_TemplateCompilerBase
     */
    private $compiler = null;
    /**
     * literal tag nesting level
     *
     * @var int
     */
    private $literal_cnt = 0;
    /**
     * trace file
     *
     * @var resource
     */
    public $yyTraceFILE;
    /**
     * trace prompt
     *
     * @var string
     */
    public $yyTracePrompt;
    /**
     * state names
     *
     * @var array
     */
    public $state_name = array(1 => 'TEXT', 2 => 'SMARTY', 3 => 'LITERAL', 4 => 'DOUBLEQUOTEDSTRING', 5 => 'CHILDBODY');
    /**
     * token names
     *
     * @var array
     */
    public $smarty_token_names = array(        // Text for parser error messages
                                               'IDENTITY'        => '===',
                                               'NONEIDENTITY'    => '!==',
                                               'EQUALS'          => '==',
                                               'NOTEQUALS'       => '!=',
                                               'GREATEREQUAL'    => '(>=,ge)',
                                               'LESSEQUAL'       => '(<=,le)',
                                               'GREATERTHAN'     => '(>,gt)',
                                               'LESSTHAN'        => '(<,lt)',
                                               'MOD'             => '(%,mod)',
                                               'NOT'             => '(!,not)',
                                               'LAND'            => '(&&,and)',
                                               'LOR'             => '(||,or)',
                                               'LXOR'            => 'xor',
                                               'OPENP'           => '(',
                                               'CLOSEP'          => ')',
                                               'OPENB'           => '[',
                                               'CLOSEB'          => ']',
                                               'PTR'             => '->',
                                               'APTR'            => '=>',
                                               'EQUAL'           => '=',
                                               'NUMBER'          => 'number',
                                               'UNIMATH'         => '+" , "-',
                                               'MATH'            => '*" , "/" , "%',
                                               'INCDEC'          => '++" , "--',
                                               'SPACE'           => ' ',
                                               'DOLLAR'          => '$',
                                               'SEMICOLON'       => ';',
                                               'COLON'           => ':',
                                               'DOUBLECOLON'     => '::',
                                               'AT'              => '@',
                                               'HATCH'           => '#',
                                               'QUOTE'           => '"',
                                               'BACKTICK'        => '`',
                                               'VERT'            => '|',
                                               'DOT'             => '.',
                                               'COMMA'           => '","',
                                               'ANDSYM'          => '"&"',
                                               'QMARK'           => '"?"',
                                               'ID'              => 'identifier',
                                               'TEXT'            => 'text',
                                               'FAKEPHPSTARTTAG' => 'Fake PHP start tag',
                                               'PHPSTARTTAG'     => 'PHP start tag',
                                               'PHPENDTAG'       => 'PHP end tag',
                                               'LITERALSTART'    => 'Literal start',
                                               'LITERALEND'      => 'Literal end',
                                               'LDELSLASH'       => 'closing tag',
                                               'COMMENT'         => 'comment',
                                               'AS'              => 'as',
                                               'TO'              => 'to',
    );

    /**
     * constructor
     *
     * @param   string                             $data template source
     * @param Smarty_Internal_TemplateCompilerBase $compiler
     */
    function __construct($data, Smarty_Internal_TemplateCompilerBase $compiler)
    {
        $this->data = $data;
        $this->counter = 0;
        if (preg_match('/^\xEF\xBB\xBF/', $this->data, $match)) {
            $this->counter += strlen($match[0]);
        }
        $this->line = 1;
        $this->smarty = $compiler->smarty;
        $this->compiler = $compiler;
        $this->ldel = preg_quote($this->smarty->left_delimiter, '/');
        $this->ldel_length = strlen($this->smarty->left_delimiter);
        $this->rdel = preg_quote($this->smarty->right_delimiter, '/');
        $this->rdel_length = strlen($this->smarty->right_delimiter);
        $this->smarty_token_names['LDEL'] = $this->smarty->left_delimiter;
        $this->smarty_token_names['RDEL'] = $this->smarty->right_delimiter;
    }

    public function PrintTrace()
    {
        $this->yyTraceFILE = fopen('php://output', 'w');
        $this->yyTracePrompt = '<br>';
    }

     
    private $_yy_state = 1;
    private $_yy_stack = array();

    public function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    public function yypushstate($state)
    {
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState push %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
    }

    public function yypopstate()
    {
       if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState pop %s\n", $this->yyTracePrompt,  isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
       $this->_yy_state = array_pop($this->_yy_stack);
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }

    }

    public function yybegin($state)
    {
       $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState set %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
    }


     
    public function yylex1()
    {
        $tokenMap = array (
              1 => 0,
              2 => 1,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 1,
              9 => 0,
              10 => 0,
              11 => 0,
              12 => 5,
              18 => 0,
              19 => 0,
              20 => 0,
              21 => 1,
              23 => 5,
              29 => 6,
              36 => 5,
              42 => 3,
              46 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\\{\\})|\G(".$this->ldel."\\*([\S\s]*?)\\*".$this->rdel.")|\G(".$this->ldel."\\s*strip\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/strip\\s*".$this->rdel.")|\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*(if|elseif|else if|while)\\s+)|\G(".$this->ldel."\\s*for\\s+)|\G(".$this->ldel."\\s*foreach(?![^\s]))|\G(".$this->ldel."\\s*setfilter\\s+)|\G((".$this->ldel."\\s*php\\s*(.)*?".$this->rdel."([\S\s]*?)".$this->ldel."\\s*\/php\\s*".$this->rdel.")|(".$this->ldel."\\s*[\/]?php\\s*(.)*?".$this->rdel."))|\G(".$this->ldel."\\s*\/)|\G(".$this->ldel."\\s*)|\G(\\s*".$this->rdel.")|\G(<\\?xml\\s+([\S\s]*?)\\?>)|\G(<%((('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(\"[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*\")|(\/\\*[\S\s]*?\\*\/)|[\S\s])*?)%>)|\G((<\\?(?:php\\s+|=)?)((('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(\"[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*\")|(\/\\*[\S\s]*?\\*\/)|[\S\s])*?)\\?>)|\G(<script\\s+language\\s*=\\s*[\"']?\\s*php\\s*[\"']?\\s*>((('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(\"[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*\")|(\/\\*[\S\s]*?\\*\/)|[\S\s])*?)<\/script>)|\G((<(\\?(?:php\\s+|=)?|(script\\s+language\\s*=\\s*[\"']?\\s*php\\s*[\"']?\\s*>)|%))|\\?>|%>)|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state TEXT');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r1_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TEXT = 1;
    function yy_r1_1($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r1_2($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_COMMENT;
         }
    function yy_r1_4($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false)  {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_STRIPON;
       }
         }
    function yy_r1_5($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_STRIPOFF;
       }
         }
    function yy_r1_6($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_LITERALSTART;
         $this->yypushstate(self::LITERAL);
        }
         }
    function yy_r1_7($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELIF;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_9($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOR;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_10($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOREACH;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_11($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELSETFILTER;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_12($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_PHP;
          $this->phpType = 'tag';
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_18($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_LDELSLASH;
         $this->yypushstate(self::SMARTY);
         $this->taglineno = $this->line;
       }
         }
    function yy_r1_19($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDEL;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r1_20($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r1_21($yy_subpatterns)
    {

        $this->token = Smarty_Internal_Templateparser::TP_XMLTAG;
        $this->taglineno = $this->line;
         }
    function yy_r1_23($yy_subpatterns)
    {

        $this->phpType = 'asp';
        $this->taglineno = $this->line;
        $this->token = Smarty_Internal_Templateparser::TP_PHP;
         }
    function yy_r1_29($yy_subpatterns)
    {

        $this->phpType = 'php';
        $this->taglineno = $this->line;
        $this->token = Smarty_Internal_Templateparser::TP_PHP;
         }
    function yy_r1_36($yy_subpatterns)
    {

        $this->phpType = 'script';
        $this->taglineno = $this->line;
        $this->token = Smarty_Internal_Templateparser::TP_PHP;
         }
    function yy_r1_42($yy_subpatterns)
    {

        $this->phpType = 'unmatched';
        $this->taglineno = $this->line;
        $this->token = Smarty_Internal_Templateparser::TP_PHP;
         }
    function yy_r1_46($yy_subpatterns)
    {

       $to = strlen($this->data);
       preg_match("/{$this->ldel}|<\?|<%|\?>|%>|<script\s+language\s*=\s*[\"\']?\s*php\s*[\"\']?\s*>/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }

     
    public function yylex2()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 1,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 0,
              11 => 0,
              12 => 4,
              17 => 4,
              22 => 2,
              25 => 0,
              26 => 3,
              30 => 0,
              31 => 0,
              32 => 0,
              33 => 0,
              34 => 0,
              35 => 0,
              36 => 0,
              37 => 0,
              38 => 1,
              40 => 1,
              42 => 0,
              43 => 0,
              44 => 0,
              45 => 2,
              48 => 0,
              49 => 0,
              50 => 0,
              51 => 0,
              52 => 0,
              53 => 0,
              54 => 0,
              55 => 0,
              56 => 0,
              57 => 0,
              58 => 0,
              59 => 0,
              60 => 0,
              61 => 1,
              63 => 0,
              64 => 0,
              65 => 0,
              66 => 0,
              67 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\")|\G('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|\G([$]smarty\\.block\\.(child|parent))|\G(\\$)|\G(\\s*".$this->rdel.")|\G(\\s+is\\s+in\\s+)|\G(\\s+as\\s+)|\G(\\s+to\\s+)|\G(\\s+step\\s+)|\G(\\s+instanceof\\s+)|\G(\\s*(([!=][=]{1,2})|([<][=>]?)|([>][=]?)|[&|]{2})\\s*)|\G(\\s+(eq|ne|neg|gt|ge|gte|lt|le|lte|mod|and|or|xor|(is\\s+(not\\s+)?(odd|even|div)\\s+by))\\s+)|\G(\\s+is\\s+(not\\s+)?(odd|even))|\G(!\\s*|not\\s+)|\G(\\((int(eger)?|bool(ean)?|float|double|real|string|binary|array|object)\\)\\s*)|\G(\\s*\\(\\s*)|\G(\\s*\\))|\G(\\[\\s*)|\G(\\s*\\])|\G(\\s*->\\s*)|\G(\\s*=>\\s*)|\G(\\s*=\\s*)|\G(\\+\\+|--)|\G(\\s*(\\+|-)\\s*)|\G(\\s*([*]{1,2}|[%\/^&]|[<>]{2})\\s*)|\G(@)|\G(#)|\G(\\s+[0-9]*[a-zA-Z_][a-zA-Z0-9_\-:]*\\s*=\\s*)|\G(([0-9]*[a-zA-Z_]\\w*)?(\\\\[0-9]*[a-zA-Z_]\\w*)+)|\G([0-9]*[a-zA-Z_]\\w*)|\G(\\d+)|\G(`)|\G(\\|)|\G(\\.)|\G(\\s*,\\s*)|\G(\\s*;)|\G(::)|\G(\\s*:\\s*)|\G(\\s*&\\s*)|\G(\\s*\\?\\s*)|\G(0[xX][0-9a-fA-F]+)|\G(\\s+)|\G(".$this->ldel."\\s*(if|elseif|else if|while)\\s+)|\G(".$this->ldel."\\s*for\\s+)|\G(".$this->ldel."\\s*foreach(?![^\s]))|\G(".$this->ldel."\\s*\/)|\G(".$this->ldel."\\s*)|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state SMARTY');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r2_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const SMARTY = 2;
    function yy_r2_1($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_QUOTE;
       $this->yypushstate(self::DOUBLEQUOTEDSTRING);
         }
    function yy_r2_2($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_SINGLEQUOTESTRING;
         }
    function yy_r2_3($yy_subpatterns)
    {

          $this->token = Smarty_Internal_Templateparser::TP_SMARTYBLOCKCHILDPARENT;
          $this->taglineno = $this->line;
         }
    function yy_r2_5($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_DOLLAR;
         }
    function yy_r2_6($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_RDEL;
       $this->yypopstate();
         }
    function yy_r2_7($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_ISIN;
         }
    function yy_r2_8($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_AS;
         }
    function yy_r2_9($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TO;
         }
    function yy_r2_10($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_STEP;
         }
    function yy_r2_11($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_INSTANCEOF;
         }
    function yy_r2_12($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_LOGOP;
         }
    function yy_r2_17($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TLOGOP;
         }
    function yy_r2_22($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_SINGLECOND;
         }
    function yy_r2_25($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_NOT;
         }
    function yy_r2_26($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TYPECAST;
         }
    function yy_r2_30($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_OPENP;
         }
    function yy_r2_31($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_CLOSEP;
         }
    function yy_r2_32($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_OPENB;
         }
    function yy_r2_33($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_CLOSEB;
         }
    function yy_r2_34($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_PTR;
         }
    function yy_r2_35($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_APTR;
         }
    function yy_r2_36($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_EQUAL;
         }
    function yy_r2_37($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_INCDEC;
         }
    function yy_r2_38($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_UNIMATH;
         }
    function yy_r2_40($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_MATH;
         }
    function yy_r2_42($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_AT;
         }
    function yy_r2_43($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_HATCH;
         }
    function yy_r2_44($yy_subpatterns)
    {

       // resolve conflicts with shorttag and right_delimiter starting with '='
       if (substr($this->data, $this->counter + strlen($this->value) - 1, $this->rdel_length) == $this->smarty->right_delimiter) {
          preg_match("/\s+/",$this->value,$match);
          $this->value = $match[0];
          $this->token = Smarty_Internal_Templateparser::TP_SPACE;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_ATTR;
       }
         }
    function yy_r2_45($yy_subpatterns)
    {

        $this->token = Smarty_Internal_Templateparser::TP_NAMESPACE;
         }
    function yy_r2_48($yy_subpatterns)
    {

        $this->token = Smarty_Internal_Templateparser::TP_ID;
         }
    function yy_r2_49($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_INTEGER;
         }
    function yy_r2_50($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_BACKTICK;
       $this->yypopstate();
         }
    function yy_r2_51($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_VERT;
         }
    function yy_r2_52($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_DOT;
         }
    function yy_r2_53($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_COMMA;
         }
    function yy_r2_54($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_SEMICOLON;
         }
    function yy_r2_55($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_DOUBLECOLON;
         }
    function yy_r2_56($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_COLON;
         }
    function yy_r2_57($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_ANDSYM;
         }
    function yy_r2_58($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_QMARK;
         }
    function yy_r2_59($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_HEX;
         }
    function yy_r2_60($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_SPACE;
         }
    function yy_r2_61($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELIF;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r2_63($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOR;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r2_64($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOREACH;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r2_65($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_LDELSLASH;
         $this->yypushstate(self::SMARTY);
         $this->taglineno = $this->line;
       }
         }
    function yy_r2_66($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDEL;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r2_67($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }


     
    public function yylex3()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/literal\\s*".$this->rdel.")|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state LITERAL');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r3_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const LITERAL = 3;
    function yy_r3_1($yy_subpatterns)
    {

       $this->literal_cnt++;
       $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
         }
    function yy_r3_2($yy_subpatterns)
    {

       if ($this->literal_cnt) {
         $this->literal_cnt--;
         $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_LITERALEND;
         $this->yypopstate();
       }
         }
    function yy_r3_3($yy_subpatterns)
    {

       $to = strlen($this->data);
       preg_match("/{$this->ldel}\/?literal{$this->rdel}/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       } else {
         $this->compiler->trigger_template_error ("missing or misspelled literal closing tag");
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
         }

     
    public function yylex4()
    {
        $tokenMap = array (
              1 => 1,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 0,
              11 => 0,
              12 => 0,
              13 => 3,
              17 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*(if|elseif|else if|while)\\s+)|\G(".$this->ldel."\\s*for\\s+)|\G(".$this->ldel."\\s*foreach(?![^\s]))|\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/)|\G(".$this->ldel."\\s*)|\G(\")|\G(`\\$)|\G(\\$[0-9]*[a-zA-Z_]\\w*)|\G(\\$)|\G(([^\"\\\\]*?)((?:\\\\.[^\"\\\\]*?)*?)(?=(".$this->ldel."|\\$|`\\$|\")))|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state DOUBLEQUOTEDSTRING');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r4_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const DOUBLEQUOTEDSTRING = 4;
    function yy_r4_1($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELIF;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r4_3($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOR;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r4_4($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDELFOREACH;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r4_5($yy_subpatterns)
    {

         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r4_6($yy_subpatterns)
    {

         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r4_7($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_LDELSLASH;
         $this->yypushstate(self::SMARTY);
         $this->taglineno = $this->line;
       }
         }
    function yy_r4_8($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
         $this->token = Smarty_Internal_Templateparser::TP_TEXT;
       } else {
          $this->token = Smarty_Internal_Templateparser::TP_LDEL;
          $this->yypushstate(self::SMARTY);
          $this->taglineno = $this->line;
       }
         }
    function yy_r4_9($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_QUOTE;
       $this->yypopstate();
         }
    function yy_r4_10($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_BACKTICK;
       $this->value = substr($this->value,0,-1);
       $this->yypushstate(self::SMARTY);
       $this->taglineno = $this->line;
         }
    function yy_r4_11($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_DOLLARID;
         }
    function yy_r4_12($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r4_13($yy_subpatterns)
    {

       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    function yy_r4_17($yy_subpatterns)
    {

       $to = strlen($this->data);
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }

     
    public function yylex5()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*strip\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/strip\\s*".$this->rdel.")|\G(".$this->ldel."\\s*block)|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state CHILDBODY');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r5_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const CHILDBODY = 5;
    function yy_r5_1($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          return false;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_STRIPON;
       }
         }
    function yy_r5_2($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          return false;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_STRIPOFF;
       }
         }
    function yy_r5_3($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          return false;
       } else {
         $this->yypopstate();
         return true;
       }
         }
    function yy_r5_4($yy_subpatterns)
    {

       $to = strlen($this->data);
       preg_match("/".$this->ldel."\s*((\/)?strip\s*".$this->rdel."|block\s+)/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       return false;
         }

     
    public function yylex6()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 1,
              6 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*block)|\G(".$this->ldel."\\s*\/block)|\G(".$this->ldel."\\s*[$]smarty\\.block\\.(child|parent))|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state CHILDBLOCK');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r6_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const CHILDBLOCK = 6;
    function yy_r6_1($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
         $this->yypushstate(self::CHILDLITERAL);
        }
         }
    function yy_r6_2($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->yypopstate();
         return true;
       }
         }
    function yy_r6_3($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->yypopstate();
         return true;
       }
         }
    function yy_r6_4($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->yypopstate();
         return true;
       }
         }
    function yy_r6_6($yy_subpatterns)
    {

       $to = strlen($this->data);
       preg_match("/".$this->ldel."\s*(literal\s*".$this->rdel."|(\/)?block(\s|".$this->rdel.")|[\$]smarty\.block\.(child|parent))/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
         }

     
    public function yylex7()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
            );
        if ($this->counter >=  strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/literal\\s*".$this->rdel.")|\G([\S\s])/iS";

        do {
            if (preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = preg_grep("/(.|\s)+/", $yysubmatches);
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state CHILDLITERAL');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r7_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >=  strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const CHILDLITERAL = 7;
    function yy_r7_1($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
         $this->yypushstate(self::CHILDLITERAL);
       }
         }
    function yy_r7_2($yy_subpatterns)
    {

       if ($this->smarty->auto_literal && isset($this->value[$this->ldel_length]) ? strpos(" \n\t\r", $this->value[$this->ldel_length]) !== false : false) {
          $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
       } else {
         $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
         $this->yypopstate();
       }
         }
    function yy_r7_3($yy_subpatterns)
    {

       $to = strlen($this->data);
       preg_match("/{$this->ldel}\/?literal\s*{$this->rdel}/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       } else {
         $this->compiler->trigger_template_error ("missing or misspelled literal closing tag");
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_BLOCKSOURCE;
         }

 }

     