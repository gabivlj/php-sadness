<?php

function downloadFile($filename, $downloadPath)
{

  // Set response headers so it streams the file into the browser
  App::set_response_header('Content-Type', 'text/plain');
  App::set_response_header('Pragma', 'no-cache');
  App::set_response_header(
    'Content-Disposition',
    "attachment; filename={$filename}"
  );
  readfile($downloadPath);
}


function downloadFolderASZip($path, $name, $diff)
{
  // Get real path for our folder
  Zipper::zipDir($path, $name, $diff);
  App::set_response_header('Content-Description', 'File Transfer');
  App::set_response_header('Content-Type', 'application/octet-stream');
  App::set_response_header('Content-Disposition', 'attachment; filename=output.zip');
  App::set_response_header('Content-Transfer-Encoding', 'binary');
  App::set_response_header('Expires', '0');
  App::set_response_header('Cache-Control', 'must-revalidate');
  App::set_response_header('Pragma', 'public');
  App::set_response_header('Content-Length', "" . filesize($name));
  readfile($name);
}

class Zipper
{

  /**
   * Zip a folder (include itself).
   * Usage:
   *   Zipper::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath, $diff)
  {
    // Initialize archive object
    $zip = new ZipArchive();
    $zip->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($sourcePath),
      RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
      // Skip directories (they would be added automatically)
      if (!$file->isDir()) {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();

        $relativePath = substr($filePath, strlen($sourcePath) + 1);
        $relativePath = implode(
          "/",
          array_diff(explode("/", $relativePath), $diff)
        );
        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
      }
    }

    // Zip archive will be created only after closing object
    $zip->close();
  }
}
