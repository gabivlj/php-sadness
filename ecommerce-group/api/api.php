<?php

class API
{
  static $REGISTER_URI_GVILL = 'http://apigvillalonga20.000webhostapp.com/sign_up/special_register';

  static function registerOnEveryAPI($user)
  {
    require_once dirname(__DIR__) . '/api/request.php';
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
}
