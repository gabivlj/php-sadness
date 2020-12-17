<?php

class Order extends Controller
{
  static $instance;
  static function init()
  {
    $ins = new Order("/orders");
    Order::$instance = $ins;
    $ins->get("/admin", ['fill_admin', 'get_orders']);
    $ins->get("/admin/:user_id", ['fill_admin', 'get_orders']);
    $ins->get("/admin/order/:id", ['fill_admin', 'get_order']);
    // DEPRECATED: Just using items.php
    $ins->post("/admin/update/:type/:id", ['fill_admin', 'update_order']);
  }

  function fill_admin()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      App::set_response_header('location', '/sign_up/login');
      $this->stop();
      return;
    }
    $id = $_SESSION['id'];
    Items::$user = User::getById($id);
    if (!Items::$user || !Items::$user['admin']) {
      App::set_response_header('location', '/home');
      $this->stop();
      return;
    }
  }

  function get_orders()
  {
    $userID = App::$uri_params['user_id'];
    $order = new Model('orders');
    $rows = $order
      ->Select('*')
      ->Where(['user_id=' => $userID])
      ->Do();
    if ($rows === false) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }

    require_once './public/ecommerce/views/table.php';
    $table = new Table($rows, 'orders');
    Items::render_view(new HtmlElement(
      'div',
      [],
      [new HtmlElement('h1', ['class' => 'm-3 p-3 text-3xl'], "Orders made by user $userID"), $table->render()]
    ));
  }

  function get_order()
  {
    $orderID = App::$uri_params['id'];
    $order = new Model('orders');
    $rows = $order
      ->Select('*')
      ->Where(['id=' => $orderID])
      ->Do();
    if (!$rows) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    $order = $rows[0];
    $items = new Model('order_item');
    $itemRows = $items
      ->Select('*')
      ->InnerJoin('items', ['id=' => new Name('order_item.item_id')])
      ->Where(['order_id=' => $orderID])
      ->Do();
    $table = new Table($rows, 'orders');
    $tableItems = new Table($itemRows, 'items');
    $root = new HtmlElement('div', [], [$table->render(), $tableItems->render()]);
    Items::render_view($root);
  }
}
