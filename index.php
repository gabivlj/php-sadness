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
    class TestController extends Controller {
        
        function prepend() {
            App::html("<html><head></head><body><p>????</p>", false);
        }

        function append() {
            App::html("</body></html>", false);
        }

        function test() {
            $inter = isset($this->ctx['cool']) ? $this->ctx['cool'] : '';
            App::html("<h1>Cool {$inter}</h1>", false);
            // $this->stop();
            $this->ctx['cool'] = 'Not cool';
        }

        function postHandler() {
            App::status_code(201);
            App::json([ 'success' => 'yes', 'sent' => App::body(true) ]);
        }
    }
    $app = new App();
    $controller = new TestController("/lel");
    $controller->get("/path/damn", ["prepend", "test", "test", "append"]);
    $controller->post("/path", ["postHandler"]);
    $app->use($controller);
    $app->run();
?>
