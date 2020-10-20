<?php

require './public/internals/math.php';

$toModify = 0;
echo "Prev: {$toModify}";
$toModify = add($toModify);
echo "<br>After add: {$toModify}<br>";
$toModify = substract($toModify);
echo "After substract: {$toModify}<br>";
