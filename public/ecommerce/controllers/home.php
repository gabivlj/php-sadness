<?php
require_once './public/ecommerce/controllers/auth.php';
class Home extends Controller
{
  static $instance;
  static function init()
  {
    $ins = new Home("/homes");
    // Home::$instance = $ins;
    // $ins->get("/", ['home']);
    // $ins->get("/json", ['home_json']);
  }

  function home()
  {
  }

  function home_json()
  {
    Search::$instance->search_general();
  }
}
