<?php

Html::append(Html::create_el("p", [], "2.	Can you create a function to create lists in HTML? It should receive an array with the elements of the list, and an optional parameter to specify the type of list. It will return the string with the resulting HTML. It shouldn’t modify the arrays."));

function createList($list, $type = "ul")
{
  return Html::create_el($type, [], map_html($list, function ($el) {
    return Html::create_el("li", [], $el);
  }));
}

Html::append(
  createList(["apple", "orange", "banana"])
);

Html::append(
  Html::create_el(
    "p",
    [],
    "3.	Can you create a function to create tables in HTML? It should receive a multidimensional array, with the data to fill the HTML table. It will return the string with the resulting HTML. It shouldn’t modify the strings."
  )
);

function table($matrix)
{

  return Html::create_el(
    "table",
    ["class" => "table-fixed"],
    [
      Html::create_el(
        "thead",
        [],
        map_html(
          array_slice($matrix, 0, 1),
          function ($list) {
            return Html::create_el("tr", ["class" => "bg-gray-100"], map_html($list, function ($el) {
              return Html::create_el("td", ["class" => "border px-4 py-2"], $el);
            }));
          }
        )
      ),
      map_html(array_slice($matrix, 1), function ($list) {
        return Html::create_el("tr", [], map_html($list, function ($el) {
          return Html::create_el("td", ["class" => "border px-4 py-2"], $el);
        }));
      })
    ]
  );
}

Html::append(Html::create_el("div", [], table([['Number', 'Cool', 'Yes', '!'], ['1', '2', '3', '4']])));

Html::append(Html::create_el("p", [], "4.	Functions can be recursive? If so, create a function that performs a deep count of the elements in a multidimensional array. (It has to count elements inside arrays) 
5.	Modify the function to also calculate the deepest level (regular array is a level 1 array). 6.	Combine both so you return an array with the two “descriptions” (totalCount,deepestLevel) and the two values."));

function deepCount($el, $level = 1)
{
  $count = 0;
  $max = $level;
  foreach ($el as $element) {
    if (is_array($element)) {
      $res = deepCount($element, $level + 1);
      $count += $res[0];
      $max = max($res[1], $max);
    } else {
      $count++;
    }
  }
  return [$count, $max];
}

$c = deepCount([[1, 2, 3, [1, 2, 3, [[[[[1]]]]]], 1], [3]]); // ==> 9
Html::append(Html::create_el("div", [], "Count: $c[0] Max: $c[1]"));

Html::append(Html::create_el("p", [], "7.	Can you create a function to build up arrays from two strings? The first string will include comma separated keys for the array. The second string will include comma separated values."));

function buildup($str = '', $str2 = '')
{
  $str = explode(',', $str);
  $str2 = explode(',', $str2);
  $len = count($str);
  $build = [];
  for ($i = 0; $i < $len; $i++) {
    $build[$str[$i]] = $str2[$i];
  }
  return $build;
}

var_dump(buildup("1,2,3,4", "cool,thing,tho,:)"));

Html::append(Html::create_el("p", [], "8.	Can you create a function that traverses an array and modifies it: When finds a number turn it into a string, when find a string turns it into a number. (Test it with strings containing numbers and with random strings)."));

function juggling($arr)
{
  $len = count($arr);
  for ($i = 0; $i < $len; $i++) {
    if (is_int($arr[$i])) {
      $arr[$i] = "$arr[$i]";
    } else {
      $arr[$i] = intval($arr[$i]);
    }
  }
  return $arr;
}

var_dump(juggling([1, '2', '3', 4]));
