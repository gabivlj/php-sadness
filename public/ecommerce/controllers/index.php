<?php

require_once './public/ecommerce/controllers/signup.php';
require_once './public/ecommerce/controllers/items.php';
require_once './public/ecommerce/controllers/order.php';
require_once './public/ecommerce/controllers/shop.php';
require_once './public/ecommerce/controllers/search.php';

function startEcommerce($app)
{
  Signup::init();
  Items::init();
  Order::init();
  Shop::init();
  Search::init();
  $app->use(Signup::$instance);
  $app->use(Order::$instance);
  $app->use(Items::$instance);
  $app->use(Shop::$instance);
  $app->use(Search::$instance);
}
