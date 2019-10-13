<?php

// autoload the classes required
spl_autoload_register(function($class) {  
    require_once "classes/$class.php"; 
});


// session related stuff

session_start();


function printErrors() {
    if(isset($_SESSION['errors'])){
        echo "<div class='error'><ul>";
        
        foreach ($_SESSION['errors'] as $value) {
            echo "<li>" . $value . "</li>";
        }
        
        echo "</ul></div>";   
        unset($_SESSION['errors']);
    }    
}

function printMessages() {
    if(isset($_SESSION['msg'])){
        echo "<div class='message'><ul>";
        
        foreach ($_SESSION['msg'] as $value) {
            echo "<li>" . $value . "</li>";
        }
        
        echo "</ul></div>";   
        unset($_SESSION['msg']);
    }    
}

function isMissingOrEmpty($name) {
    $request = $_REQUEST;
    if ($name != 'token') {
        $request = json_decode($_REQUEST['r'], true);
    }
    
    if (!isset($request[$name])) {
        return "missing $name";
    }

    // client did send the value over
    $value = $request[$name];
    if (empty($value)) {
        return "blank $name";
    }
}

# check if an int input is an int and non-negative
function isNonNegativeInt($var) {
    if (is_numeric($var) && $var >= 0 && $var == round($var))
        return TRUE;
}

# check if a float input is is numeric and non-negative
function isNonNegativeFloat($var) {
    if (is_numeric($var) && $var >= 0)
        return TRUE;
}

# this is better than empty when use with array, empty($var) returns FALSE even when
# $var has only empty cells
function isEmpty($var) {
    if (isset($var) && is_array($var))
        foreach ($var as $key => $value) {
            if (empty($value)) {
               unset($var[$key]);
            }
        }

    if (empty($var))
        return TRUE;
}



?>