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

// Initialize app
$app = new App();
$controller = new TestController("/");
// /path/to should override /path/*any
$controller->get("/path/to", ["prepend", "test", "test", "append"]);
// /path/*any/*any
$controller->get("/path/:cool_parameter_1/:cool_parameter_2", ["test_params"]);
// This should be /path/*any
$controller->get("/path/:any_parameter", ["test_params"]);
// This should be path /path/*any/eee. SHOULD override /path/*any/*any
$controller->get("/path/:parameter/eee", ["test_params"]);
// Also you can do the same but with POST requests
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
require './public/portfolio/index.php';
require './public/db_test/index.php';
$app->use($controller_portfolio);
$app->use($db_test_controller);
require './public/ecommerce/controllers/index.php';
startEcommerce($app);
$app->run();
?>







