<?php
require_once './db.php';
require_once './api/api.php';
require_once './middleware.php';
require_once '../express-php/express.php';
require_once '../express-php/uuid.php';
require_once '../public/ecommerce/email.php';
initDB();
if (!isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['web'])) {
  header('Location: /ecommerce-group/products_html.php');
  exit();
}
redirectIfNotLogedIn();
$product = Api::getProduct($_GET['type'], $_GET['web'], $_GET['id']);
$str = "";
if (!isset($product['Current items in cart'])) {
  $product['Current items in cart'] = 0;
}
foreach ($product as $key => $value) {
  if (
    stripos($key, "id") !== false ||
    stripos($key, "price") !== false ||
    stripos($key, "image") !== false ||
    stripos($key, "name") !== false ||
    stripos($key, "web") !== false
  ) continue;
  $key = ucfirst($key);
  $str .= "<br/>$key: <label id='$key'>$value</label>";
}

?>
<style>
  body {
    background-color: #EEEEEE
  }

  a {
    text-decoration: none !important
  }

  .card-product-list,
  .card-product-grid {
    margin-bottom: 0
  }

  .card {
    width: 500px;
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 23px;
    margin-top: 50px
  }

  .card-product-grid:hover {
    -webkit-box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
    box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
    -webkit-transition: .3s;
    transition: .3s
  }

  .card-product-grid .img-wrap {
    border-radius: 0.2rem 0.2rem 0 0;
    height: 220px
  }

  .card .img-wrap {
    overflow: hidden
  }

  .card-lg .img-wrap {
    height: 280px
  }

  .card-product-grid .img-wrap {
    border-radius: 0.2rem 0.2rem 0 0;
    height: 275px;
    padding: 16px
  }

  [class*='card-product'] .img-wrap img {
    height: 100%;
    max-width: 100%;
    width: auto;
    display: inline-block;
    -o-object-fit: cover;
    object-fit: cover
  }

  .img-wrap {
    text-align: center;
    display: block
  }

  .card-product-grid .info-wrap {
    overflow: hidden;
    padding: 18px 20px
  }

  [class*='card-product'] a.title {
    color: #212529;
    display: block
  }

  .rating-stars {
    display: inline-block;
    vertical-align: middle;
    list-style: none;
    margin: 0;
    padding: 0;
    position: relative;
    white-space: nowrap;
    clear: both
  }

  .rating-stars li.stars-active {
    z-index: 2;
    position: absolute;
    top: 0;
    left: 0;
    overflow: hidden
  }

  .rating-stars li {
    display: block;
    text-overflow: clip;
    white-space: nowrap;
    z-index: 1
  }

  .card-product-grid .bottom-wrap {
    padding: 18px;
    border-top: 1px solid #e4e4e4
  }

  .bottom-wrap-payment {
    padding: 0px;
    border-top: 1px solid #e4e4e4
  }

  .rated {
    font-size: 10px;
    color: #b3b4b6
  }

  .btn {
    display: inline-block;
    font-weight: 600;
    color: #343a40;
    text-align: center;
    vertical-align: middle;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.45rem 0.85rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.2rem
  }

  .btn-primary {
    color: #fff;
    background-color: #3167eb;
    border-color: #3167eb
  }

  .fa {
    color: #FF5722
  }
</style>

<head>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
  <div class="container d-flex justify-content-center">
    <figure class="card card-product-grid card-lg"> <a href="#" class="img-wrap" data-abc="true"> <img src="<?php
                                                                                                            echo $product['image_uri']; ?>"> </a>
      <figcaption class="info-wrap">
        <div class="row">
          <div class="col-md-9 col-xs-9"> <a href="#" class="title" data-abc="true">
              <?php
              echo $product['name']; ?></a> <span class="rated uppercase"><?php
                                                                          echo $product['type']; ?></span> </div>
          <div class="col-md-3 col-xs-3">
            <div class="rating text-right"> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <span class="rated">Rated 5.0/5! Trust us...</span> </div>
          </div>

        </div>
      </figcaption>
      <div class="bottom-wrap-payment">
        <figcaption class="info-wrap">
          <div class="row">
            <div class="col-md-9 col-xs-9"> <a href="#" class="title" data-abc="true">Price: $<?php
                                                                                              echo $product['price'];
                                                                                              echo $str; ?></a> </div>
          </div>
        </figcaption>
      </div>
      <div class="bottom-wrap">
        Quantity
        <input type="number" name="quantity" id="quantity_input">
        <button id="buy" class="btn btn-primary ml-3">Add to cart</button>
        <div id="htmlButton">
        </div>
        <p id="message"></p>
      </div>
    </figure>
  </div>
</body>
<script>
  let htmlButton = document.createElement('button', []);
  htmlButton.className = 'btn btn-danger mt-3';
  htmlButton.innerHTML = "Remove Item";
  const message = document.getElementById('message');
  const quantityInfo = document.getElementById('Quantity');
  const addedInfo = document.getElementById('Current items in cart');
  const quantityInput = document.getElementById('quantity_input');
  const buttonBuy = document.getElementById('buy');
  const buttonRemoveItemWrapper = document.getElementById('htmlButton');
  const urlParams = new URLSearchParams(window.location.search);
  const queryString = `?id=${urlParams.get('id')}&type=${urlParams.get('type')}&web=${urlParams.get('web')}`;
  if (+addedInfo.innerHTML > 0) {
    buttonRemoveItemWrapper.appendChild(htmlButton);
  }
  buttonBuy.addEventListener('click', e => {
    let passedQuantity = (+quantityInput.value) || 1;
    message.innerHTML = 'Loading...';
    fetch(
        `${window.location.protocol}//${window.location.hostname}/ecommerce-group/add_cart.php${queryString}`, {
          method: 'POST',
          body: JSON.stringify({
            quantity: +passedQuantity
          })
        }
      )
      .then(el => el.json())
      .then(el => {
        if (el.success) {
          updateInfo(passedQuantity);
          message.innerHTML = 'Successfully added to cart!';
        } else {
          message.innerHTML = "Couldn't add to cart";
        }
      }).catch(err => console.log(err));
  });
  htmlButton.addEventListener('click', e => {
    console.log('removed');
  });

  function updateInfo(newQuantity) {
    const newAdded = (parseInt(addedInfo.innerHTML, 10)) + newQuantity;
    if (newAdded === newQuantity) {
      buttonRemoveItemWrapper.appendChild(htmlButton);
    }
    if (newAdded === 0) {
      buttonRemoveItemWrapper.removeChild(htmlButton);
    }
    addedInfo.innerHTML = newAdded;
    quantityInfo.innerHTML = (parseInt(quantityInfo.innerHTML, 10)) - newQuantity;
  }
</script>