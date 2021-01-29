<?php
require_once dirname(__DIR__) . '/api/request.php';
require_once dirname(__DIR__) . '/middleware.php';

define("GVS_IMAGE_TYPE", 1);
define("DANI_IMAGE_TYPE", 2);


function getProductMap()
{
  $URIS_PRODUCT = [
    'gvs' => function ($type, $id) {
      return "http://apigvillalonga20.000webhostapp.com/shop/$type/$id";
    },
    'dani' => function ($type, $id) {
      return "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/shop/?type=$type&id=$id";
    },
  ];
  return $URIS_PRODUCT;
}

function getShopDelete()
{
  $URIS_DELETE_CART = [
    'gvs' => function ($type, $id) {
      return "http://apigvillalonga20.000webhostapp.com/shop/cart/remove/$id";
    },
    'dani' => function ($type, $id) {
      return "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/shop/cart/delete/?product_id=$id";
    }
  ];
  return $URIS_DELETE_CART;
}

function getShopAddCartMap()
{
  $URIS_ADD_CART = [
    'gvs' => function ($id, $_type, $quantity) {
      return "http://apigvillalonga20.000webhostapp.com/shop/cart/$id";
    },
    'dani' => function ($id, $type, $quantity) {
      return "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/shop/cart/?id=$id&type=$type&quantity=$quantity";
    },
  ];
  return $URIS_ADD_CART;
}


class API
{
  static $REGISTER_URI_GVILL = 'http://apigvillalonga20.000webhostapp.com/sign_up/special_register';
  static $REGISTER_URI_DANI = 'https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/auth/signup/special_register/';
  static $SEARCH_PRODUCTS_GVILL = 'http://apigvillalonga20.000webhostapp.com/search/json?limit=100';
  static $IMG_GVS = 'http://apigvillalonga20.000webhostapp.com/public/ecommerce/files';
  static $IMG_DANI = 'http://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/admin';

  static function getImage($type, $id)
  {
    if ($type == GVS_IMAGE_TYPE) {
      return API::$IMG_GVS . "/$id.png";
    } else {
      return API::$IMG_DANI . "$id";
    }
  }

  static function registerOnEveryAPI($user)
  {
    $user['secret'] = 'SECRET_WORD';
    $req = new Request(API::$REGISTER_URI_GVILL);
    $req->setRequestType('POST');
    $req->setPostFields(json_encode($user));
    $req->execute();
    $code = $req->getHttpCode();
    if ($code != 200) {
      return null;
    }
    $user['secret'] = 'SECRET_WORD';
    $req = new Request(API::$REGISTER_URI_DANI);
    $req->setRequestType('POST');
    $req->setPostFields(json_encode($user));
    $req->execute();
    $response = json_decode($req->getResponse(), true);
    $code = $req->getHttpCode();
    if ($code != 200) {
      var_dump($response);
      return null;
    }
    return $response;
  }

  static $SEARCH_PRODUCTS_DANI = "http://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/search/json/?limit=100";

  static function normalizeItem(&$item)
  {
    $item["price"] = $item["prize"];
    $item["image_uri"] = API::getImage(212, $item["image"]);
    $item["web"] = "dani";
    unset($item["prize"]);
  }

  static function normalizeItemsDani(&$items)
  {
    if (isset($items[0])) {
      foreach ($items as &$item) {
        API::normalizeItem($item);
      }
    } else {
      API::normalizeItem($items);
    }
    return $items;
  }

  static function searchDani($searchTerm, $type)
  {
    $req = new Request(API::$SEARCH_PRODUCTS_DANI);
    $req->setRequestType('POST');
    $req->setPostFields(["type" => $type, 'name' => $searchTerm]);
    $req->execute();
    $res = $req->getResponse();
    $responseDani = json_decode($res, true);
    if (!isset($responseDani["items"])) {
      return ["items" => []];
    }
    $responseDani["items"] = API::normalizeItemsDani($responseDani["items"]);
    foreach ($responseDani["items"] as &$item) {
      $item["type"] = $type;
    }
    return $responseDani;
  }

