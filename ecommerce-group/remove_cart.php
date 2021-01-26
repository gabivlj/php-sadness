<?php

require_once './middleware.php';
require_once '../express-php/express.php';
require_once './api/api.php';

if (!isLoged()) {
  App::status_code(400);
  App::json(['error' => 'Unauthorized, not loged in']);
  die();
}
if (!isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['web'])) {
  App::status_code(400);
  App::json(['error' => 'Bad request']);
  die();
}
$success = API::deleteFromCart($_GET['id'], $_GET['web'], $_GET['type']);
if (!$success) {
  App::status_code(400);
  App::json(['success' => false]);
  die();
}
App::json(['success' => true]);
