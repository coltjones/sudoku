# sudoku-gen

Requires PHP 5.4+

If you want to run unit testing code coverage you will need 
XDebug >=2.0.5

## Docs

Grid/Cell layout These locations should be used for initializing cell values

    > [1, 2, 3,  10,11,12, 19,20,21]
    > [4, 5, 6,  13,14,15, 22,23,24]
    > [7, 8, 9,  16,17,18, 25,26,27]
    >
    > [28,29,30, 37,38,39, 46,47,48]
    > [31,32,33, 40,41,42, 49,50,51]
    > [34,35,36, 43,44,45, 52,53,54]
    >
    > [55,56,57, 64,65,66, 73,74,75]
    > [58,59,60, 67,68,69, 76,77,78]
    > [61,62,63, 70,71,72, 79,80,81]

## Examples

Generate a solved sudoku 

    > ./main.php

Solve a puzzle given initial values

    > ./sampleSolve.php

## Tests

Have composer install any dependancies.

    > php composer.phar update

Run the tests

    > vendor/bin/phpunit tests

Run the tests with code coverage (Requires XDebug support in your PHP install)

    > vendor/bin/phpunit --coverage-html ./report tests

## Confirmation

Run the following to get a 'seed' string for [Sudoku Solutions](http://www.sudoku-solutions.com/)

    > ./main.php | perl -ne 'while(/\d/g){print "$&";}' | xargs echo

## License

MIT
