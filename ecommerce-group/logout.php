<?php

echo "login out...";

$_SESSION = [];
setcookie(session_name(), '', time() - 10000000, '/');
session_destroy();

header('location: /ecommerce-group/login.php');
