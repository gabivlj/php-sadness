<?php

function filterOutDots($folders)
{
  return array_filter($folders, function ($folder) {
    return $folder !== '..' and $folder !== '.';
  });
}

function getAllUnits()
{
  $folders = scandir("./public/portfolio/exercises");
  if (!$folders) return [];
  $foldersFiltered = filterOutDots($folders);
  return array_map(function ($unit) {
    return ['title' => "Unit {$unit}", 'unit' => $unit, 'exercises' => getAllExercises($unit)];
  }, $foldersFiltered);
}

function getAllExercises($unitName)
{
  $exercises = scandir("./public/portfolio/exercises/{$unitName}");
  if (!$exercises) return [];
  $exercisesFiltered = filterOutDots($exercises);
  return array_map(function ($exercise) {
    return [$exercise, "Exercise {$exercise}"];
  }, $exercisesFiltered);
}

/**
 * Lists all the folders and files of an exercise
 * @return ["name" => "example", "is_folder" => false | true, "children" => morefiles... )]
 */
function getAllFilesExercise($unit, $exercise, $additional = '')
{
  $dir = "./public/portfolio/exercises/{$unit}/$exercise{$additional}";
  $files = filterOutDots(scandir($dir));
  return array_map(function ($file) use ($additional, $dir, $unit, $exercise) {
    $isFolder = is_dir("{$dir}/{$file}");
    $element = ['name' => $file, 'is_folder' => $isFolder, 'children' => []];
    if ($isFolder) {
      $element['children'] = getAllFilesExercise($unit, $exercise, $additional . "/{$file}");
    }
    return $element;
  }, $files);
}
