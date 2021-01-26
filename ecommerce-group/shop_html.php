<?php
require_once './db.php';
require_once './navbar.php';
require_once './api/api.php';
require_once './middleware.php';
require_once '../express-php/express.php';


function newShopItem($price, $type, $web, $id, $name, $imageURI, $quantity)
{
  $total = $quantity * $price;
  echo "<li class='list-group-item'>
    <div class='media align-items-lg-center flex-column flex-lg-row p-3'>
      <div class='media-body order-2 order-lg-1'>
        <h5 class='mt-0 font-weight-bold mb-2'><a href='/ecommerce-group/product_html.php?id=${id}&type=${type}&web=${web}'>${name}</a></h5>
        <p class='font-italic text-muted mb-0 small'> {$type} - Quantity: $quantity</p>
        <div class='d-flex align-items-center justify-content-between mt-1'>
          <h6 class='font-weight-bold my-2'>$$price - Total: $$total </h6>
          <ul class='list-inline small'>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
            <li class='list-inline-item m-0'><i class='fa fa-star text-success'></i></li>
          </ul>
        </div>
      </div><img src='${imageURI}' alt='Generic placeholder image' width='200' class='ml-lg-5 order-1 order-lg-2'>
    </div>
  </li>";
}

$items = API::getCartItems()['items'];


?>

<head>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
  <div class="container py-5">
    <h1 class="display-4 pb-4">Shop Cart</h1>
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <!-- List group-->
        <ul class="list-group shadow" id="list">
          <?php
          foreach ($items as $item) {
            newShopItem($item['price'], $item['type'], $item['web'], $item['item_id'], $item['name'], $item['image_uri'], $item['quantity']);
          }
          ?>
        </ul>

      </div>
    </div>
  </div>
  <script src="./js/products.js"></script>
</body>