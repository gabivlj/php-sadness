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

class HtmlElement
{
    function __construct($tag, $atts, $content = [])
    {
        $this->tag = $tag;
        $this->atts = $atts;
        $this->content = $content;
        $this->raw = false;
    }

    static function raw($raw)
    {
        $el = new HtmlElement("", [], $raw);
        $el->raw = true;
        return $el;
    }

    static function Style($content)
    {
        return new HtmlElement("style", [], [$content]);
    }

    static function Javascript($path)
    {
        return new HtmlElement("script", ["src" => $path], []);
    }

    static function Script($content)
    {
        return new HtmlElement("script", [], [$content]);
    }

    static function Body()
    {
        return new HtmlElement("body", [], []);
    }

    function render()
    {

        $s = "";
        if ($this->raw) {
            return $this->content;
        }
        if (is_string($this->content)) {
            $s = $this->content;
        } else {
            foreach ($this->content as $child) {
                if (is_string($child)) {
                    $s .= $child;
                    continue;
                }
                $s .= $child->render();
            }
        }

        $attsStr = ' ';
        foreach ($this->atts as $att => $val) {
            $attsStr .= "{$att}=\"{$val}\"";
        }
        return "<{$this->tag}{$attsStr}>{$s}</{$this->tag}>";
    }

    function append($child)
    {
        if (is_string($child)) {
            array_push($this->content, new HtmlElement("p", [], $child));
            return $this;
        }
        array_push($this->content, $child);
        return $this;
    }
}

class HtmlRoot
{
    private static $html;
    public static $head;

    public static function prep($libraries)
    {
        HtmlRoot::$html = new HtmlElement("html", [], []);
        HtmlRoot::$head = new HtmlElement('head', [], []);
        foreach ($libraries as $lib) {
            HtmlRoot::$head
                ->append(new HtmlElement('link', ['href' => $lib, 'rel' => 'stylesheet']));
        }
        HtmlRoot::$html->append(HtmlRoot::$head);
    }

    public static function append($child)
    {
        HtmlRoot::$html->append($child);
    }

    public static function end()
    {
        Html::append(HtmlRoot::$html->render());
    }
}

class Html
{
    public static function prep($libraries = [])
    {
        $lib = map_html($libraries, function ($lib) {
            return Html::create_el('link', ['href' => $lib, 'rel' => 'stylesheet']);
        });
        echo "<html><head>{$lib}</head>";
    }

    public static function wrapPHPCode($file, $dst)
    {
        echo "<div id='WRAPPER_PHP_CODE' dst='$dst'>";
        require $file;
        echo "</div>";
    }

    public static function finish()
    {
        echo "</html>";
    }

    public static function prepend($libraries = [])
    {
        $lib = map_html($libraries, function ($lib) {
            return Html::create_el('link', ['href' => $lib, 'rel' => 'stylesheet']);
        });

        echo "<html><head>{$lib}</head><body>";
    }

    public static function end()
    {
        echo '</body></html>';
    }

    public static function append($comp)
    {
        echo $comp;
    }

    public static function create_el($name, $atts, $content = [])
    {
        $children = gettype($content) === 'array' ?
            map_html($content, function ($el) {
                return $el;
            }) :
            $content;
        assert(gettype($children) === 'string', "Content should be an array or a string");
        $attsStr = ' ';
        foreach ($atts as $att => $val) {
            $attsStr .= "{$att}=\"{$val}\"";
        }
        $str = "<{$name}{$attsStr}>{$children}</{$name}>";
        return $str;
    }
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
    public static $query = null;
    public static $uri_params = [];

    /**
     * 
     * Returns the base URI of host
     */
    public static function get_host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * 
     * Returns 'http' or 'https'
     */
    public static function get_protocol()
    {
        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        return $protocol;
    }

    public static function get_file($key)
    {
        if (!isset($_FILES[$key])) {
            return null;
        }
        return $_FILES[$key];
    }

    public static function get_form_value($key)
    {
        if (!isset($_POST[$key])) {
            return null;
        }
        return $_POST[$key];
    }


    public static function serve_php($path)
    {
        $file = file_get_contents($path);
        if (strpos($path, ".php")) {
            require $path;
            return;
        }
        Html::append(str_replace("\n", "</br>", $file));
    }

    public static function query_params()
    {
        if (App::$query != null) {
            return App::$query;
        }
        $queries = array();
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queries);
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

    public static function body($json = false, $multipart = false)
    {
        if ($multipart) {
            return $_POST;
        }
        $entityBody = file_get_contents('php://input');
        if ($json) {
            $entityBody = json_decode($entityBody, true);
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
        header($key . ': ' . $val, true);
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
    public static function html($render, $appendDefaults = false)
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
            default:
                echo 'Not implemented.';
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
        $this->routes_tree_get->add_path($this->main_route . $total_path, $nameOfCallback);
    }

    public function post($total_path, $nameOfCallback)
    {
        $this->routes_tree_post->add_path($this->main_route . $total_path, $nameOfCallback);
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
