<?php
require_once './public/ecommerce/controllers/auth.php';
class Order extends Auth
{
  static $SELECT_ATTRIBUTES_ALL = 'DISTINCT order_item.item_id, order_item.order_id as order_item_id, headset.name as headset_name, players.name as players_name, albums.name as albums_name, image.id as image_id, order_item.quantity as quantity, order_item.original_price as price';
  static $instance;
  static function init()
  {
    $ins = new Order("/orders");
    Order::$instance = $ins;
    $ins->get("/user", ['fill_admin', 'get_orders']);
    $ins->get("/json", ['try_fill_user', 'get_orders_json']);
    $ins->get("/order/json", ['try_fill_user', 'get_order_json']);
    $ins->get("/admin", ['fill_admin', 'get_orders']);
    $ins->get("/admin/:user_id", ['fill_admin', 'get_orders']);
    $ins->get("/admin/orders/:id", ['fill_admin', 'get_order']);
  }
  function get_orders_json()
  {
    $user = $this->user;
    if (!$user) {
      App::status_code(400);
      App::json(['error' => 'Unauthorized']);
      return;
    }
    $order = new Model('orders');
    $order = $order
      ->Select('*')
      ->Where(['user_id=' => $user['id']]);
    $rows = $order->Do();
    if (!$rows) {
      App::json(['orders' => $rows]);
      return;
    }
    App::json(['orders' => $rows]);
  }

  function get_order_json()
  {
    // QueryOptions::$DEBUG_QUERIES = true;
    $user = $this->user;
    if (!$user) {
      App::status_code(400);
      App::json(['error' => 'Unauthorized']);
      return;
    }
    $queryParams = App::query_params();
    $orderId = $queryParams['order_id'];
    $id = $user['id'];
    $order = new Model('order_item');
    $order = $order
      ->Select(Order::$SELECT_ATTRIBUTES_ALL)
      ->Where(['order_id=' => $orderId]);
    foreach (Shop::$product_types as $product => $_) {
      $order->LOJoin($product, ["$product.id=" => new Name('order_item.item_id')]);
    }
    // $order->LOJoin("items", ["items.id_ext=" => new Name('order_item.item_id')]);
    $items = $order
      ->Join('image', ['image.item_id=' => new Name('order_item.item_id')])
      ->GroupBy("order_item.item_id")
      ->Do();
    Shop::normalizeItems($items);
    App::json(['items' => $items]);
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
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    require_once './public/ecommerce/views/table.php';
    $table = new Table($rows, 'orders');
    $this->render_view(new HtmlElement(
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
      $this->render("./public/ecommerce/html/not_found.html");
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
    $this->render_view($root);
  }
}
