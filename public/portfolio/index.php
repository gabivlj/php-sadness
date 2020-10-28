<?php

class Portfolio extends Controller
{

  function index()
  {
    $query_params = App::query_params();
    // Time to download some files!
    if (
      isset($query_params['download']) &&
      isset($query_params['file']) &&
      isset($query_params['unit'])
    ) {
      $fileNameForDownload = str_replace(">", "", $query_params['file']);

      // Set response headers so it streams the file into the browser
      App::set_response_header('Content-Type', 'text/plain');
      App::set_response_header('Pragma', 'no-cache');
      App::set_response_header(
        'Content-Disposition',
        "attachment; filename={$fileNameForDownload}"
      );

      // Normalized path
      $normalizedPath = str_replace(">", "/", $query_params['file']);

      // Extract query params
      $unit = $query_params['unit'];
      $exercise = $query_params['exercise'];

      // Get complete normalized path
      $completePath = "public/portfolio/exercises/$unit/$exercise/$normalizedPath";
      readfile($completePath);
      return;
    }

    // Prepare all the dependencies and tell the framework that we will be rendering HTML
    Html::prep(['//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/styles/default.min.css']);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    $projectCSS = file_get_contents('./public/portfolio/css/style.css');
    Html::append(HtmlElement::Style("$tailwindCSS $projectCSS")->render());

    // Require PHP components
    require './public/portfolio/php/navbar.php';
    require './public/portfolio/php/finder.php';
    require './public/portfolio/php/file_tree.php';

    // Initialize highlight library
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
      // Get all the files in a bit data structure like a tree
      $tree = getAllFilesExercise($query_params['unit'], $query_params['exercise']);

      // Showcase the tree in the DOM
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

          // Button controls
          $container->append(new HTMLElement('div', ['class' => 'flex-1 w-4 m-4'], [
            redirectButton(['interpret' => 'true'], 'Interpret', ['download']),
            redirectButton(['interpret' => 'false'], 'Show file', ['download']),
            redirectButton(['download' => 'true'], 'Download file')
          ]));
        }
      }
      $body->append($container);
    }

    // Add the body into the DOM
    Html::append($body->render());

    // Finish adding the last Javascript components and dependencies
    Html::append(HtmlElement::Script('hljs.initHighlightingOnLoad();')->render());
    Html::append(HtmlElement::Javascript("./public/portfolio/js/add_redirects.js")->render());
    Html::append(HtmlElement::Javascript("./public/portfolio/js/wrapper-php.js")->render());
    Html::append(HtmlElement::Javascript("./public/portfolio/js/folder.js")->render());

    // Tell the framework that we finished rendering HTML
    Html::finish();
  }
}

$controller_portfolio = new Portfolio("/portfolio");
$controller_portfolio->get("/", ['index']);
