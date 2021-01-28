
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
    }
  ];
  return $URIS_PRODUCT;
}

function getShopDelete()
{
  $URIS_DELETE_CART = [
    'gvs' => function ($type, $id) {
      return "http://apigvillalonga20.000webhostapp.com/shop/cart/remove/$id";
    },
  ];
  return $URIS_DELETE_CART;
}

function getShopAddCartMap()
{
  $URIS_ADD_CART = [
    'gvs' => function ($id, $_type) {
      return "http://apigvillalonga20.000webhostapp.com/shop/cart/$id";
    }
  ];
  return $URIS_ADD_CART;
}


class API
{
  static $REGISTER_URI_GVILL = 'http://apigvillalonga20.000webhostapp.com/sign_up/special_register';
  static $SEARCH_PRODUCTS_GVILL = 'http://apigvillalonga20.000webhostapp.com/search/json?limit=100';
  static $IMG_GVS = 'http://apigvillalonga20.000webhostapp.com/public/ecommerce/files';

  static function getImage($type, $id)
  {
    if ($type == GVS_IMAGE_TYPE) {
      return API::$IMG_GVS . "/$id.png";
    }
  }

  static function registerOnEveryAPI($user)
  {
    // todo: Dani register
    $user['secret'] = 'SECRET_WORD';
    $req = new Request(API::$REGISTER_URI_GVILL);
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

  static function search($searchTerm)
  {
    // todo: Dani search
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
    // TODO: Search Dani products here
    if ($code != 200) {
      return [];
    }
    $response = &$responseGVS;
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
    // todo: Dani get
    $uri = getProductMap()[$web]($type, $id);
    $req = new Request($uri);
    $req->setRequestType('GET');
    API::executeIncludingAuth($req);
    $response = json_decode($req->getResponse(), true);
    if ($web == "gvs") {
      $response['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $response['image']['id']);
      $response['web'] = 'gvs';
    } else if ($web == "dani") {
      // TODO:  Dani action products here
      // TODO:
    }
    return $response;
  }

  static function addToCart($quantity, $id, $web, $type)
  {
    // todo: Dani add
    $uri = getShopAddCartMap()[$web]($id, $type);
    $req = new Request($uri);
    $req->setRequestType('POST');
    $req->setPostFields(["quantity" => $quantity]);
    API::executeIncludingAuth($req);
    $res = json_decode($req->getResponse(), true);
    return $req->getHttpCode() == 200 && isset($res['success']) && $res['success'] ? true : false;
  }

  static function deleteFromCart($id, $web, $type)
  {
    // todo: Dani delete
    $uri = getShopDelete()[$web]($type, $id);
    $req = new Request($uri);
    $req->setRequestType('POST');
    $req->setPostFields([]);
    API::executeIncludingAuth($req);
    $res = json_decode($req->getResponse(), true);
    return $req->getHttpCode() == 200 && isset($res['success']) && $res['success'] ? true : false;
  }

  static $CART_ITEMS_GVILL = "http://apigvillalonga20.000webhostapp.com/shop/cart";

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
    // TODO: Search Dani products here
    if ($code != 200) {
      return [];
    }
    $response = &$responseGVS;
    return $response;
  }

  static $FULLFILL_CART_GVS = "http://apigvillalonga20.000webhostapp.com/shop/cart/fulfill";

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

  static function getOrderItemsGVS($id)
  {
    return "https://apigvillalonga20.000webhostapp.com/orders/order/json?order_id=$id";
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
      // TODO: 
    }

    return $items;
  }
}
