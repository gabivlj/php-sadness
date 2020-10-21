<?php

require './public/portfolio/php/button.php';

/**
 * Util function that hides and shows the button stack of a div.
 */
function navButton($text, $unit, $exercises = [])
{
  $buttons = [];
  foreach ($exercises as $exercise) {
    array_push($buttons, redirectButton(
      ["exercise" => $exercise[0], 'unit' => $unit],
      $exercise[1]
    ));
  }
  $buttonsStack =  new HtmlElement(
    "div",
    ["class" => "showme bg-teal-900 z-0 absolute"],
    $buttons
  );
  return new HtmlElement(
    "div",
    ["class" => "showhim inline-block"],
    [new HtmlElement(
      "div",
      ["class" => "text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-teal-500 hover:bg-white mt-4 lg:mt-0"],
      [$text]
    ), $buttonsStack]
  );
}

/**
 * Shows all the unit data
 * @param [[title => String, exercises => [Redirect, Name]]] $units
 */
function section($units)
{
  $unitElements = [];
  foreach ($units as $unit) {
    array_push($unitElements, navButton($unit['title'], $unit['unit'], $unit['exercises']));
  }
  return $unitElements;
}

function nav($units)
{
  return new HtmlElement(
    "nav",
    ["class" => "flex items-center justify-between flex-wrap p-6 bg-teal-900"],
    [new HtmlElement(
      "div",
      ["class" => "w-full block flex-grow lg:flex lg:items-center lg:w-auto"],
      [new HtmlElement(
        "div",
        ["class" => "text-sm lg:flex-grow"],
        section($units)
      )]
    )]
  );
}
