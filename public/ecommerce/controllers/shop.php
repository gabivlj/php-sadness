<?php


class Shop extends Controller
{
  static $instance;
  static function init()
  {
    $ins = new Shop("/shop");
    Shop::$instance = $ins;
    $ins->post("/cart/:item_id", ['fill_user', 'post_item']);
    $ins->post("/fulfill", ['fill_user', 'fulfill_order']);
    $ins->post("/cart/remove/:item_id", ['fill_user', 'remove_item']);
    $ins->get("/cart", ['fill_user', 'get_cart_items']);
    $ins->get("/:type/:id", ['fill_user', 'get_item']);
  }

  function remove_item()
  {
  }

  function get_item()
  {
    QueryOptions::$DEBUG_QUERIES = false;
    $type = App::$uri_params['type'];
    if (!isset(Items::$available_types[$type])) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    $id = App::$uri_params['id'];
    $items = new Model($type);

    if ($type === 'albums') {
      $items = $items
        ->Select('albums.id as id, albums.name as name, artists.name as artist, items.price as price, items.quantity as quantity')
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
      ->And(['user_id=' => Items::$user['id']])
      ->Do();
    if ($existingItem) {
      $rows[0]['Current items in cart'] = $existingItem[0]['quantity'];
    }
    if (!$rows) {
      Items::render("./public/ecommerce/html/not_found.html");
      return;
    }
    require_once './public/ecommerce/views/item.php';
    $itemView = new ItemView($rows[0], $images);
    Items::render_view($itemView->render(!!$existingItem));
  }

  function post_item()
  {
    $itemID = App::$uri_params['item_id'];
    $item = (new Model('items'))->Select('*')->Where(['id_ext=' => $itemID])->Limit(1)->Do();
    if (!$item) {
      App::status_code(404);
      App::json(['message' => 'Item does not exist']);
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
    if (!(new Model('items'))->Update()->Where(['id_ext=' => $itemID])->Set(['quantity' => $newQuantity])->Do()) {
      App::set_response_header('location', "/shop/{$item['type']}/{$itemID}?error=2");
      return;
    }
    // Cart item check if it already exists for this user to not add a new entry
    $cart = new Model('cart_item');
    $existingItem = $cart
      ->Select('quantity')
      ->Where(['item_id=' => $item['id_ext']])
      ->And(['user_id=' => Items::$user['id']])
      ->Do();
    $cart = new Model('cart_item');
    $itemUpd = [
      'quantity' => $body['quantity'],
      'item_id' => $itemID,
      'added' => time(),
      'user_id' => Items::$user['id'],
    ];
    if ($existingItem) {
      $itemUpd['quantity'] += $existingItem[0]['quantity'];
      $cart
        ->Update()
        ->Where(['item_id=' => $itemID])
        ->And(['user_id=' => Items::$user['id']])
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
    Items::$user = User::getById($id);
  }
}
