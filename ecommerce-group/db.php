<?php

require_once '../express-php/db/db.php';

function initDB()
{
  Model::$address = "192.168.64.2";
  Model::$name_db = "gabi";
  Model::$password = "123456";
  Model::$db = "ecommerce-group";
}
