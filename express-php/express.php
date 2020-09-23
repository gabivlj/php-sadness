<?php

function requestPath()
{
    // var_dump($_SERVER['REQUEST_URI']);
    $request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $script_name = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    $parts = array_diff_assoc($request_uri, $script_name);
    if (empty($parts)) {
        return '/';
    }
    $path = implode('/', $parts);
    if (($position = strpos($path, '?')) !== false) {
        $path = substr($path, 0, $position);
    }
    return $path;
}

/// Global lib state for simple helper methods

function getRequestHeaders() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

class App
{
    private $controllers;
    static $rendered_html = false;
    static $parsed_headers = null;

    static function status_code($code) {
       http_response_code($code); 
    }

    static function raw($data) {
        echo $data;        
    } 

    static function body($json = false) {
        $entityBody = file_get_contents('php://input');
        if ($json) {
            $entityBody = json_decode($entityBody);
        } 
        return $entityBody;
    }

    static function json($object) {
        App::set_response_header("Content-Type", "application/json");
        echo json_encode($object);
    }

    static function set_response_header($key, $val) {
        header($key.': '.$val, true);
    }

    static function get_header($key) {
        if (App::$parsed_headers == null) {
            App::$parsed_headers = getRequestHeaders();  
        }
        return isset(App::$parsed_headers[$key]) ? App::$parsed_headers[$key] : '';
    }

   /**
    * Renders HTML text. By default it will append html and body.
    */ 
    static function html($render, $appendDefaults = true) {
        if (!App::$rendered_html && $appendDefaults) {
            echo '<html><head></head><body>';
        }

        echo $render;

        if (!App::$rendered_html && $appendDefaults) {
            echo '</body></html>';
        }  
        
        App::$rendered_html = true;
    }
    
    public function __construct()
    {
        $this->controllers = [];
    }

    public function use($controller)
    {
        assert(get_parent_class($controller) == "Controller" || get_class($controller) == "Controller", "Type/Parent should be Controller type");
        array_push($this->controllers, $controller);
    }


    public function run()
    {
        $path = requestPath();
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->process_get($path);
                break;
            case 'POST':
                $this->process_post($path);
                break;
            default: echo 'Not implemented.';    
        }
    }

    function process_get($path) {
        foreach ($this->controllers as $key) {
            $callback_name = $key->routes_tree_get->get($path);
            if ($callback_name) {
                $key->{$callback_name}();
                break;
            } 
        }
    }

    function process_post($path) {
        foreach ($this->controllers as $key) {
            $callback_name = $key->routes_tree_post->get($path);
            if ($callback_name) {
                $key->{$callback_name}();
                break;
            } 
        }
    }
        
     
}

class Controller 
{
    public $main_route;
    public $routes_tree_get;
    public $routes_tree_post;

    public function __construct($main_route) {
        $this->main_route = $main_route;
        $this->routes_tree_get = new Tree();

        $this->routes_tree_post = new Tree();
    }
    

    public function get($total_path, $nameOfCallback) {
        $this->routes_tree_get->add_path($this->main_route.$total_path, $nameOfCallback);
    }

    public function post($total_path, $nameOfCallback) {
        $this->routes_tree_post->add_path($this->main_route.$total_path, $nameOfCallback);
    }

    public function put() {}

    public function delete() {}

    
}

class TestController extends Controller {
    function test() {
        App::html("<h1>Cool thing</h1>");
    }

// todo: Tell a way to stop executing middleware :)
    function postHandler() {
        App::status_code(201);
        App::json(['success' => 'yes', 'sent' => App::body(true) ]);
    }
}

// This will be the route tree
class Tree {
    public $trees;

    
    function __construct() {
        $this->trees = [];
    }

    public function add_path($path, $data) {
        $arr = explode('/', $path);
        $current_tree = $this;
        foreach($arr as $word) {
            if ($word == "") continue;
            if (!isset($current_tree->trees[$word])) {
                $current_tree->trees[$word] = new Tree();
            }
            $current_tree = $current_tree->trees[$word];
        }
        $current_tree->trees['*end'] = $data;
    }

    public function get($path) {
        $arr = explode('/', $path);
        $current_tree = $this->trees;

        foreach($arr as $word) {
            if ($word == "") continue;
            if (!isset($current_tree[$word])) return null;
            $current_tree = $current_tree[$word]->trees;
        }
        return isset($current_tree['*end']) ? $current_tree['*end'] : null;
    }

}
