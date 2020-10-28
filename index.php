<?php include './express-php/express.php'; ?>
<?php
// if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
// $uri = 'https://';
// } else {
// $uri = 'http://';
// }
// $uri .= $_SERVER['HTTP_HOST'];
// header('Location: '.$uri.'/index');
//
// if ($_SERVER['HTTPS'] == 'on') {
//     $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//     header('Location: ' . $url, true, 301);
//     exit();
// }
class TestController extends Controller
{
    public function test_params()
    {
        echo 'Parameters</br>';

        foreach (App::$uri_params as $key => $par) {
            echo '</br>';
            echo $key . ' ' . $par;
        }
    }

    public function prepend()
    {
        App::html("<html><head></head><body><p>????</p>", false);
        $cosas = ["sss", "bbb", "lmao"];
        $arr = map_html($cosas, function ($el) {
            return "<h3>{$el}</h3>";
        });
        App::html("$arr", false);
    }

    public function append()
    {
        App::html("</body></html>", false);
    }

    public function test()
    {
        $inter = isset($this->ctx['cool']) ? $this->ctx['cool'] : '';
        App::html("<h1>Cool {$inter}</h1>", false);
        // $this->stop();
        $this->ctx['cool'] = 'Not cool';
    }

    public function postHandler()
    {
        App::status_code(201);
        App::json(['success' => 'yes', 'sent' => App::body(true)]);
    }
}
$app = new App();
$controller = new TestController("/");
// /path/damn should override /path/*any
$controller->get("/path/damn", ["prepend", "test", "test", "append"]);
// /path/*any/*any
$controller->get("/path/:damn/:xd", ["test_params"]);
// This should be /path/*any
$controller->get("/path/:damn", ["test_params"]);
// This should be path /path/*any/heee. SHOULD override /path/*any/*any
$controller->get("/path/:damn/heee", ["test_params"]);
$controller->post("/path", ["postHandler"]);
class ExercisesController extends Controller
{
    public function folderIndex()
    {
        $resDir = scandir("./public/exercises");
        Html::prepend(['https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css']);
        Html::append(
            Html::create_el(
                'div',
                ['class' => 'ml-56 text-6xl underline'],
                [Html::create_el('h1', [], 'Exercises')]
            )
        );

        Html::append(
            Html::create_el(
                "ul",
                [],
                map_html($resDir, function ($el) {
                    if ($el === '.' or $el === '..') {
                        return '';
                    }
                    return Html::create_el("li", [], Html::create_el(
                        "a",
                        ['href' => "/exercises/{$el}", "class" => "ml-56 underline text-xl text-green-700 hover:text-green-500"],
                        str_replace(".php", "", ucfirst(implode(' ', explode("-", $el))))
                    ));
                })
            )
        );

        Html::end();
    }

    public function open_file()
    {
        Html::prepend(['https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css']);
        $exercise_name = App::$uri_params['id'];
        if (!file_exists("./public/exercises/{$exercise_name}")) {
            App::set_response_header('Location', '/exercises');
            return;
        }
        Html::append("<div class=\"ml-5 container\">");
        App::serve_php("./public/exercises/{$exercise_name}");
        require './public/internals/github.php';
        Html::append("</div>");
        Html::append("<h2 class='text-xl ml-5 mt-56'>Code of this exercise:</h2>");
        Html::append("<div class='container ml-5 mt-3'>");
        Github::showPieceOfCode("https://github.com/gabivlj/php-sadness/blob/master/public/exercises/{$exercise_name}");
        Html::append("</div>");
        Html::end();
    }
}
$controller_exercise = new ExercisesController("/exercises");
$controller_exercise->get("/", ["folderIndex"]);
$controller_exercise->get("/:id", ['open_file']);
$app->use($controller);
$app->use($controller_exercise);


class Portfolio extends Controller
{

    function index()
    {
        Html::prep(['//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/styles/default.min.css']);
        $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
        $projectCSS = file_get_contents('./public/portfolio/css/style.css');
        Html::append(HtmlElement::Style("$tailwindCSS $projectCSS")->render());
        require './public/portfolio/php/navbar.php';
        require './public/portfolio/php/finder.php';
        require './public/portfolio/php/file_tree.php';

        $body = HtmlElement::Body()
            ->append(
                HtmlElement::Javascript(
                    '//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/highlight.min.js'
                )
            )->append(nav(getAllUnits()));
        $query_params = App::query_params();
        $hasUnit = isset($query_params['unit']);
        $hasExercise = isset($query_params['exercise']);
        // Check if we want to show units/exercise fileFolder
        if ($hasUnit && $hasExercise) {
            $tree = getAllFilesExercise($query_params['unit'], $query_params['exercise']);
            $treeNode = showTree($tree);
            $container = new HtmlElement('div', ['class' => 'flex'], []);
            $container->append($treeNode);
            // Check if we want a file
            if (isset($query_params['file']) && $hasUnit && $hasExercise) {
                // extract parameters and sanitized
                $interpret = isset($query_params['interpret']) &&
                    $query_params['interpret'] == 'true';
                $normalizedPath = str_replace(">", "/", $query_params['file']);
                $unit = $query_params['unit'];
                $exercise = $query_params['exercise'];
                $completePath = "public/portfolio/exercises/$unit/$exercise/$normalizedPath";
                // Check if the path is good
                if (file_exists($completePath)) {
                    if ($interpret) {
                        $container->append(new HtmlElement('div', ['class' => 'wrapper-php flex-1 w-64 h-64 overflow-y-auto'], []));
                        // !! Important: we need to import the wrapper-php.js code so we get the 
                        // !! echoes and add it into the part of the dom that we want
                        Html::wrapPHPCode($completePath, 'wrapper-php');
                    } else {
                        // Just append the raw text into a syntax highlighter
                        $container->append(new HtmlElement(
                            'pre',
                            ['class' => 'flex-1 w-64 h-64 overflow-y-auto'],
                            [new HtmlElement('code', ['class' => 'max-w-screen-md h-64 overflow-y-auto'], [str_replace('<?php', '', file_get_contents($completePath))])]
                        ));
                    }
                    // Controls
                    $container->append(new HTMLElement('div', ['class' => 'flex-auto w-4 m-4'], [
                        redirectButton(['interpret' => 'true'], 'Interpret', ['download']),
                        redirectButton(['interpret' => 'false'], 'Show file', ['download']),
                        redirectButton(['download' => 'true'], 'Download file')
                    ]));
                }
            }
            $body->append($container);
        }
        Html::append($body->render());
        Html::append(HtmlElement::Script('hljs.initHighlightingOnLoad();')->render());
        Html::append(HtmlElement::Javascript("./public/portfolio/js/add_redirects.js")->render());
        Html::append(HtmlElement::Javascript("./public/portfolio/js/wrapper-php.js")->render());
        Html::append(HtmlElement::Javascript("./public/portfolio/js/folder.js")->render());
        Html::finish();
    }
}
$controller_portfolio = new Portfolio("/portfolio");
$controller_portfolio->get("/", ['index']);
$app->use($controller_portfolio);
$app->run();
?>







