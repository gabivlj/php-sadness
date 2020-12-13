<?php

require_once './public/ecommerce/controllers/signup.php';
require_once './public/ecommerce/controllers/items.php';

function startEcommerce($app)
{
  Signup::init();
  Items::init();
  $app->use(Signup::$instance);
  $app->use(Items::$instance);
}
