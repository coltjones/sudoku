<?php
ini_set('display_errors',1);
error_reporting(E_ALL & ~E_STRICT);

//Ensure Xdebug doesnt cause errors when running unit tests...
if (extension_loaded('xdebug')) {
    //Check and make sure that we have max_nesting level of at least 1000
    $maxNestingLvl = ini_get('xdebug.max_nesting_level');
    if ($maxNestingLvl < 1000) {
        die("Warning: php.ini found with xdebug.max_nesting_level = $maxNestingLvl We suggest a value of atleast 1000.".PHP_EOL.
            "Please add the following to your ".php_ini_loaded_file().PHP_EOL.
            "xdebug.max_nesting_level = 1000".PHP_EOL);
    }
}

require_once('grid.class.php');
require_once('sudoku.class.php');
