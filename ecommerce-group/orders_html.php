<?php
require_once "./api/api.php";
require_once "./db.php";
require_once './navbar.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';


initDB();

if (redirectIfNotLogedIn()) {
  die();
}

$user = getUser();

$orders = new Model("orders");

$orders = $orders->Select("*")->Where(["user_id=" => $user["email"]])->Do();

if (!$orders) {
  die();
}

function newOrder($id, $time)
{
  echo "<li class='list-group-item'>
    <div class='media align-items-lg-center flex-column flex-lg-row p-3'>
      <div class='media-body order-2 order-lg-1'>
        <h5 class='mt-0 font-weight-bold mb-2'><a href='/ecommerce-group/order_html.php?id=${id}'>$id</a></h5>

        <div class='d-flex align-items-center justify-content-between mt-1'>
          <h6 class='font-weight-bold my-2'>Ordered at: $time</h6>
          <ul class='list-inline small'>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
          </ul>
        </div>
    </div>
  </li>";
}

?>

<head>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
  <div class="container py-5">
    <h1 class="display-4 pb-4">Your Orders</h1>
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <!-- List group-->
        <ul class="list-group shadow" id="list">
          <?php
          foreach ($orders as $item) {
            newOrder($item['id'], $item['date']);
          }
          ?>
        </ul>
      </div>
    </div>
  </div>
  <script src="./js/products.js"></script>
</body>