<?php


function mult($mult, $start, $end)
{
    $i = $start;
    while ($i <= $end) {
        $res = $i * $mult;
        echo "$res </br>";
        $i++;
    }
}

mult(3, -3, 10);

echo "18.	Tables in HTML are really easy (https://www.w3schools.com/html/html_tables.asp) Can you create a loop to print the ASCII table (for each element print the integer value and the resulting char)?";

$i = 0;
while ($i < 256) {
    $i++;
}

echo "19.	Create a loop from 0 to x that prints even numbers (I am #, an even number) and numbers that are multiple of 3 ( I am #, multiple of 3). If both conditions happen at the same time only one line should be produced (I am #, an even number multiple of 3).
  </br>
  ";
$i = 0;
while ($i < 100) {
    $msg = "";
    if ($i % 2 === 0 && $i % 3 === 0) {
        echo "I am $i an even multiple of 3";
    } elseif ($i % 3 === 0) {
        echo "I am $i, a multiple of 3";
    } elseif ($i % 2 === 0) {
        echo "I am $i, an even number";
    }
    echo "</br>";
    $i++;
}
