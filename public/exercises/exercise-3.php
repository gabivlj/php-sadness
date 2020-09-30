
<?php
  echo '
  1.	Can you produce this output using single quote strings?
  \ at the end of the line?   >>>			Hello World\ 
  \’ at any point in the line?   >>>			Hello \’ world
  </br>
  No
  </br>
  2.	Why do I need to put double slash in “c:\\xampp”? What happens if I do not put them?
  </br>
  \ is used for escape secuences.
  </br>
  3.	What can a variable store?  Show the four different scalar types using var_dump.
  Objects, strings, numbers, arrays.
  </br>
  4.	What is the difference between $variable = 1 and $variable == 1?
  Assignment, comparison.
  </br>
  5.	Underscore is allowed in variable names ($current_user), whereas hyphens are not ($current-user). Why? (make a guess) 
  </br>
  Because when parsing the code it’s more optimal to assume that “-“ is a subtraction operation than making the whole pass on the string to check if it’s a variable.
  </br>
  6.	Are variable names case-sensitive?
  </br>
  Yes.
  </br>
  7.	Can you use spaces in variable names?
  </br>
  No.
  </br>
  8.	How do you explicitly convert one variable type to another (say, a string to a number)?
  I don’t know check the docs bro.
  </br>
  9.	What is the difference between ++$j and $j++?
  The operation is made on that instant, the other one is added to the stack of operations.
  </br>
  10.	Are the operators && and and interchangeable?
  Well && has higher preference.
  </br>
  11.	Can you redefine a constant?
  I hope not, because if you can PHP is so bad it’s unreal.
  </br>
  12.	When would you use the === (identity) operator?
  Checking the type.
  </br>
  13.	Why is a for loop more powerful than a while loop?
  You can iter. The collection without initializing and adding an index.
  </br>
  14.	What happens when there is an overflow in an integer value? To check it, create a loop that increases an “integer variable” and overflows it. Use var_dump so we can see the moment where the overflow occurs. (If you iterate from 0 it will take too much time, use the constants in PHP, slide 23, to iterate only “around” the overflow).
  </br>
  Starts over again.
  </br>
  15 16 and 17.</br>';


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

?>
