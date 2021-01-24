
<?php
require_once dirname(__DIR__) . '/api/request.php';

define("GVS_IMAGE_TYPE", 1);
define("DANI_IMAGE_TYPE", 2);

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
    if ($code != 200) {
      return [];
    }
    $response = &$responseGVS;
    return $response;
  }
}
