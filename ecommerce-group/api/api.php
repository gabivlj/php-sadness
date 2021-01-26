
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
  static $SEARCH_PRODUCTS_GVILL = 'https://apigvillalonga20.000webhostapp.com/search/json?limit=100';
  static $IMG_GVS = 'https://apigvillalonga20.000webhostapp.com/public/ecommerce/files';

  static function getImage($type, $id)
  {
    if ($type == GVS_IMAGE_TYPE) {
      return API::$IMG_GVS . "/$id.png";
    }
  }

  static function registerOnEveryAPI($user)
  {
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
    // TODO: Search products here
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
    $uri = getProductMap()[$web]($type, $id);
    $req = new Request($uri);
    $req->setRequestType('GET');
    API::executeIncludingAuth($req);
    $response = json_decode($req->getResponse(), true);
    if ($web == "gvs") {
      $response['image_uri'] = API::getImage(GVS_IMAGE_TYPE, $response['image']['id']);
      $response['web'] = 'gvs';
    } else if ($web == "dani") {
      // TODO:
    }
    return $response;
  }

  static function addToCart($quantity, $id, $web, $type)
  {
    $uri = getShopAddCartMap()[$web]($id, $type);
    $req = new Request($uri);
    $req->setRequestType('POST');
    $req->setPostFields(["quantity" => $quantity]);
    API::executeIncludingAuth($req);
    $_ = json_decode($req->getResponse());
    return $req->getHttpCode() == 200 ? true : false;
  }
}
