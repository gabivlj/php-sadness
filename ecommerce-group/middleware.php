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
