<?php

require_once './public/ecommerce/controllers/signup.php';
require_once './public/ecommerce/controllers/items.php';
require_once './public/ecommerce/controllers/order.php';
require_once './public/ecommerce/controllers/shop.php';

function startEcommerce($app)
{
  Signup::init();
  Items::init();
  Order::init();
  Shop::init();
  $app->use(Signup::$instance);
  $app->use(Order::$instance);
  $app->use(Items::$instance);
  $app->use(Shop::$instance);
}
