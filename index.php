<?php include './express-php/express.php';?>
<?php
    // if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
        // $uri = 'https://';
    // } else {
        // $uri = 'http://';
    // }
    // $uri .= $_SERVER['HTTP_HOST'];
    // header('Location: '.$uri.'/index');
//
    class TestController extends Controller
    {
        public function test_params()
        {
            echo 'Parameters</br>';
            
            foreach (App::$uri_params as $key => $par) {
                echo '</br>';
                echo $key.' '.$par;
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
            App::json([ 'success' => 'yes', 'sent' => App::body(true) ]);
        }
    }
    $app = new App();
    $controller = new TestController("/lel");
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
            Html::prepend();
            Html::append(
                Html::create_el(
                    'div',
                    ['class' => 'cool'],
                    [Html::create_el('h1', [], 'Hey')]
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
                            ['href' => "/exercises/{$el}"],
                            $el
                        ));
                    })
                )
            );
            Html::end();
        }

        public function open_file()
        {
            $exercise_name = App::$uri_params['id'];
            if (!file_exists("./public/exercises/{$exercise_name}")) {
                App::set_response_header('Location', '/exercises');
                return;
            }
            $file = file_get_contents("./public/exercises/{$exercise_name}");

            echo str_replace("\n", "</br>", $file);
        }
    }
    $controller_exercise = new ExercisesController("/exercises");
    $controller_exercise->get("/", ["folderIndex"]);
    $controller_exercise->get("/:id", ['open_file']);
    $app->use($controller);
    $app->use($controller_exercise);
    $app->run();
?>
