<?php
require_once './db.php';
require_once './api/api.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';
initDB();
if (!isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['web'])) {
  header('Location: /ecommerce-group/products_html.php');
  exit();
}
redirectIfNotLogedIn();
$user = getSession();
$users = (new Model('users'))->Select('*')->Where(['email=' => $user['email']])->Do();
if (!$users) {
  echo "what";
}
$users = $users[0];
var_dump(Api::getProduct($_GET['type'], $_GET['web'], $_GET['id'], $users['password'], $users['email']));
