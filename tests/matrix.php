<?php

require '../src/Hungarian.php';

$array = [
    [1,2,3,0,1],
    [0,2,3,12,1],
    [3,0,1,13,1],
    [3,1,1,12,0],
    [3,1,1,12,0],
];

$hungarian  = new \RPFK\Matrix\Hungarian($array);

$hungarian->solve();

var_dump($hungarian->getMatrix());
var_dump($hungarian->allocation());
var_dump($hungarian->allocated_rows, $hungarian->allocated_columns);