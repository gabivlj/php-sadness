<?php
require './public/portfolio/php/icons.php';
function buttonNode($name, $isFile = false, $path = '')
{
  $iconElement = $isFile ? fileIcon() : folderIcon();
  $q = App::query_params();
  $q['file'] = $path;
  $res = http_build_query($q);
  $class = $isFile ? 'file' : 'folder';
  return new HtmlElement(
    !$isFile ? 'div' : 'a',
    ['class' => "inline-flex items-center mt-4 cursor-pointer text-teal-700 hover:text-teal-400 {$class} mr-4", "href" => "/portfolio?{$res}"],
    [$iconElement, $name]
  );
}


function showTree($treeNode, $depth = 1, $path = '')
{
  $tree = new HtmlElement("div", [], []);
  foreach ($treeNode as $name => $value) {
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
    $tree->append(new HtmlElement(
      "div",
      ['class' => 'ml-3'],
      [buttonNode("{$name}", !$isFolder, $newPath), new HtmlElement('div', [], $displayChildren)]
    ));
  }
  return $tree;
}
