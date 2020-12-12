<?php

require_once './public/ecommerce/models/user.php';

class Signup extends Controller
{
  static $instance;

  static function init()
  {
    Signup::$instance = new Signup("/sign_up");
    Signup::$instance->post("/register", ['register']);
    Signup::$instance->get("/register", ['register_html']);
    Signup::$instance->get("/login", ['login_html']);
    Signup::$instance->post("/login", ['login']);
    Signup::$instance->post("/verification", ['verify']);
    Signup::$instance->get("/test/:username/:email", ['test']);
  }

  function test()
  {
    $user = User::exists(App::$uri_params['username'], App::$uri_params['email']);
    if ($user == null) {
      App::status_code(404);
      return App::json(['error' => 'User of specified id not found']);
    }
    App::json(['user' => $user]);
  }

  /**
   * POST request
   * @body [username, email, password]
   * @response 200 if successful, otherwise is not successful
   */
  function login()
  {
    $body = App::body(true);
    $username = $body['username'];
    $email = $body['email'];
    $password = $body['password'];
    if (User::exists($username, $email)) {
      App::status_code(400);
      App::json(['message' => 'user already exists!']);
      return;
    }
    $response = User::createUser($username, $password, $email);
    App::status_code(200);
    App::json(['verified' => $response]);
  }

  /**
   * POST request
   * @body [token, email]
   * @response 200 if successful, otherwise is not successful
   */
  function verify()
  {
    $body = App::body(true);
    $token = $body['token'];
    $email = $body['email'];
    $result = User::confirmUser($email, $token);
    if (!$result) {
      App::status_code(400);
      App::json(['message' => 'verification failed for this user!']);
      return;
    }
    App::status_code(200);
    App::json(['message' => 'verification is successful!', 'user' => $result]);
  }
}
