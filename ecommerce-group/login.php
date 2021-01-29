<?php

require_once './db.php';
require_once './middleware.php';
require_once './api/api.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';

if (redirectIfLogedIn()) {
  die();
}

initDB();
function error($msg = "Unknown err")
{
  header("Location: /ecommerce-group/register_html.php?error={$msg}");
}

if (!isset($_POST['email']) || !isset($_POST['password'])) {
  header("Location: /ecommerce-group/login_html.php");
  return;
}
$email = $_POST['email'];
$password = $_POST['password'];
$user = (new Model('users'))->Select('password')->Where(['email=' => $email])->And(['confirmed=' => 1])->Do();
if (!$user) {
  error("Bad credentials");
  return;
}
$user = $user[0];
if (!password_verify($password, $user['password'])) {
  error("Bad credentials");
  return;
}

$_SESSION['email'] = $email;
$_SESSION['id'] = $email;
$_SESSION['username'] = $email;
header("Location: /ecommerce-group/products_html.php");
