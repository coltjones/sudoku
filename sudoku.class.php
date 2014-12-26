<?php

class sudoku extends grid {

    public function __construct () {
        //Initialize our seed state
        $this->seeded = FALSE;
        //Create an array of 9 elements starting at 1 all NULL values
        $grids = array_fill(1,9,NULL);
        //For each NULL create a new grid object.
        $grids = array_map(function ($val) {
            $tmp = new grid();
            return $tmp;
        },$grids);
        $this->data = array_combine(range(1,9),$grids);
        
        //Populate the positions we need to place values for
        $tmp = range(1,81);
        $this->placementArr = array_combine($tmp,$tmp);

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
        if ($this->seeded) {
            $possibles = $this->findPossibles();
            //Sort our position array so the easiest items are first
            uasort($possibles, function ($a, $b) {
                $nA = count($a);
                $nB = count($b);
                if ($nA == $nB) {
                    return 0;
                }
                return ($nA < $nB)? -1 : 1;
            });
            $tmp = array_keys($possibles);
            $this->placementArr = array_combine($tmp,$tmp);
        }
        return $this->solve($this, $this->placementArr);
    }

    public function findPossibles ($location = NULL) {
        $possible = [];
        if ($location === NULL) {
            //Do them all
            $range = array_keys($this->placementArr);
        } else {
            $range = [$location];
        }
        //Look through each column
        foreach ($range as $loc) {
            list($gridNum, $cellNum) = $this->lookupCoords($loc);
            //Get the grid
            $sGrid = $this->getGrid($gridNum);
            $gRow = $this->gridNumToRow($gridNum);
            $gCol = $this->gridNumToCol($gridNum);
            
            //Gather the info we can
            $rem = $sGrid->getRemaining();
            sort($rem);
            $rem = array_combine($rem,$rem);
            $row = array_filter($this->getAggRow($gRow, $cellNum));
            $row = array_combine($row,$row);
            asort($row);
            $col = array_filter($this->getAggCol($gCol, $cellNum));
            $col = array_combine($col,$col);
            asort($col);
            //Finally diff it
            $possible = array_diff_assoc($rem, $row, $col);
            if ($location === NULL) {
                $ret[$loc] = $possible;
            } else {
                //Only care about this cell right now.
                return $possible;
            }
        }
        if ($location === NULL) {
            return $ret;
        }
    }

    public function solve ($gArr, $pArr) {
        do {
            $g = $gArr;
            $p = $pArr;
        
            //Place some stuff
            if (count($p) > 0) {
                $loc = array_shift($p);
                list($gridNum, $cellNum) = $this->lookupCoords($loc);
//echo "LOC $loc".PHP_EOL; 

                //Get the subGrid
                $sGrid = $g->getGrid($gridNum);

                //If the cell is locked just recurse, nothing to do at this position
                if ($sGrid->isLockedCell($cellNum)) {
//echo "Cell is locked. Moving on...".PHP_EOL;
                    if ($this->solve($g,$p)) {
                        return TRUE;
                    } else {
                        return FALSE;
                    }
                }
            
                if ($this->seeded) {
                    //Eliminate as many options as we can...
                    $testVals = $this->findPossibles($loc);
                } else {
                    $testVals = $sGrid->getRemaining();
                }

                if (!(count($testVals) > 0)) {
                    return FALSE;
                }
//echo "\ttestVals = ".implode(", ",$testVals).PHP_EOL;
                do {
                    $val = array_shift($testVals);
                    //Set the value
                    $sGrid->setCell($cellNum, $val);
                    //Test for validity @ the supergrid
                    $valid = $g->isValid($gridNum, $cellNum);
                    if ($valid) {
//echo $this->getPrintableGrid();
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
        
        //Get the grid row and column we need
        $gRow = $this->gridNumToRow($gridNum);
        $gCol = $this->gridNumToCol($gridNum);
        
        //Get the row and column you need
        $tmp = $this->getAggRow($gRow, $cellNum);
        $rValid = $this->checkDupes($tmp);

        $tmp = $this->getAggCol($gCol, $cellNum);
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

    public function initCell ($loc, $value) {
        $this->seeded = TRUE;
        list($gridNum, $cellNum) = $this->lookupCoords($loc);
        $sGrid = $this->getGrid($gridNum);
        $sGrid->setCell($cellNum, $value, TRUE);
        //Clear this from the work list
        unset($this->placementArr[$loc]);
        return TRUE;
    }

    public function getAggRow ($gRow, $cellNum) {
        $tmp = [];
        foreach ($gRow as $row) {
            $cRow = $row->gridNumToRow($cellNum);
            $tmp = array_merge($tmp,$cRow);
        }

        return $tmp;
    }

    public function getAggCol ($gCol, $cellNum) {
        $tmp = [];
        foreach ($gCol as $col) {
            $cCol = $col->gridNumToCol($cellNum);
            $tmp = array_merge($tmp,$cCol);
        }
        return $tmp;
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

