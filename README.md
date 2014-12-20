# sudoku-gen

Requires PHP 5.4+

If you want to run unit testing code coverage you will need 
XDebug >=2.0.5

## Docs


## Examples

Generate a solved sudoku 

    > ./main.php

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
