#!/usr/bin/env php
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require_once('includes.php');
$puz = new sudoku();

//Solve it
if ($puz->generate()) {
    echo $puz->getPrintableGrid();
}
