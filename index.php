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
            echo 'xd';
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
    $controller->get("/path/damn", ["prepend", "test", "test", "append"]);
    $controller->get("/path/:damn/:xd", ["test_params"]);
    $controller->get("/path/:damn", ["test_params"]);
    $controller->post("/path", ["postHandler"]);
    $app->use($controller);
    $app->run();
?>
