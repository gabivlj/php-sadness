<?php
require_once './db.php';
require_once './api/api.php';
require_once './navbar.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';
if (redirectIfNotLogedIn()) {
  die();
}
?>

<head>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
  <div class="container py-5">
    <div class="form-group">
      <label for="productSearch">Search A Product</label>
      <form id="search">
        <input type="text" id="search_term" class="form-control" id="productName" aria-describedby="productHelp" placeholder="Product Name">
      </form>
    </div>
    <select name="select" id="select">
      <option value="priceAsc" selected>Price Ascendent</option>
      <option value="priceDesc">Price Descendent</option>
      <option value="nameAsc">Name Ascendent</option>
      <option value="nameDesc">Name Descendent</option>
    </select>
    <h4 id="loading">Loading...</h4>
    <div class="row text-center text-white mb-5">
      <div class="col-lg-7 mx-auto">
        <h1 class="display-4">Product List</h1>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <!-- List group-->
        <ul class="list-group shadow" id="list">

        </ul>

      </div>
    </div>
  </div>
  <script src="./js/products.js"></script>
</body>