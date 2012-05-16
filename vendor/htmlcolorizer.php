<?php

// http://www.php.net/manual/en/function.highlight-string.php#81257

class HTMLcolorizer{
    private $pointer = 0; //Cursor position.
    private $content = null; //content of document.
    private $colorized = null;
    function __construct($content){
        $this->content = $content;
    }
    function colorComment($position){
        $buffer = "&lt;<span class='HTMLComment'>";
        for($position+=1;$position < strlen($this->content) && $this->content[$position] != ">" ;$position++){
            $buffer.= $this->content[$position];
        }
        $buffer .= "</span>&gt;";
        $this->colorized .= $buffer;
        return $position;
    }
    function colorTag($position){
        $buffer = "&lt;<span class='tagName'>";
        $coloredTagName = false;
        //As long as we're in the tag scope
        for($position+=1;$position < strlen($this->content) && $this->content[$position] != ">" ;$position++){
            if($this->content[$position] == " " && !$coloredTagName){
                $coloredTagName = true;
                $buffer.="</span>";
            }else if($this->content[$position] != " " && $coloredTagName){
                //Expect attribute
                $attribute = "";
                //While we're in the tag
                for(;$position < strlen($this->content) && $this->content[$position] != ">" ;$position++){
                    if($this->content[$position] != "="){
                        $attribute .= $this->content[$position];
                    }else{
                        $value="";
                        $buffer .= "<span class='tagAttribute'>".$attribute."</span>=";
                        $attribute = ""; //initialize it
                        $inQuote = false;
                        $QuoteType = null;
                        for($position+=1;$position < strlen($this->content) && $this->content[$position] != ">" && $this->content[$position] != " "  ;$position++){
                            if($this->content[$position] == '"' || $this->content[$position] == "'"){
                                $inQuote = true;
                                $QuoteType = $this->content[$position];
                                $value.=$QuoteType;
                                //Read Until next quotation mark.
                                for($position+=1;$position < strlen($this->content) && $this->content[$position] != ">" && $this->content[$position] != $QuoteType  ;$position++){
                                    $value .= $this->content[$position];
                                }    
                                $value.=$QuoteType;
                            }else{//No Quotation marks.
                                $value .= $this->content[$position];
                            }                            
                        }
                        $buffer .= "<span class='tagValue'>".$value."</span>";
                        break;            
                    }
                    
                }
                if($attribute != ""){$buffer.="<span class='tagAttribute'>".$attribute."</span>";}
            }
            if($this->content[$position] == ">" ){break;}else{$buffer.= $this->content[$position];}
            
        }
        //In case there were no attributes.
        if($this->content[$position] == ">" && !$coloredTagName){
            $buffer.="</span>&gt;";
            $position++;
        }
        $this->colorized .= $buffer;
        return --$position;
    }
    function colorize(){
        $this->colorized="";
        $inTag = false;
        for($pointer = 0;$pointer<strlen($this->content);$pointer++){
            $thisChar = $this->content[$pointer];
            $nextChar = $this->content[$pointer+1];
            if($thisChar == "<"){
                if($nextChar == "!"){
                    $pointer = $this->colorComment($pointer);
                }else if($nextChar == "?"){
                    //colorPHP();
                }else{
                    $pointer = $this->colorTag($pointer);
                }
            }else{
                $this->colorized .= $this->content[$pointer];
            }
        }
        return $this->colorized;
    }
}
