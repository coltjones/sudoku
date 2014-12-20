<?php 

require_once('includes.php');

class sudokuTest extends PHPUnit_Framework_TestCase {
    public function testSudokuConstruct () {
        $s = new sudoku();
        //Ensure data has 9 elements and all are instances of grid
        for ($i = 1; $i <= 9; $i++) {
            $test = FALSE;
            if (isset($s->data[$i])) {
                $test = TRUE;
            }
            $this->assertEquals(TRUE, $test);
            $test = $s->data[$i] instanceof grid;
            $this->assertEquals(TRUE, $test);
        }

        //Ensure the placementArr has 81 items
        $this->assertEquals(81, count($s->placementArr));

        //Ensure the first item is 1 and the last is 81
        $this->assertEquals(1, $s->placementArr[0]);
        $this->assertEquals(81, $s->placementArr[(count($s->placementArr)-1)]);
        
        //Ensure there is no gap in the values
        $foundGap = FALSE;
        foreach ($s->placementArr as $i => $v) {
            if (isset($s->placementArr[$i]) && isset($s->placementArr[$i+1])) {
                if (abs($s->placementArr[$i] - $s->placementArr[$i+1]) > 1) {
                    $foundGap = TRUE;
                }
            }
        }
        $this->assertEquals(FALSE, $foundGap);
        
        //Ensure we default to a NOT solved state
        $this->assertEquals(FALSE, $s->solved);
    }

    public function testSudokuClone () {
        $s1 = new sudoku();
        $s2 = clone $s1;

        //Ensure both objects are instances of sudoku and have different hashes
        $hash1 = spl_object_hash($s1);
        $hash2 = spl_object_hash($s2);

        $test = ($hash1 == $hash2)?TRUE:FALSE;
        $this->assertEquals(FALSE, $test);

        //Ensure grid count matches
        $this->assertEquals(count($s1->data), count($s2->data));
        
        //Ensure all the children are cloned too
        for ($i = 1; $i <= 9; $i++) {
            //Ensure proper object types
            $test = ($s1->data[$i] instanceof grid)?TRUE:FALSE;
            $this->assertEquals(TRUE, $test);
            $test = ($s2->data[$i] instanceof grid)?TRUE:FALSE;
            $this->assertEquals(TRUE, $test);
            
            //Ensure the objects are different
            $hash1 = spl_object_hash($s1->data[$i]);
            $hash2 = spl_object_hash($s2->data[$i]);
            $test = ($hash1 == $hash2)?TRUE:FALSE;
            $this->assertEquals(FALSE, $test);
        }
    }

    public function testGetGrid () {
        $s = new sudoku();
        
        //Ensure direct access and method access return the same thing
        for ($i = 1; $i <= 9; $i++) {
            $hash1 = spl_object_hash($s->data[$i]);
            $hash2 = spl_object_hash($s->getGrid($i));
            $test = ($hash1 == $hash2)?TRUE:FALSE;
            $this->assertEquals(TRUE, $test);
        }
    }

    public function testGenerate () {
        $s = new sudoku();

        //Ensure we return true
        $this->assertEquals(TRUE, $s->generate());
    }

    public function testGetPrintableGrid () {
        $s = new sudoku();
        
        //Ensure a grid populated with a known arrangment prints as it should (compare hashes)
        $knownMd5 = '374b41e354e21a40428be1b93a0f82a5';
        for ($i = 1; $i <= 9; $i++) {
            $g = $s->getGrid($i);
            $g->setCell($i, $i);
        }
        $this->assertEquals($knownMd5, md5($s->getPrintableGrid()));
    }

    public function testLookupCoords () {
        $s = new sudoku();
        
        //Ensure a few lookups are correct
        $test = md5(serialize([1,1]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(1))));
        $test = md5(serialize([2,2]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(11))));
        $test = md5(serialize([3,3]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(21))));
        $test = md5(serialize([4,4]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(31))));
        $test = md5(serialize([5,5]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(41))));
        $test = md5(serialize([6,6]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(51))));
        $test = md5(serialize([7,7]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(61))));
        $test = md5(serialize([8,8]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(71))));
        $test = md5(serialize([9,9]));
        $this->assertEquals($test, md5(serialize($s->lookupCoords(81))));
    }
}
