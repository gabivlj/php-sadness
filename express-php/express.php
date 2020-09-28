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


function map_html($arr, $callback)
{
    $str = "";
    foreach ($arr as $element) {
        $str .= $callback($element);
    }
    return $str;
}

/// Global lib state for simple helper methods

function getRequestHeaders()
{
    $headers = array();
    foreach ($_SERVER as $key => $value) {
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
    public static $rendered_html = false;
    public static $parsed_headers = null;
    public static $query = [];
    public static $uri_params = [];


    public static function query_params()
    {
        $queries = array();
        parse_str($_SERVER['QUERY_STRING'], $queries);
        App::$query = $queries;
        return $queries;
    }

    public static function status_code($code)
    {
        http_response_code($code);
    }

    public static function raw($data)
    {
        echo $data;
    }

    public static function body($json = false)
    {
        $entityBody = file_get_contents('php://input');
        if ($json) {
            $entityBody = json_decode($entityBody);
        }
        return $entityBody;
    }

    public static function json($object)
    {
        App::set_response_header("Content-Type", "application/json");
        echo json_encode($object);
    }

    public static function set_response_header($key, $val)
    {
        header($key.': '.$val, true);
    }

    public static function get_header($key)
    {
        if (App::$parsed_headers == null) {
            App::$parsed_headers = getRequestHeaders();
        }
        return isset(App::$parsed_headers[$key]) ? App::$parsed_headers[$key] : '';
    }

    /**
     * Renders HTML text. By default it will append html and body.
     */
    public static function html($render, $appendDefaults = true)
    {
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
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->process_get($path);
                break;
            case 'POST':
                $this->process_post($path);
                break;
            default: echo 'Not implemented.';
        }
    }

    public function process_get($path)
    {
        foreach ($this->controllers as $key) {
            $callback_names = $key->routes_tree_get->get($path);
            if ($callback_names) {
                foreach ($callback_names as $callback_name) {
                    if ($key->end) {
                        break;
                    }
                    $key->{$callback_name}();
                }
            }
        }
    }

    public function process_post($path)
    {
        foreach ($this->controllers as $key) {
            $callback_names = $key->routes_tree_post->get($path);
            if ($callback_names) {
                foreach ($callback_names as $callback_name) {
                    if ($key->end) {
                        break;
                    }
                    $key->{$callback_name}();
                }
            }
        }
    }
}

class Controller
{
    public $main_route;
    public $routes_tree_get;
    public $routes_tree_post;
    public $end;
    public $ctx;

    public function stop()
    {
        $this->end = true;
    }

    public function __construct($main_route)
    {
        $this->main_route = $main_route;
        $this->routes_tree_get = new Tree('');
        $this->ctx = [];

        $this->routes_tree_post = new Tree('');
    }
    

    public function get($total_path, $nameOfCallback)
    {
        $this->routes_tree_get->add_path($this->main_route.$total_path, $nameOfCallback);
    }

    public function post($total_path, $nameOfCallback)
    {
        $this->routes_tree_post->add_path($this->main_route.$total_path, $nameOfCallback);
    }

    public function put()
    {
    }

    public function delete()
    {
    }
}

// This will be the route tree
class Tree
{
    public $trees;
    public $param_name;

    
    public function __construct($param_name_const)
    {
        $this->trees = [];
        $this->param_name = $param_name_const;
    }

    public function add_path($path, $data)
    {
        $arr = explode('/', $path);
        $current_tree = $this;
        $param_name = '';
        foreach ($arr as $word) {
            if ($word == "") {
                continue;
            }
            // echo $word.':wa\n';
            if ($word[0] == ':') {
                $param_name = substr($word, 1);
                $word = '*any';
            }
            if (!isset($current_tree->trees[$word])) {
                $current_tree->trees[$word] = new Tree($param_name);
            }
            $current_tree = $current_tree->trees[$word];
        }
        $current_tree->trees['*end'] = $data;
    }

    public function get($path)
    {
        $arr = explode('/', $path);
        $current_tree = $this->trees;

        foreach ($arr as $word) {
            if ($word == "") {
                continue;
            }
            if (!isset($current_tree[$word]) and !isset($current_tree['*any'])) {
                return null;
            }
            if (!isset($current_tree[$word]) and isset($current_tree['*any'])) {
                App::$uri_params[$current_tree['*any']->param_name] = $word;
                $current_tree = $current_tree['*any']->trees;
                continue;
            }
            $current_tree = $current_tree[$word]->trees;
        }
        return isset($current_tree['*end']) ? $current_tree['*end'] : null;
    }
}
