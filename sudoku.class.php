<?php

class sudoku extends grid {

    public function __construct () {
        //Initialize our seed state
        $this->seeded = FALSE;
        //Initialize hidden singles
        $this->hiddenSingles = 0;
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
            $this->findHiddenSingles();
        }
        return $this->solve($this, $this->placementArr);
    }

    public function findHiddenSingles () {
        $found = FALSE;
        $possibles = $this->findPossibles();
        $gridLines = $this->getGridLineArrs();
        foreach ($gridLines as $lineArr) {
            //Initialize the lookup array to empty
            $checkArr = [];
            foreach ($lineArr as $loc) {
                if (!isset($possibles[$loc])) {
                    continue;
                }
                foreach ($possibles[$loc] as $v) {
                    @$checkArr[$v]++;
                }
            }
            //Find any keys with a value of 1
            $singles = array_filter($checkArr, function ($v) {
                return ($v == 1)?TRUE:FALSE;
            });
            //Do we have singles to handle?
            if (count($singles) > 0) {
                $singles = array_keys($singles);
                foreach ($singles as $sVal) {
                    //Find the location of the singles
                    foreach ($lineArr as $loc) {
                        if (!isset($possibles[$loc])) {
                            continue;
                        }
                        if (in_array($sVal, $possibles[$loc])) {
                            $this->initCell($loc, $sVal);
                            $this->hiddenSingles++;
                            $found = TRUE;
                        }
                    }
                }
            }
        }
        if ($found) {
            $this->findHiddenSingles();
        }
        return;
    }

    public function findPossibles ($location = NULL) {
        $possible = [];
        $single = FALSE;
        if (is_array($location)) {
            //Do what we were asked to
            $range = $location;
        } else {
            if ($location === NULL) {
                //Do them all
                $range = array_keys($this->placementArr);
            } else {
                //Do only the one specified
                $range = [$location];
                $single = TRUE;
            }
        }
        //Look through each column
        foreach ($range as $loc) {
            list($gridNum, $cellNum) = $this->lookupCoords($loc);
            //Get the grid
            $sGrid = $this->getGrid($gridNum);
            if ($sGrid->isLockedCell($cellNum)) {
                return [$sGrid->getCell($cellNum)];
            }
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
            if ($single) {
                //Only care about this cell right now.
                return $possible;
            } else {
                $ret[$loc] = $possible;
            }
        }
        return $ret;
    }

    public function solve ($gArr, $pArr) {
        do {
            $g = $gArr;
            $p = $pArr;

            //Place some stuff
            if (count($p) > 0) {
                $p = $this->sortPlacementArr($p);
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
//echo "No testVals to use...".PHP_EOL;
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
//echo "Invalid placement...".PHP_EOL;
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

    public function getGridLineArrs () {
        $cellArrs = [
                     [1, 4, 7,  28,31,34, 55,58,61],
                     [2, 5, 8,  29,32,35, 56,59,62],
                     [3, 6, 9,  30,33,36, 57,60,63],

                     [10,13,16, 37,40,43, 64,67,70],
                     [11,14,17, 38,41,44, 65,68,71],
                     [12,15,18, 39,42,45, 66,69,72],

                     [19,22,25, 46,49,52, 73,76,79],
                     [20,23,26, 47,50,53, 74,77,80],
                     [21,24,27, 48,51,54, 75,78,81],

                     [1, 2, 3,  10,11,12, 19,20,21],
                     [4, 5, 6,  13,14,15, 22,23,24],
                     [7, 8, 9,  16,17,18, 25,26,27],

                     [28,29,30, 37,38,39, 46,47,48],
                     [31,32,33, 40,41,42, 49,50,51],
                     [34,35,36, 43,44,45, 52,53,54],

                     [55,56,57, 64,65,66, 73,74,75],
                     [58,59,60, 67,68,69, 76,77,78],
                     [61,62,63, 70,71,72, 79,80,81],
                    ];
        return $cellArrs;
    }

    public function sortPlacementArr ($p = NULL) {
        if ($p === NULL) {
            $arr = $this->placementArr;
        } else {
            $arr = $p;
        }
//echo "I'm sorting...".PHP_EOL;
//var_dump(array_slice($p,0,5));
        $possibles = $this->findPossibles($arr);
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
        $arr = array_combine($tmp,$tmp);
        if ($p === NULL) {
            $this->placementArr = $arr;
        }
        return $arr;
    }
}
