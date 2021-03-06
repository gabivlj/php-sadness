<?php
require './public/portfolio/php/icons.php';
function buttonNode($name, $isFile = false, $path = '')
{
  $iconElement = $isFile ? fileIcon() : folderIcon();
  // Update the query params for exampleto: unit=2&exercise=3&file=folder>exercise
  $q = App::query_params();
  $q['file'] = $path;
  $res = http_build_query($q);
  return new HtmlElement(
    !$isFile ? 'div' : 'a',
    ['class' => "inline-flex items-center mt-4 cursor-pointer text-teal-700 hover:text-teal-400 mr-4", "href" => "/portfolio?{$res}"],
    [$iconElement, $name]
  );
}


function showTree($treeNode, $depth = 1, $path = '')
{
  $tree = new HtmlElement("div", ['class' => 'flex-1 max-w-sm'], []);
  foreach ($treeNode as $_ => $value) {
    if (!isset($value['name'])) {
      continue;
    }
    $name = $value['name'];
    $isFolder = $value['is_folder'];
    $children = $value['children'];
    $displayChildren = [];
    $pathFormat = strlen($path) > 0 ? "$path>" : $path;
    $newPath = "{$pathFormat}{$name}";
    if ($isFolder) {
      array_push($displayChildren, showTree($children, $depth + 1, $newPath));
    }
    $folderDisplay = $isFolder ? "folder" : '';
    $tree->append(new HtmlElement(
      "div",
      ['class' => 'ml-3'],
      [buttonNode("{$name}", !$isFolder, $newPath), new HtmlElement('div', ['class' => "$folderDisplay"], $displayChildren)]
    ));
  }
  return $tree;
}
