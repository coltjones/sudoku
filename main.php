#!/usr/bin/env php
<?php
require_once('includes.php');
$puz = new sudoku();
if ($puz->generate()) {
    echo $puz->getPrintableGrid();
}
