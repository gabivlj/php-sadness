<?php

require_once './public/ecommerce/controllers/auth.php';

class Shop extends Auth
{
  static $instance;

  static $SELECT_ATTRIBUTES_ALL = 'DISTINCT cart_item.item_id, cart_item.id as cart_item_id, headset.name as headset_name, players.name as players_name, albums.name as albums_name, image.id as image_id, cart_item.quantity as quantity, items.type as type, items.price as price';

  static $product_types = [
    'headset' => true,
    'players' => true,
    'albums' => true,
  ];

  static function init()
  {
    $ins = new Shop("/shop");
    Shop::$instance = $ins;
    $ins->post("/cart/:item_id", ['fill_user', 'post_item']);
    $ins->post("/cart/fulfill", ['fill_user', 'fulfill_order']);
    $ins->post("/cart/remove/:item_id", ['fill_user', 'remove_item']);
    $ins->get("/cart", ['fill_user', 'get_cart_items']);
    $ins->get("/:type/:id", ['fill_user', 'get_item']);
  }

  function fulfill_order()
  {
    // QueryOptions::$DEBUG_QUERIES = true;
    $cart = new Model('cart_item');
    $id = $this->user['id'];
    $rows = $cart
      ->Select('items.id_ext as item_id, cart_item.quantity as quantity, items.price as price')
      ->Join('items', ['items.id_ext=' => new Name('cart_item.item_id')])
      ->Where(['cart_item.user_id=' => $id])
      ->Do();;
    if (!$rows) {
      App::set_response_header('location', '/shop/cart');
      return;
    }
    $cart = new Model('cart_item');
    $ok = $cart->Delete()->Where(['cart_item.user_id='  => $id])->Do();
    if ($ok === false) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $orderId = UUID::v4();
    if (!(new Model('orders'))->Create([
      'id' => $orderId,
      'user_id' => $id,
      'date' => time(),
      'status' => 'PROC',
    ])->Do()) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    foreach ($rows as $item) {
      $orderItem = new Model('order_item');
      $ok = $orderItem->Create([
        'order_id' => $orderId,
        'item_id' => $item['item_id'],
        'quantity' => $item['quantity'],
        'original_price' => $item['price'],
      ])->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    App::set_response_header('location', "/user/orders/{$orderId}?success");
  }

  function get_cart_items()
  {

    $id = $this->user['id'];
    $cart = new Model('cart_item');
    $cart = $cart
      ->Select(Shop::$SELECT_ATTRIBUTES_ALL)
      ->Where(['user_id=' => $id]);
    foreach (Shop::$product_types as $product => $_) {
      $cart->LOJoin($product, ["$product.id=" => new Name('cart_item.item_id')]);
    }
    $cart->LOJoin("items", ["items.id_ext=" => new Name('cart_item.item_id')]);
    $cart = $cart
      ->Join('image', ['image.item_id=' => new Name('cart_item.item_id')])
      ->GroupBy("cart_item.item_id");
    $items = $cart->Do();
    // echo "<pre>", var_dump($items), "</pre>";
    require_once './public/ecommerce/views/cart_list.php';
    $cartList = new CartList($items);
    // echo 'eee';
    // var_dump($cartList);
    $this->render_view($cartList->render());
  }

  function remove_item()
  {
    $itemID = App::$uri_params['item_id'];
    $cart = new Model('cart_item');
    $cartItem = $cart
      ->Select('quantity')
      ->Where(['item_id=' => $itemID])
      ->And(['user_id=' => $this->user['id']])
      ->Do();
    if (!$cartItem) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $cartItem = $cartItem[0];
    // Select the item (we want the type and the quantity)
    $item = (new Model('items'))->Select('type, quantity')->Where(['id_ext=' => $itemID])->Do();
    if (!$item) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $item = $item[0];
    // Update quantities to the stock
    if (!(new Model('items'))
      ->Update()
      ->Where(['id_ext=' => $itemID])
      ->Set(['quantity' => $item['quantity'] + $cartItem['quantity']])
      ->Do()) {
      $this->render("./public/ecommerce/html/not_found.html");
    }
    // Delete from cart
    $ok = $cart
      ->Delete()
      ->Where(['item_id=' => $itemID])
      ->And(['user_id=' => $this->user['id']])
      ->Do();
    if (!$ok) {
      App::set_response_header('location', "/shop/{$item['type']}/{$itemID}?error=3");
    } else {
      // If we can redirect to previous page, do it.
      if (isset($_SERVER["HTTP_REFERER"])) {
        App::set_response_header("location", $_SERVER["HTTP_REFERER"]);
      } else {
        App::set_response_header('location', "/shop/{$item['type']}/{$itemID}");
      }
    }
  }

  function get_item()
  {
    // Clean items
    $this->cleanItems();
    QueryOptions::$DEBUG_QUERIES = false;
    $type = App::$uri_params['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $id = App::$uri_params['id'];
    $items = new Model($type);

    if ($type === 'albums') {
      $items = $items
        ->Select('albums.id as id, albums.name as name, artists.name as artist, items.price as price, items.quantity as quantity, albums.genre')
        ->Where(['albums.id=' => $id])
        ->InnerJoin('items', ['items.id_ext=' => new Name("$type.id")]);
      $items->LOJoin('artists', ['artists.id=' => new Name('albums.artist_id')]);
    } else {
      $items = $items
        ->Select('*')
        ->Where(['id_ext=' => $id])
        ->InnerJoin('items', ['id_ext=' => new Name("$type.id")]);
    }
    $rows = $items->Do();
    $images = (new Model('image'))->Select('id')->Where(['item_id=' => $id])->Do();
    $cart = new Model('cart_item');
    $existingItem = $cart
      ->Select('quantity')
      ->Where(['item_id=' => $id])
      ->And(['user_id=' => $this->user['id']])
      ->Do();
    if ($existingItem) {
      $rows[0]['Current items in cart'] = $existingItem[0]['quantity'];
    }
    if (!$rows) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    require_once './public/ecommerce/views/item.php';
    $itemView = new ItemView($rows[0], $images);
    $this->render_view($itemView->render(!!$existingItem));
  }

  function post_item()
  {
    $itemID = App::$uri_params['item_id'];
    $item = (new Model('items'))->Select('*')->Where(['id_ext=' => $itemID])->Limit(1)->Do();
    if (!$item) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $item = $item[0];
    $body = App::body(false, true);
    // Check quantities
    if ($item['quantity'] < $body['quantity']) {
      App::set_response_header(
        'location',
        "/shop/{$item['type']}/{$itemID}?error=1"
      );
      return;
    }
    // New quantity
    $newQuantity = $item['quantity'] - $body['quantity'];
    // Update quantity in stock
    if (!(new Model('items'))
      ->Update()
      ->Where(['id_ext=' => $itemID])
      ->Set(['quantity' => $newQuantity])
      ->Do()) {
      App::set_response_header('location', "/shop/{$item['type']}/{$itemID}?error=2");
      return;
    }
    // Cart item check if it already exists for this user to not add a new entry
    $cart = new Model('cart_item');
    $existingItem = $cart
      ->Select('quantity')
      ->Where(['item_id=' => $item['id_ext']])
      ->And(['user_id=' => $this->user['id']])
      ->Do();
    $cart = new Model('cart_item');
    $itemUpd = [
      'quantity' => $body['quantity'],
      'item_id' => $itemID,
      'added' => time(),
      'user_id' => $this->user['id'],
    ];
    if ($existingItem) {
      $itemUpd['quantity'] += $existingItem[0]['quantity'];
      $cart
        ->Update()
        ->Where(['item_id=' => $itemID])
        ->And(['user_id=' => $this->user['id']])
        ->Set($itemUpd)
        ->Do();
    } else {
      $cart
        ->Create($itemUpd)
        ->Do();
    }
    App::set_response_header('location', "/shop/{$item['type']}/{$itemID}");
  }

  function fill_user()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      App::set_response_header('location', '/sign_up/login');
      $this->stop();
      return;
    }
    $id = $_SESSION['id'];
    $this->user = User::getById($id);
  }

  function cleanItems()
  {
    // 30 MINS
    $SECONDS_EXPIRES = 60 * 30;
    $item = new Model('cart_item');
    $rows = $item
      ->Select('added, quantity, item_id')
      ->Where(['added<' => time() - $SECONDS_EXPIRES])
      ->Do();
    if (!$rows) return;
    foreach ($rows as $row) {
      (new Model('items'))
        ->Update()
        ->Set(['quantity' => new Name("quantity + {$row['quantity']}")])
        ->Where(['id_ext=' => $row['item_id']])
        ->Do();
    }
    $item
      ->Delete('added, quantity, item_id')
      ->Where(['added<' => time() - $SECONDS_EXPIRES])
      ->Do();
  }
}
