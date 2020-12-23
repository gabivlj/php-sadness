<?php

class UserController extends Controller
{
  static $instance = null;

  static function init()
  {
    $ins = new UserController("/user");
    UserController::$instance = $ins;
    $ins->get("/:username", ['fill_user', 'get_user']);
  }

  function fill_user()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      App::set_response_header('location', '/sign_up/login');
      $this->stop();
      return;
    }
    $id = $_SESSION['id'];
    Items::$user = User::getById($id);
  }

  function get_user()
  {
    $username = App::$uri_params['username'];
    $user = new Model('users');
    $users = $user
      ->Select('username, id')
      ->Limit(1)
      ->Where(['username=' => $username])
      ->Do();
    if (!$users) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    $user = $users[0];
    if ($user['id'] != Items::$user['id']) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    $orders = (new Model('orders'))
      ->Select('status, date, id')
      ->Where(['orders.user_id=' => $user['id']])
      ->Do();
    if (!$orders) {
      $orders = [];
    }
    require_once './public/ecommerce/views/table.php';
    $table = new Table($orders, 'order');
    $root = new HtmlElement('div', ['class' => 'container'], []);
    $root->append(new HtmlElement('h1', ['class' => 'text-4xl m-5'], "Hi, {$user['username']}, these are your orders!"));
    $root->append(new HtmlElement('h1', ['class' => 'text-2xl m-5'], "Click on the ID column to go for more details about that order."));
    $root->append($table->render("/user"));
    Items::render_view($root);
  }
}
