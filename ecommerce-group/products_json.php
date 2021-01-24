<?php
require_once './db.php';
require_once './api/api.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';

if (!isset($_GET['search'])) {
  $_GET['search'] = '';
}
$search = $_GET['search'];
$response = API::search($search);
App::json($response);
