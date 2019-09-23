<?php

class Bid {
    // property declaration
    public $userid;
    public $amount;
    public $code;    
    public $section;
    
    public function __construct($userid, $amount, $code, $section) {
        $this->userid = $userid;
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
    }
}

?>