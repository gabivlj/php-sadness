<?php

function redirectIfLogedIn()
{
  session_start();
  if (isset($_SESSION['email'])) {
    header("Location: /ecommerce-group/products_html.php");
    return true;
  }
  return false;
}

function redirectIfNotLogedIn()
{
  session_start();
  if (!isset($_SESSION['email'])) {
    header("Location: /ecommerce-group/login_html.php");
    return true;
  }
  return false;
}

function isLoged()
{
  session_start();
  return isset($_SESSION['email']);
}

function getSession()
{
  return $_SESSION;
}

function getUser()
{
  require_once dirname(__DIR__) . '/ecommerce-group/db.php';
  require_once dirname(__DIR__) . '/express-php/db/db.php';

  initDB();
  $user = getSession();
  $users = (new Model('users'))->Select('*')->Where(['email=' => $user['email']])->Do();
  if (!$users) {
    echo "ERROR: Your user doesn't exist anymore for some reason :/";
  }
  return $users[0];
}
