<?php

require_once './public/ecommerce/controllers/signup.php';
require_once './public/ecommerce/controllers/items.php';
require_once './public/ecommerce/controllers/order.php';

function startEcommerce($app)
{
  Signup::init();
  Items::init();
  Order::init();
  $app->use(Signup::$instance);
  $app->use(Order::$instance);
  $app->use(Items::$instance);
}
