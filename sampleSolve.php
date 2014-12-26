#!/usr/bin/env php
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require_once('includes.php');
$puz = new sudoku();

//Set an initial puzzle
$puz->initCell(7,5);
$puz->initCell(10,8);
$puz->initCell(12,1);
$puz->initCell(22,4);
$puz->initCell(23,3);
$puz->initCell(35,2);
$puz->initCell(38,7);
$puz->initCell(44,3);
$puz->initCell(46,8);
$puz->initCell(49,1);
$puz->initCell(55,6);
$puz->initCell(60,3);
$puz->initCell(67,4);
$puz->initCell(70,2);
$puz->initCell(74,7);
$puz->initCell(75,5);
$puz->initCell(79,6);
/*
*/

echo "Solving puzzle: ".PHP_EOL;
echo $puz->getPrintableGrid();

//Solve it
if ($puz->generate()) {
    echo $puz->getPrintableGrid();
}
