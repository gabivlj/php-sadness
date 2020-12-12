<?php

require_once './public/ecommerce/models/user.php';
require_once './public/ecommerce/email.php';

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
    Signup::$instance->get("/verification", ['verification_html']);
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
  function register()
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
    $hostname = App::get_host();
    $protocol = App::get_protocol();
    if (!sendEmail($email, $username, "Dear $username,<br>please, verify your email clicking in this link: $protocol://$hostname/sign_up/verification?e=$email&t=$response")) {
      App::status_code(400);
      App::json(['message' => 'Email verification is not working! TODO: Reset user data']);
      return;
    }
    App::status_code(200);
    App::json(['message' => 'Starting email verification!']);
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

  function verification_html()
  {
    HtmlRoot::prep(['//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.3.1/styles/default.min.css']);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style("$tailwindCSS")
    );
    require './public/ecommerce/views/verification.php';
    $query = App::query_params();
    if (!isset($query['e']) || !isset($query['t'])) {
      HtmlRoot::append(new HtmlElement(
        "a",
        ['href' => '/sign_up/login', 'class' => 'underline m-4'],
        'You should not be here, click here to come back'
      ));
    } else {
      Verification::view($query['e'], $query['t']);
    }
    HtmlRoot::end();
  }
}
