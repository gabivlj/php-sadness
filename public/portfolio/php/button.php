<?php

/**
 * Redirect button redirects to the new query state of the page that is included
 * on the parameters of the function.
 * @param Array<String, String> $tags The new key values to modify
 * @param String $name The value that will show the button
 */
function redirectButton($tags, $name, $deleteTags = [])
{
  $q = App::query_params();
  foreach ($tags as $tag => $value) {
    $q[$tag] = $value;
  }
  foreach ($deleteTags as $tag) {
    unset($q[$tag]);
  }
  $res = http_build_query($q);
  return new HtmlElement(
    "div",
    [
      "class" => "text-sm px-4 bg-teal-900 py-2 leading-none border text-white border-white 
      hover:border-transparent hover:text-teal-500 p-1 hover:bg-white w-36 redirecter rounded  cursor-pointer ",
      "href" => "/portfolio?{$res}"
    ],
    [$name]
  );
}
