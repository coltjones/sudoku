<?php
if (defined(APP_DEBUG)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

require_once('grid.class.php');
require_once('sudoku.class.php');
