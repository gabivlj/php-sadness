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
    $ins->get("/admin/orders/:id", ['fill_admin', 'get_order']);
    // todo:
    $ins->post("/admin/update/:id", ['fill_admin', 'update_order']);
    // todo:
    $ins->post("/admin/delete/:id", ['fill_admin', 'delete_order']);
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
    $userID = isset(App::$uri_params['user_id']) ? App::$uri_params['user_id'] : null;
    $order = new Model('orders');
    $order = $order
      ->Select('*');
    if ($userID) {
      $order->Where(['user_id=' => $userID]);
    }
    $rows = $order->Do();
    if ($rows === false) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }

    require_once './public/ecommerce/views/table.php';
    $table = new Table($rows, 'orders');
    Items::render_view(new HtmlElement(
      'div',
      [],
      [new HtmlElement('h1', ['class' => 'm-3 p-3 text-3xl'], $userID ? "Orders made by user $userID" : 'Orders'), $table->render('/orders/admin')]
    ));
  }

  function get_order()
  {
    require_once './public/ecommerce/views/table.php';
    require_once './public/ecommerce/views/delete_button.php';
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
      ->Select('order_item.order_id, items.type as type, order_item.quantity as quantity, items.price as price, order_item.original_price as original_price, items.id_ext as id')
      ->InnerJoin('items', ['id_ext=' => new Name('order_item.item_id')])
      ->Where(['order_id=' => $orderID])
      ->Do();
    $table = new Table($rows, 'orders');
    $tableItems = new Table($itemRows, null);
    $root = new HtmlElement('div', [], [
      $table->render(),
      $tableItems->render(),
      // (new DeleteButton())->render("/orders/admin/delete/{$order['id']}")
    ]);
    Items::render_view($root);
  }
}
