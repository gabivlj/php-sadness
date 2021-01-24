<?php
require_once './db.php';
require_once './api/api.php';
require_once '../express-php/express.php';
initDB();
function error($msg = "Unknown err")
{
  header("Location: /ecommerce-group/register_html.php?error={$msg}");
}
if (!isset($_GET['e'])) {
  error("Don't go to verify bro");
  return;
}
QueryOptions::$DEBUG_QUERIES = true;
$email = $_GET['e'];
$token = $_GET['t'];
$users = (new Model('users'))->Select('password')->Where(['email=' => $email, 'confirmed=' => 0, 'token=' => $token])->Do();
if (!$users) {
  error("not found");
  return;
}
$ok = (new Model('users'))
  ->Update()
  ->Where(['email=' => $email])
  ->And(['token=' => $token, 'confirmed=' => 0])
  ->Set(['token' => '', 'confirmed' => 1])
  ->Do();
if (!$ok) {
  error("Unknown error confirming :(");
  return;
}
if (!API::registerOnEveryAPI(['email' => $email, 'password' => $users[0]['password']])) {
  error("error registering on the rest of the systems");
  return;
}
header("Location: /ecommerce-group/login_html.php?success_verifying");
