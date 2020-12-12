<?php

require_once './public/ecommerce/controllers/signup.php';

function startEcommerce($app)
{
  Signup::init();
  $app->use(Signup::$instance);
}
