<?php

class grid {
    public $lockedCells = NULL;

    public function __construct () {
        //Initialize our data array for this grid
        $this->data = array_fill(1,9,NULL);
        //Initialize lockedCells to and empty array
        $this->lockedCells = [];
        //Overload remaining here
        $this->remaining = array_combine(range(1,9),range(1,9));
        uasort($this->remaining, function ($a, $b) {
            return mt_rand(-1, 1);
        });
    }

    public function isLockedCell ($cellNum) {
        if (isset($this->lockedCells[$cellNum])) {
            return TRUE;
        }
        return FALSE;
    }

    public function setCell ($cellNum, $value, $lock = FALSE) {
        if (!in_array($cellNum,range(1,9))) {
            return FALSE;
        }
        if (!in_array($value,range(1,9))) {
            return FALSE;
        }
        //Value could be a cell's integer value OR another grid object for the super set of grids
        $this->data[$cellNum] = $value;
        //We were used so eliminate this value from remaining
        unset($this->remaining[$value]);
        
        //Handle locks
        if ($lock) {
            $this->lockedCells[$cellNum] = $cellNum;
        }
        return TRUE;
    }

    public function clearCell ($cellNum) {
        $val = $this->data[$cellNum];
        $this->data[$cellNum] = NULL;
        $this->remaining[$val] = $val;
        return TRUE;
    }

    public function getRemaining () {
        return $this->remaining;
    }

    public function getRow ($rowNum = NULL) {
        //Based on the column number passed pull those cell values.
        switch ($rowNum) {
         case 1:
            $getArr = [1,2,3];
            break;
         case 2:
            $getArr = [4,5,6];
            break;
         case 3:
            $getArr = [7,8,9];
            break;
         default:
            throw new RangeException ("Invalid rowNum passed, $rowNum given, expected 1-3.");
        }
        return $this->getCellArr($getArr);
    }

    public function getCol ($colNum = NULL) {
        //Based on the column number passed pull those cell values.
        switch ($colNum) {
         case 1:
            $getArr = [1,4,7];
            break;
         case 2:
            $getArr = [2,5,8];
            break;
         case 3:
            $getArr = [3,6,9];
            break;
         default:
            throw new RangeException ("Invalid colNum passed, $colNum given, expected 1-3.");
        }
        return $this->getCellArr($getArr);
    }

    public function isValid ($filterEmpties = TRUE) {
        $tmp = [];
        if ($filterEmpties) {
            $check = array_filter($this->data);
        } else {
            $check = $this->data;
        }
        foreach ($check as $v) {
            if ($v === NULL) {
                return FALSE;
            }
            @$tmp[$v]++;
        }
        arsort($tmp);
        $one = array_shift($tmp);
        if ($one > 1) {
            return FALSE;
        }
        return TRUE;
    }

    protected function getCellArr ($getArr) {
        return array(1 => $this->data[$getArr[0]], 2 => $this->data[$getArr[1]], 3 => $this->data[$getArr[2]]);
    }

}
