<?php

class sudoku extends grid {

    public function __construct () {
        //Create an array of 9 elements starting at 1 all NULL values
        $grids = array_fill(1,9,NULL);
        //For each NULL create a new grid object.
        $grids = array_map(function ($val) {
            $tmp = new grid();
            return $tmp;
        },$grids);
        $this->data = array_combine(range(1,9),$grids);
        
        //Populate the positions we need to place values for
        $this->placementArr = range(1,81);

        //Ensure we are NOT solved
        $this->solved = FALSE;
    }

    public function __clone () {
        //Make sure any objects we have in $this->data get cloned too
        foreach ($this->data as $i => $o) {
            if ($o instanceof grid) {
                $this->data[$i] = clone $o;
            }
        }
    }

    public function getGrid ($gridNum) {
        return $this->data[$gridNum];
    }

    public function generate () {
        return $this->solve($this, $this->placementArr);
    }

    public function solve ($gArr, $pArr) {
        do {
            $g = $gArr;
            $p = $pArr;
        
            //Place some stuff
            if (count($p) > 0) {
                $loc = array_shift($p);
                list($gridNum, $cellNum) = $this->lookupCoords($loc);
            
                //Get the value to place
                $sGrid = $g->getGrid($gridNum);
                $testVals = $sGrid->getRemaining();

                do {
                    $val = array_shift($testVals);
                    //Set the value
                    $sGrid->setCell($cellNum, $val);
                    //Test for validity @ the supergrid
                    $valid = $g->isValid($gridNum, $cellNum);
                    if ($valid) {
                        //Try this tree
                        if ($this->solve($g,$p)) {
                            return TRUE;
                        } else {
                            //Reset the cell
                            $sGrid->clearCell($cellNum);
                            $valid = FALSE;
                        }
                    } else {
                        $sGrid->clearCell($cellNum);
                    }
                } while (!$valid && count($testVals));

                if (!$valid) {
                    return FALSE;
                }
            } else {
                $this->solved = TRUE;
                $this->data = $g->data;
            }
        } while (!$this->solved);
        return TRUE;
    }

    public function getPrintableGrid () {
        $printStr = PHP_EOL;
        $printStr .= "-------------------------".PHP_EOL;
        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $printStr .= "| ";
                foreach ($this->getRow($i) as $gObj) {
                    foreach ($gObj->getRow($j) as $cellVal) {
                        $printStr .= sprintf("%1s ", $cellVal);
                    }
                    $printStr .= "| ";
                }
                $printStr .= PHP_EOL;
            }
            $printStr .= "-------------------------".PHP_EOL;
        }
        $printStr .= PHP_EOL;

        return $printStr;
    }

    public function isValid ($gridNum, $cellNum) {
        //Get the grid row we are about
        $rNum = (int) ceil($gridNum/3);
        $gRow = $this->getRow($rNum);
        
        //Get the grid columns we care about
        $cNum = (int) ($gridNum%3);
        if ($cNum == 0) {
            $cNum = 3;
        }
        $gCol = $this->getCol($cNum);
        //Row Checking...
        $tmp = [];
        foreach ($gRow as $row) {
            //Which cells are we checking?
            $num = (int) ceil($cellNum/3);
            $cRow = $row->getRow($num);
            $tmp = array_merge($tmp,$cRow);
        }
        $rValid = $this->checkDupes($tmp);
        //Col Checking...
        $tmp = [];
        foreach ($gCol as $col) {
            //Which cells are we checking?
            $num = (int) ($cellNum%3);
            if ($num == 0) {
                $num = 3;
            }
            $cCol = $col->getCol($num);
            $tmp = array_merge($tmp,$cCol);
        }
        $cValid = $this->checkDupes($tmp);

        return ($rValid&&$cValid)?TRUE:FALSE;
    }

    public function checkDupes ($check) {
        $tmp = [];
        $check = array_filter($check);
        foreach ($check as $v) {
            @$tmp[$v]++;
        }
        arsort($tmp);
        $one = array_shift($tmp);
        if ($one > 1) {
            return FALSE;
        }
        return TRUE;
    }

    public function lookupCoords ($num) {
        //Find which grid we need
        $g = (int) (floor($num/9)+1);
        //Find which cell we need
        $c = (int) ($num%9);
        //Handle overflow
        if ($c == 0) {
            $g--;
            $c = 9;
        }
        return [$g,$c];
    }

}

