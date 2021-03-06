<?php

class Portfolio extends Controller
{
  private $maximumUploadSize = 10485760; // 10MB

  function mkdir()
  {
    $exercise = App::get_form_value('exercise');
    $unit = App::get_form_value('unit');
    $path = App::get_form_value('path');
    if (!is_dir("./public/portfolio/exercises/$unit/$exercise/$path")) {
      mkdir("./public/portfolio/exercises/$unit/$exercise/$path", 0777, true);
    }
    App::json(['success' => true]);
  }

  function fileUpload()
  {
    $file = App::get_file('file');
    if ($file == null) {
      App::json([
        'success' => false,
        'message' => 'POST request should be included with the `file` parameter'
      ]);
      return;
    }
    if ($file['size'] > $this->maximumUploadSize) {
      App::json(['success' => false, 'message' => 'file too big...']);
      return;
    }
    $exercise = App::get_form_value('exercise');
    $unit = App::get_form_value('unit');
    $path = App::get_form_value('path');
    if (!is_dir("./public/portfolio/exercises/$unit/$exercise/$path")) {
      mkdir("./public/portfolio/exercises/$unit/$exercise/$path", 0777, true);
    }
    move_uploaded_file(
      $file["tmp_name"],
      "./public/portfolio/exercises/$unit/$exercise/$path{$file["name"]}"
    );
    App::json(['success' => true]);
  }

  function downloadZip()
  {
    $query_params = App::query_params();
    $path = str_replace(">", "/", $query_params['file']);
    $pieces = implode("/", array_slice(explode("/", $path), 0, -1));
    $parsedPath = "/$pieces";
    require './public/portfolio/php/download.php';
    // Extract query params
    $unit = $query_params['unit'];
    $exercise = $query_params['exercise'];
    $path = "./public/portfolio/exercises/{$unit}/{$exercise}{$parsedPath}";
    $temp_dir = sys_get_temp_dir();
    downloadFolderASZip(
      $path,
      tempnam($temp_dir, "Unit{$unit}Exercise$exercise.zip"),
      ['public', 'portfolio', 'exercises', "$unit", "$exercise"]
    );
  }

