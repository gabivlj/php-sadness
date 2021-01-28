<?php

require_once "./api/api.php";
require_once "./db.php";
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';

initDB();

if (redirectIfNotLogedIn()) {
  die();
}

$responseGVS = API::fullFillCartGVS();
$responseDani = ['success' => false];

if (!$responseGVS['success'] && !$responseDani['success']) {
  echo "Empty cart!";
  header("location: /ecommerce-group/shop_html.php");
  die();
}

$user = getUser();

$model = new Model("orders");
$objectCreation = ["dani_id" => "", "gabi_id" => "", "id" => UUID::v4(), "date" => time(), "user_id" => $user["email"]];

if ($responseGVS['success']) {
  $objectCreation["gabi_id"] = $responseGVS["order_id"];
}

if ($responseDani['success']) {
  $objectCreation["dani_id"] = $responseDani["order_id"];
}

$ok = $model->Create($objectCreation)->Do();
if (!$ok) {
  echo "Couldn't create the order... Go back to the ecommerce...";
  die();
}

header("location: /ecommerce-group/shop_html.php");
