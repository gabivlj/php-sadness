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
$responseDani = API::fullFillCartDani();

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


$itemsToDelete = (new Model("cart_items"))->Select("*")->Where(["user_id=" => $user_id])->Do();

$okDeletion = (new Model("cart_items"))->Delete()->Where(["user_id=" => $user_id])->Do();

$ok = (new Model("order_items"))->Create([
  "order_id" => $orderId,
  "quantity" => $to_delete["quantity"],
  "prize" => $to_delete["prize"],
  "item_type" => $to_delete["product_type"],
  "item_id" => $to_delete["item_id"],
])->Do();

(new Model("orders"))
  ->Create(["user_id" => $user_id, "date" => time(), "status" => "Processing...", "id" => $orderId])
  ->Do();


(new Model("orders"))->Select("*")->Where(['user_id=' => $user_id])->Do();


$existsOrder = (new Model("orders"))->Select('*')->Where(["user_id=" => $user_id])->And(["order_id=" => $orderId])->Do();
if (!$existsOrder) {
  return ["order_items" => []];
}