  function index()
  {
    $query_params = App::query_params();
    if (
      isset($query_params['download_all']) &&
      isset($query_params['unit']) && isset($query_params['exercise'])
      && isset($query_params['file'])
    ) {
      $this->downloadZip();
      return;
    }
    // Time to download some files!
    if (
      isset($query_params['download']) &&
      isset($query_params['file']) &&
      isset($query_params['unit'])
    ) {
      require './public/portfolio/php/download.php';
      $fileNameForDownload = str_replace(">", "", $query_params['file']);
      // Normalized path
      $normalizedPath = str_replace(">", "/", $query_params['file']);
      // Extract query params
      $unit = $query_params['unit'];
      $exercise = $query_params['exercise'];
      // Get complete normalized path
      $completePath = "public/portfolio/exercises/$unit/$exercise/$normalizedPath";
      downloadFile($fileNameForDownload, $completePath);
      return;
    }

    // Prepare all the dependencies and tell the framework that we will be rendering HTML
    HtmlRoot::prep(['//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/styles/default.min.css']);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    $projectCSS = file_get_contents('./public/portfolio/css/style.css');
    HtmlRoot::$head->append(
      HtmlElement::Style("$tailwindCSS $projectCSS")
    );

    // Require PHP components
    require './public/portfolio/php/navbar.php';
    require './public/portfolio/php/finder.php';
    require './public/portfolio/php/file_tree.php';
    require './public/portfolio/php/input.php';

    // Initialize highlight library and append nav
    $body = HtmlElement::Body()
      ->append(
        HtmlElement::Javascript(
          '//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/highlight.min.js'
        )
      )->append(nav(getAllUnits()));

    // Helper booleans
    $hasUnit = isset($query_params['unit']);
    $hasExercise = isset($query_params['exercise']);
    // Check if we want to show units/exercise fileFolder
    if ($hasUnit && $hasExercise) {
      // Get all the files in a data structure like a tree
      $tree = getAllFilesExercise($query_params['unit'], $query_params['exercise']);

      // Get the tree element 
      $treeNode = showTree($tree);

      // Create a container so we get a GRID
      $container = new HtmlElement('div', ['class' => 'flex'], []);
      // Append the tree into the container
      $container->append($treeNode);

      // Check if we want a file
      if (isset($query_params['file']) && $hasUnit && $hasExercise) {
        // extract parameters and sanitized
        $interpret = isset($query_params['interpret']) &&
          $query_params['interpret'] == 'true';

        // because we get in the query param something like path>to>file
        //  we replace with '/'
        $normalizedPath = str_replace(">", "/", $query_params['file']);

        // Get the values of the params
        $unit = $query_params['unit'];
        $exercise = $query_params['exercise'];

        // Complete normalized path
        $completePath = "public/portfolio/exercises/$unit/$exercise/$normalizedPath";

        // Check if the path is good
        if (file_exists($completePath)) {
          if ($interpret) {
            // Append into the container the wrapper-php DOM node,
            //  which will be added by some javascript file that I've created.
            $container->append(
              new HtmlElement(
                'div',
                [
                  'class' => 'wrapper-php flex-3 overflow-y-auto',
                  'style' => 'height:32rem;width:64rem;'
                ],
                []
              )
            );

            // !! Important: we need to import the wrapper-php.js code so we get the 
            // !! echoes and add it into the part of the dom that we want
            Html::wrapPHPCode($completePath, 'wrapper-php');
          } else {
            // Just append the raw text into a syntax highlighter
            $container->append(new HtmlElement(
              'pre',
              ['class' => 'flex-3 w-64 overflow-y-auto', 'style' => 'height:32rem;width:70%;'],
              [new HtmlElement('code', ['class' => ' overflow-y-auto'], [str_replace('<?php', '', file_get_contents($completePath))])]
            ));
          }
          $pathForCreateFile = implode("/", array_slice(explode("/", "$normalizedPath"), 0, -1));
          // Button controls
          $container->append(new HTMLElement('div', ['class' => 'flex-1 w-4 m-4'], [
            redirectButton(['interpret' => 'true'], 'Interpret', ['download', 'download_all']),
            redirectButton(['interpret' => 'false'], 'Show file', ['download', 'download_all']),
            redirectButton(['download' => 'true'], 'Download file'),
            redirectButton(['download_all' => 'true'], 'Download all files'),
            postButton('Create File', '/', [
              'path' => "$pathForCreateFile/", 'unit' => $unit, 'exercise' => $exercise
            ], "true"),
            input([
              'id' => 'inputFolder',
              'type' => 'text',
              'value' => "$pathForCreateFile",
              'placeholder' => 'Write some valid path here...'
            ]),
          ]));
        }
      }
      $body->append($container);
    }
    // Finish adding the last Javascript components and dependencies
    $body->append(HtmlElement::Script('hljs.initHighlightingOnLoad();'));
    $body->append(HtmlElement::Javascript("./public/portfolio/js/add_redirects.js"));
    $body->append(HtmlElement::Javascript("./public/portfolio/js/wrapper-php.js"));
    $body->append(HtmlElement::Javascript("./public/portfolio/js/folder.js"));
    $body->append(HtmlElement::Javascript("./public/portfolio/js/post_input.js"));
    // Add the body into the DOM
    HtmlRoot::append($body);
    // Tell the framework that we finished rendering HTML
    HtmlRoot::end();
  }
}

$controller_portfolio = new Portfolio("/portfolio");
$controller_portfolio->get("/", ['index']);
$controller_portfolio->post("/", ['fileUpload']);
$controller_portfolio->post("/mkdir", ['mkdir']);
