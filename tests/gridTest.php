<?php

require_once('includes.php');

class gridTest extends PHPUnit_Framework_TestCase {
    public function testGridConstruct () {
        $g = new grid();
        
        //We have 9 elements in the data array, indexes 1-9 and all are NULL
        $this->assertEquals(9, count($g->data));
        for ($i = 1; $i <= 9; $i++) {
            $this->assertArrayHasKey($i, $g->data);
            $this->assertEquals(NULL, $g->data[$i]);
        }

        //We have 9 remaining values 1-9 and all indexes match their values
        $this->assertEquals(9, count($g->remaining));

        $rem = $g->getRemaining();
        for ($i = 1; $i <= 9; $i++) {
            $this->assertArrayHasKey($i, $rem);
            $this->assertEquals($i, $rem[$i]);
        }

        //Ensure the remaining numbers have been shuffled
        $remVals = array_keys($rem);
        $foundGap = FALSE;
        foreach ($remVals as $i => $v) {
            if (isset($remVals[$i]) && isset($remVals[$i+1])) {
                if (abs($remVals[$i] - $remVals[$i+1]) > 1) {
                    $foundGap = TRUE;
                }
            }
        }
        $this->assertEquals(TRUE, $foundGap);
    }

    public function testSetCell () {
        $g = new grid();
        
        //Make sure we only allow 1-9 for either the cellNum or the val
        for ($i = 1; $i <= 9; $i++) {
            for ($j = 1; $j <= 9; $j++) {
                $test = $g->setCell($i, $j);
                $this->assertEquals(TRUE, $test);
            }
        }
        
        //Make sure 0 and 10 fail
        $test = $g->setCell(0, 0);
        $this->assertEquals(FALSE, $test);
        $test = $g->setCell(0, 1);
        $this->assertEquals(FALSE, $test);
        $test = $g->setCell(1, 0);
        $this->assertEquals(FALSE, $test);
        $test = $g->setCell(10, 1);
        $this->assertEquals(FALSE, $test);
        $test = $g->setCell(1, 10);
        $this->assertEquals(FALSE, $test);
        $test = $g->setCell(10, 10);
        $this->assertEquals(FALSE, $test);

        //Ensure the value is stored in the expected key
        $g = new grid();
        
        $cellNum = mt_rand(1,9);
        $val = mt_rand(1,9);
        $g->setCell($cellNum,$val);
        $this->assertEquals($val, $g->data[$cellNum]);

        //Ensure the key is NOT set in remainders
        $rem = $g->getRemaining();
        $test = FALSE;
        if (isset($rem[$val])) {
            $test = TRUE;
        }
        $this->assertEquals(FALSE, $test);

        //Ensure the value is NOT set in remaining
        $test = FALSE;
        if (in_array($val,$rem)) {
            $test = TRUE;
        }
        $this->assertEquals(FALSE, $test);
    
    }

    public function testClearCell () {
        $g = new grid();

        $cellNum = mt_rand(1,9);
        $val = mt_rand(1,9);
        $g->setCell($cellNum,$val);

        $test = $g->clearCell($cellNum);

        //Ensure the return value is true
        $this->assertEquals(TRUE, $test);

        //Ensure the cell value is NULL
        $this->assertEquals(NULL, $g->data[$cellNum]);

        //Ensure the value has been put back in remaining
        $rem = $g->getRemaining();
        $test = FALSE;
        if (isset($rem[$val])) {
            $test = TRUE;
        }
        $this->assertEquals(TRUE, $test);

        //Ensure the value is set in remaining
        $test = FALSE;
        if (in_array($val,$rem)) {
            $test = TRUE;
        }
        $this->assertEquals(TRUE, $test);
    }

    public function testGetRemaining () {
        $g = new grid();
        $rem1 = serialize($g->remaining);
        $rem2 = serialize($g->getRemaining());
        //Ensure that the array matches the method output
        $this->assertEquals($rem1, $rem2);
    }

    public function testGetRow () {
        $g = new grid();
        
        //Set some values to text based on
        for ($i = 1; $i <= 3; $i++) {
            $test = $g->setCell($i, 1);
        }
        for ($i = 4; $i <= 6; $i++) {
            $test = $g->setCell($i, 2);
        }
        for ($i = 7; $i <= 9; $i++) {
            $test = $g->setCell($i, 3);
        }
        $test = $g->getRow(1);
        $this->assertEquals(3, array_sum($test));
        $test = $g->getRow(2);
        $this->assertEquals(6, array_sum($test));
        $test = $g->getRow(3);
        $this->assertEquals(9, array_sum($test));

        //Test an invalid row request
        $test = FALSE;
        try {
            $g->getRow(0);
        } catch (RangeException $e) {
            $test = TRUE;
        }
        $this->assertEquals(TRUE, $test);
    }

    public function testGetCol () {
        $g = new grid();
        
        //Set some values to text based on
        for ($i = 1; $i <= 9; $i = $i+3) {
            $test = $g->setCell($i, 1);
        }
        for ($i = 2; $i <= 9; $i = $i+3) {
            $test = $g->setCell($i, 2);
        }
        for ($i = 3; $i <= 9; $i = $i+3) {
            $test = $g->setCell($i, 3);
        }
        $test = $g->getCol(1);
        $this->assertEquals(3, array_sum($test));
        $test = $g->getCol(2);
        $this->assertEquals(6, array_sum($test));
        $test = $g->getCol(3);
        $this->assertEquals(9, array_sum($test));

        //Test an invalid col request
        $test = FALSE;
        try {
            $g->getCol(0);
        } catch (RangeException $e) {
            $test = TRUE;
        }
        $this->assertEquals(TRUE, $test);
    }

    public function testIsValid () {
        $g = new grid();
        
        //Ensure nulls are not ok for strict checks
        $this->assertEquals(FALSE, $g->isValid(FALSE));

        //Populate a valid set
        for ($i = 1; $i <= 9; $i++) {
            $test = $g->setCell($i, $i);
        }
        //Ensure we know it's valid for both strict and loose checks
        $this->assertEquals(TRUE, $g->isValid());
        $this->assertEquals(TRUE, $g->isValid(FALSE));

        //Ensure we detect duplicates correctly
        for ($i = 1; $i <= 9; $i++) {
            $test = $g->setCell($i, 1);
        }
        //Ensure we know it's invalid for both strict and loose checks
        $this->assertEquals(FALSE, $g->isValid());
        $this->assertEquals(FALSE, $g->isValid(FALSE));
    }
}
