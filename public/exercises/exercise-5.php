<?php
Html::append(Html::create_el("h2", ["class" => "text-xl"], "1.	To test out the linking of files you will create three .php files:
•	add.php: add one to the variable declared in 1.php.
•	substract.php remove one to the variable declared in 1.php.
•	1.php, the root script:
i.	Declare a variable
ii.	Link add.php so the variable is increased (do it random times)
iii.	Link remove.php so the variable is decreased (do it random times)
iv.	Finally print the variable
"));

require './public/internals/1.php';
echo "Prev: {$toModify}<br>";
require './public/internals/add.php';
echo "After add: {$toModify}<br>";
require './public/internals/remove.php';
echo "After subs: {$toModify}<br>";
require './public/internals/1.php';


Html::append(Html::create_el("h2", ["class" => "text-xl"], "2.	Exercise 1 is a good example of   what shouldn’t be ever done! Instead of that, reformulate the previous code into:
  •	maths.php file with two functions to add and substract.
  •	2.php, doing the same as 1 but calling those functions.
  
"));

require './public/internals/2.php';

Html::append(Html::create_el("h2", ["class" => "text-xl"], "3.	What happens if we pass the returning string of a method to a function? Is it by reference or by value? Show it with an example.
"));


Html::append(Html::create_el("h2", ["class" => "text-md"], "Strings are passed by reference."));

function returnStrRef()
{
  return "Because if they are not passed by reference";
}

function modify($str)
{
  $str .= ", you might allocate big text everytime you call this function for no reason!";
  return $str;
}

echo modify(returnStrRef());


Html::append(Html::create_el("h2", ["class" => "text-xl"], "4.	What happens if we copy an array (using the assignment operator) that contains some objects? Are they copied or referenced? Show it with an example.
"));

Html::append(Html::create_el("h2", ["class" => "text-md"], "Arrays and objects are copied to their content."));

function test()
{
  $obj1 = [['cool' => ':)'], 0];
  echo "Before change: ";
  print_r($obj1);
  echo "<br>";
  $obj2 = $obj1;
  $obj2[0]['cool'] .= ' it did not change :)';
  echo "After change: ";
  print_r($obj1);
  // print_r($obj2);
  echo "<br>";
}

test();

Html::append(Html::create_el("h2", ["class" => "text-xl"], "5.	What happens if I try to set an object’s attribute that is not declared in the class? Will it work for all instances of that class or only for that specific object? What do you think that is happening? Show it with an example
"));

class TestClass
{
  public $cool;
  function __construct()
  {
    $this->cool = "eee";
  }
}
Html::append(Html::create_el("h2", ["class" => "text-md"], "It defines on that instance that attribute."));

$test_class = new TestClass();
print_r($test_class);
$test_class->ttt = 'eee';

print_r($test_class);

Html::append(Html::create_el("h2", ["class" => "text-xl"], "6.	Circular Reference how to avoid (example of the dog).
"));
Html::append(Html::create_el("h2", ["class" => "text-md"], "A circular dependency is when a class depends on another class and that class depends on the other one as well, you can prevent this with dependency injection, setters, etc."));

Html::append(Html::create_el("h2", ["class" => "text-xl"], "7.	Protected can be accessed from the children classes and from the parent class! Show it in an example accessing both, methods and attributes.
"));

class Parenting
{
  protected $thing;
  function __construct()
  {
    $this->thing = "thing";
  }

  protected function printThing()
  {
    echo "Printing thing again: {$this->thing}";
  }
}


class Son extends Parenting
{
  function __construct()
  {
    parent::__construct();
    echo $this->thing;
    echo "<br>";
    $this->printThing();
  }
}

$son = new Son();

Html::append(Html::create_el("h2", ["class" => "text-xl"], "8.	Create a class hierarchy including:
•	Abstract Figure class with two abstract methods and two attributes:
i.	Abstract method getArea()
ii.	Abstract method getPerimeter()
iii.	x
iv.	y
v.	implement also the __toString() method.
vi.	Static attribute to hold the count of the number of objects of this type that exists at a given time.
•	Class Circle extends figure and implements the two methods
i.	Add the attributes that you need to represent a circle (radius).
ii.	Implement the __toString() method.
iii.	Static attribute to hold the count of the number of objects of this type that exists at a given time.
•	Class Rectangle extends figure and implements the two methods
i.	Add the attributes that you need to represent a rectangle.
ii.	Implement the __toString() method.
iii.	Static attribute to hold the count of the number of objects of this type that exists at a given time.
•	Test that it works properly, you could need more things than requested (constructors, destructors, etc).

"));

abstract class Figure
{
  abstract function getArea();
  abstract function getPerimeter();
  public $x;
  public $y;
  function __toString()
  {
    return "Figure({$this->x}, {$this->y})";
  }

  static $numberOfFigures;

  function __construct($x, $y)
  {
    Figure::$numberOfFigures++;
    $this->x = $x;
    $this->y = $y;
  }

  function __destruct()
  {
    Figure::$numberOfFigures--;
  }
}

class Circle extends Figure
{
  public $radius;
  static $numberOfCircles;

  function __construct($x, $y, $radius)
  {
    Circle::$numberOfCircles++;
    parent::__construct($x, $y);
    $this->radius = $radius;
  }

  function __destruct()
  {
    Circle::$numberOfCircles--;
  }

  function getPerimeter()
  {
    return 2 * pi() * $this->radius;
  }

  function getArea()
  {
    return $this->radius * $this->radius * pi();
  }


  function __toString()
  {
    return "Circle({$this->x}, {$this->y}, {$this->radius})";
  }
}


class Rectangle extends Figure
{
  public $width;
  public $height;
  static $numberOfRectangles;

  function __construct($x, $y, $width, $height)
  {
    Rectangle::$numberOfRectangles++;
    parent::__construct($x, $y);
    $this->width = $width;
    $this->height = $height;
  }

  function __destruct()
  {
    Rectangle::$numberOfRectangles--;
  }

  function getPerimeter()
  {
    return $this->width * 2 + $this->height * 2;
  }

  function getArea()
  {
    return $this->width * $this->height;
  }


  function __toString()
  {
    return "Rectangle({$this->x}, {$this->y}, {$this->width}, {$this->height})";
  }
}
function br()
{
  echo "<br>";
}
$circle = new Circle(1, 1, 110);
$circle2 = new Circle(1, 1, 120);
$circle3 = new Circle(1, 1, 103);
print_r(Figure::$numberOfFigures);
br();
print_r($circle);
br();
print_r($circle->getArea());
br();
print_r($circle->getPerimeter());
br();

$rect = new Rectangle(1, 1, 102, 10);
$rect2 = new Rectangle(1, 1, 130, 10);
$rect3 = new Rectangle(1, 1, 140, 10);
print_r(Figure::$numberOfFigures);
br();
print_r($rect);
br();
print_r($rect->getArea());
br();
print_r($rect->getPerimeter());
br();


Html::append(Html::create_el("h2", ["class" => "text-xl"], "9.	Create a singleton class called FigureManager with the ability to:
•	Hold an array with Figures
•	Hold an array with Rectangles
•	Hold an array with Circles
•	Methods to create Circles or Rectangles (and store them in the proper array).
•	Methods to show the contents of each array.
•	A single method, remove() that receives an object and removes it from all the arrays.

"));

class FigureManager
{
  private static $instance;

  private function __construct()
  {
    if (FigureManager::$instance == null) {
      FigureManager::$instance = $this;
      $this->circles = [];
      $this->rectangles = [];
      $this->figures = [];
    }
  }

  private $figures;
  private $rectangles;
  private $circles;

  function add($anyTypeOfFigure)
  {
    array_push($this->figures, $anyTypeOfFigure);
    switch (get_class($anyTypeOfFigure)) {
      case 'Circle':
        array_push($this->circles, $anyTypeOfFigure);
        break;
      case 'Rectangle':
        array_push($this->rectangles, $anyTypeOfFigure);
        break;
    }
  }

  function remove(&$figure)
  {
    $this->figures = array_filter($this->figures, function (&$el) use (&$figure) {
      return $el != $figure;
    });
    switch (get_class($figure)) {
      case 'Circle':
        $this->circles = array_filter($this->circles, function (&$el) use (&$figure) {
          return $el != $figure;
        });
        break;
      case 'Rectangle':
        $this->rectangles = array_filter($this->rectangles, function (&$el) use (&$figure) {
          return $el != $figure;
        });
        break;
    }
  }

  function showCircles()
  {
    foreach ($this->circles as $circle) {
      print_r($circle);
      br();
    }
  }

  function showRectangles()
  {
    foreach ($this->rectangles as $circle) {
      print_r($circle);
      br();
    }
  }

  function showFigures()
  {
    foreach ($this->figures as $circle) {
      print_r($circle);
      br();
    }
  }

  static function get()
  {
    return FigureManager::$instance;
  }

  static function init()
  {
    new FigureManager();
  }
}


FigureManager::init();
FigureManager::get()->add($circle);
FigureManager::get()->add($circle2);
FigureManager::get()->add($circle3);
FigureManager::get()->add($rect);
FigureManager::get()->add($rect2);
FigureManager::get()->add($rect3);
FigureManager::get()->showCircles();
FigureManager::get()->showRectangles();
FigureManager::get()->showFigures();
FigureManager::get()->remove($circle2);
FigureManager::get()->showCircles();

Html::append(Html::create_el("h2", ["class" => "text-xl"], "10.	What is the starting point when declaring paths in a require_once? Is this a good idea or you think it can give you problems?
"));

Html::append(Html::create_el("h2", ["class" => "text-md"], "The starting point in require once is the root of the server, this is a bad idea because if there is a bad import, it will show the entire path in the error message."));

echo "You can use dirname to fix this so you can have dynamic filepaths";

Html::append(Html::create_el("h2", ["class" => "text-xl"], "11.	What is the starting point when declaring paths in a require_once? Is this a good idea or you think it can give you problems?
"));
