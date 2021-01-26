<?php

require_once './db.php';
require_once './api/api.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';

if (redirectIfLogedIn()) die();
initDB();
function error($msg = "Unknown err")
{
  header("Location: /ecommerce-group/register_html.php?error={$msg}");
}

if (!isset($_POST['email']) || !isset($_POST['password'])) {
  error();
  return;
}
$email = $_POST['email'];
$password = $_POST['password'];
$users = (new Model('users'))->Select('*')->Where(['email LIKE ' => $email])->Do();
if ($users) {
  error();
  return;
}
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$response = UUID::v4();
$user = ['email' => $email, 'password' => $hashedPassword, 'confirmed' => 0, 'token' => $response];
$userCreatedOk = (new Model('users'))->Create($user)->Do();
if (!$userCreatedOk) {
  error();
  return;
}
$hostname = App::get_host();
$protocol = App::get_protocol();
var_dump(sendEmail($email, "", "Welcome to PHPEcommerceMerger, please go to $protocol://$hostname/ecommerce-group/verification.php?e=$email&t=$response to verify your account"));
echo "We sent you an email. Please check inbox";
// if (!API::registerOnEveryAPI($user)) {
//   (new Model('users'))->Delete()->Where(['email=' => $email])->Do();
//   error("Internal error.");
//   return;
// }
