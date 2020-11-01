<?php


function createFile($path, $content)
{
  $fileStream = fopen("$path", "w");
  // chmod($path, 0777);
  echo fwrite($fileStream, $content);
  fclose($fileStream);
}
