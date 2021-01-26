<?php

require_once './middleware.php';
require_once '../express-php/express.php';
require_once './api/api.php';

if (!isLoged()) {

  App::status_code(400);
  App::json(['error' => 'Unauthorized, not loged in']);
  die();
}
$body = App::body(true, false);
if (!isset($body['quantity']) || !isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['web'])) {
  App::status_code(400);
  App::json(['error' => 'Bad request']);
  die();
}
$quantity = $body['quantity'];
$id = $_GET['id'];
$type = $_GET['type'];
$web = $_GET['web'];
$success = API::addToCart($quantity, $id, $web, $type);
if (!$success) {
  App::status_code(400);
  App::json(['success' => false]);
  die();
}
App::json(['success' => true]);
