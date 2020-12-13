<?php

class Order extends Controller
{
  static $instance;
  static function init()
  {
    $ins = new Order("/orders");
    Order::$instance = $ins;
    $ins->get("/admin/:user_id", ['fill_admin', 'get_orders']);
    $ins->get("/admin/:id", ['fill_admin', 'get_order']);
    $ins->post("/admin/update/:type/:id", ['fill_admin', 'update_order']);
    $ins->post("/admin/delete/:type/:id", ['fill_admin', 'delete_item']);
    $ins->post("/admin/:type", ['fill_admin', 'post_item']);
  }
}