  static function search($searchTerm)
  {
    $keyboards = API::searchDani($searchTerm, "keyboards");
    $keycaps = API::searchDani($searchTerm, "keycaps");
    $frames = API::searchDani($searchTerm, "frames");
    $items = array_merge($keyboards["items"], array_merge($keycaps["items"], $frames["items"]));
    $req = new Request(API::$SEARCH_PRODUCTS_GVILL . "&name={$searchTerm}");
    $req->setRequestType('GET');
    $req->execute();
    $responseGVS = json_decode($req->getResponse(), true);
    if (!isset($responseGVS)) {
      return ["items" => []];
    }
    foreach ($responseGVS['items'] as &$item) {
      $item['web'] = 'gvs';
      $item['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $item['image_id']);
    }
    $code = $req->getHttpCode();
    if ($code != 200) {
      return ["items" => $items];
    }
    $response = ["items" => array_merge($items, $responseGVS["items"])];
    return $response;
  }

  static function executeIncludingAuth(Request $request)
  {
    $user = getUser();
    $username = $user['email'];
    $password = $user['password'];
    $request->execute(array("X-USER: $username", "X-PASSWORD: $password"));
  }

  static function getProduct($type, $web, $id)
  {
    $uri = getProductMap()[$web]($type, $id);
    $req = new Request($uri);
    $req->setRequestType('GET');
    API::executeIncludingAuth($req);
    $response = json_decode($req->getResponse(), true);
    if ($web == "gvs") {
      $response['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $response['image']['id']);
      $response['web'] = 'gvs';
    } else if ($web == "dani") {
      API::normalizeItem($response);
      $response["Current items in cart"] = $response["added_quantity"];
      unset($response["added"]);
      unset($response["added_quantity"]);
      $response["type"] = $type;
    }
    return $response;
  }

  static function addToCart($quantity, $id, $web, $type)
  {

    $uri = getShopAddCartMap()[$web]($id, $type, $quantity);
    $req = new Request($uri);
    $req->setRequestType('POST');
    $req->setPostFields(["quantity" => $quantity]);
    API::executeIncludingAuth($req);
    $resS = $req->getResponse();
    // var_dump($resS);
    $res = json_decode($resS, true);
    if (isset($res['stock'])) {
      $res['quantity'] = $res['stock'];
    }
    return $req->getHttpCode() == 200 && (isset($res['success']) && $res['success']) ? true : false;
  }

  static function deleteFromCart($id, $web, $type)
  {
    $uri = getShopDelete()[$web]($type, $id);
    $req = new Request($uri);
    $req->setRequestType('POST');
    $req->setPostFields([]);
    API::executeIncludingAuth($req);
    $res = json_decode($req->getResponse(), true);
    return $req->getHttpCode() == 200 && ((isset($res['success']) && $res['success']) || $web == "dani") ? true : false;
  }

  static $CART_ITEMS_GVILL = "http://apigvillalonga20.000webhostapp.com/shop/cart";
  static $CART_ITEMS_DANI = "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/shop/cart/";

  static function getCartItemsDani()
  {

    $req = new Request(API::$CART_ITEMS_DANI);
    $req->setRequestType('GET');
    API::executeIncludingAuth($req);
    $responseDani = json_decode($req->getResponse(), true);
    if (!isset($responseDani) || !$responseDani["items"]) {
      return [];
    }
    API::normalizeItemsDani($responseDani["items"]);
    foreach ($responseDani["items"] as &$item) {
      $item["type"] = $item["product_type"];
      $item["item_id"] = $item["product_id"];
    }
    return $responseDani["items"];
  }

  static function getCartItems()
  {
    $req = new Request(API::$CART_ITEMS_GVILL);
    $req->setRequestType('GET');
    API::executeIncludingAuth($req);
    $responseGVS = json_decode($req->getResponse(), true);
    if (!isset($responseGVS)) {
      return ["items" => []];
    }
    foreach ($responseGVS['items'] as &$item) {
      $item['web'] = 'gvs';
      $item['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $item['image_id']);
    }
    $code = $req->getHttpCode();
    $itemsDani = API::getCartItemsDani();
    if ($code != 200) {
      return [];
    }
    $response = array_merge($responseGVS["items"], $itemsDani);
    return ["items" => $response];
  }

  static $FULLFILL_CART_GVS = "http://apigvillalonga20.000webhostapp.com/shop/cart/fulfill";
  static $FULLFILL_CART_DANI = "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/shop/cart/fulfill/";

  static function fullFillCartGVS()
  {
    $req = new Request(API::$FULLFILL_CART_GVS);
    $req->setRequestType('POST');
    API::executeIncludingAuth($req);
    $responseGVS = json_decode($req->getResponse(), true);
    if (!isset($responseGVS["success"]) || $req->getHttpCode() !== 200) {
      return ["success" => false];
    }
    return $responseGVS;
  }

  static function fullFillCartDani()
  {
    $req = new Request(API::$FULLFILL_CART_DANI);
    $req->setRequestType('POST');
    API::executeIncludingAuth($req);
    $responseDani = json_decode($req->getResponse(), true);
    if (!isset($responseDani["success"]) || $req->getHttpCode() !== 200) {
      return ["success" => false];
    }
    return $responseDani;
  }

  static function getOrderItemsGVS($id)
  {
    return "https://apigvillalonga20.000webhostapp.com/orders/order/json?order_id=$id";
  }

  static function getOrderItemsDani($id)
  {
    return "https://apidgracia20.000webhostapp.com/PAPI/projects/keycompass/orders/json/?order_id=$id";
  }

  static function getItemsFromOrder($order)
  {
    $items = [];
    if (isset($order["gabi_id"]) && $order["gabi_id"]) {
      $uri = API::getOrderItemsGVS($order["gabi_id"]);
      $req = new Request($uri);
      $req->setRequestType("GET");
      API::executeIncludingAuth($req);
      $responseGVS = json_decode($req->getResponse(), true);
      if (isset($responseGVS["items"])) {
        foreach ($responseGVS["items"] as $item) {
          $item['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $item["image_id"]);
          $item['web'] = 'gvs';
          $items[] = $item;
        }
      }
    }

    if (isset($order["dani_id"]) && $order["dani_id"]) {
      $uri = API::getOrderItemsDani($order["dani_id"]);
      $req = new Request($uri);
      $req->setRequestType("GET");
      API::executeIncludingAuth($req);
      $responseDani = json_decode($req->getResponse(), true);
      if (isset($responseDani["order_items"])) {
        API::normalizeItemsDani($responseDani["order_items"]);
        foreach ($responseDani["order_items"] as &$item) {
          $item['type'] = $item["item_type"];
          $items[] = $item;
        }
      }
    }

    return $items;
  }
}

// $item = (new Model("cart_items"))->Select('*')->Where(['product_id=' => $product_id])->And(['user_id=' => $user_id])->Do();
// $item = $item[0];
// $products = (new Model($item["product_type"]))->Update()
//   ->Set(['quantity' => new Name("quantity + ${$item['quantity']}")])
//   ->Where(['id=' => $item['product_id']])
//   ->And(['user_id=' => $user_id])
//   ->Do();
