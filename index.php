<?php include './express-php/express.php';?>
<?php
    // if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
        // $uri = 'https://';
    // } else {
        // $uri = 'http://';
    // }
    // $uri .= $_SERVER['HTTP_HOST'];
    // header('Location: '.$uri.'/index');
    $app = new App();
    $controller = new TestController("/lel");
    // todo: test should be inside an array of callbacks!
    $controller->get("/path/damn", "test");
    $controller->post("/path", "postHandler");
    $app->use($controller);
    $app->run();
?>
