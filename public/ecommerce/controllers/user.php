<?php

class UserController extends Controller
{
  static $instance = null;

  static function init()
  {
    $ins = new UserController("/user");
    UserController::$instance = $ins;
    $ins->get("/:username", ['fill_user', 'get_user']);
    $ins->get("/order/:order_id", ['fill_user', 'get_user_order']);
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

  function get_user_order()
  {
    require_once './public/ecommerce/views/table.php';
    require_once './public/ecommerce/views/delete_button.php';
    $orderID = App::$uri_params['order_id'];
    $order = new Model('orders');
    $rows = $order
      ->Select('orders.id, orders.status, orders.user_id')
      ->Where(['id=' => $orderID])
      ->Do();
    if (!$rows) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }

    $order = $rows[0];
    if ($order['user_id'] !== Items::$user['id']) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    unset($rows[0]['user_id']);
    $items = new Model('order_item');
    $itemRows = $items
      ->Select('items.type as type, order_item.quantity as quantity, items.price as price, order_item.original_price as original_price, items.id_ext as id')
      ->InnerJoin('items', ['id_ext=' => new Name('order_item.item_id')])
      ->Where(['order_id=' => $orderID])
      ->Do();
    $table = new Table($rows, 'orders');
    $tableItems = new Table($itemRows, null);
    $root = new HtmlElement('div', ['class' => 'container'], [
      $table->render(),
      $tableItems->render(),
      // (new DeleteButton())->render("/orders/admin/delete/{$order['id']}")
    ]);
    Items::render_view($root);
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
