<?php

require_once './public/ecommerce/models/user.php';
require_once './public/ecommerce/email.php';

class Signup extends Controller
{
  static $instance;

  static function init()
  {
    Signup::$instance = new Signup("/sign_up");
    Signup::$instance->post("/register", ['middleware_redirect', 'register']);
    Signup::$instance->get("/register", ['middleware_redirect', 'register_html']);
    Signup::$instance->get("/login", ['middleware_redirect', 'login_html']);
    Signup::$instance->post("/login", ['middleware_redirect', 'login']);
    Signup::$instance->post("/verification", ['middleware_redirect', 'verify']);
    Signup::$instance->get("/verification", ['middleware_redirect', 'verification_html']);
  }

  function middleware_redirect()
  {
    session_start();
    if (isset($_SESSION['id'])) {
      App::set_response_header('location', '/home');
      $this->stop();
    }
  }

  /**
   * POST request
   * @body [email, password]
   * @response 200 success otherwise it's bad. Returns the user in "user" json and a message which  * is "Success!"
   */
  function login()
  {
    $body = App::body(true);
    $email = $body['email'];
    $password = $body['password'];
    $user = User::checkPassword($email, $password);
    if (!$user) {
      App::status_code(400);
      App::json(['message' => 'Invalid credentials']);
      return;
    }
    session_start();
    $_SESSION['email'] =  $user['email'];
    $_SESSION['id'] =  $user['id'];
    $_SESSION['username'] =  $user['username'];
    App::json(['user' => $user, 'message' => 'Success!', 'redirect' => '/home']);
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
    $error = null;
    if (strlen($username) < 4 || strlen($username) > 16) {
      $error = "Username should be atleast between 4 and 16 characters";
    }
    if (strlen($email) < 4 || strlen($email) > 128) {
      $error = "Enter a valid email";
    }
    if (strlen($password) < 4) {
      $error = "Password is too short";
    }
    if ($error) {
      App::status_code(400);
      App::json(['message' => $error]);
      return;
    }
    if (User::exists($username, $email)) {
      App::status_code(400);
      App::json(['message' => 'User already exists!']);
      return;
    }
    $response = User::createUser($username, $password, $email);
    $hostname = App::get_host();
    $protocol = App::get_protocol();
    if (!sendEmail($email, $username, "Dear $username,<br>please, verify your email clicking in this link: $protocol://$hostname/sign_up/verification?e=$email&t=$response")) {
      App::status_code(400);
      App::json(['message' => 'Email verification is not working!']);
      return;
    }
    App::status_code(200);
    App::json(['message' => 'Starting email verification!', 'redirect' => '/sign_up/login']);
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
    HtmlRoot::prep([]);
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

  function register_html()
  {
    HtmlRoot::prep([]);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style("$tailwindCSS")
    );

    require_once './public/ecommerce/views/navbar.php';
    HtmlRoot::append(
      HtmlElement::Body()
        ->append(navBarUnverified())
        ->append(HtmlElement::raw(file_get_contents("./public/ecommerce/html/register.html")))
    );
    HtmlRoot::append(HtmlElement::Javascript("/public/ecommerce/js/form_log.js"));
    HtmlRoot::end();
  }

  function login_html()
  {
    HtmlRoot::prep([]);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style("$tailwindCSS")
    );
    require_once './public/ecommerce/views/navbar.php';
    HtmlRoot::append(
      HtmlElement::Body()
        ->append(navBarUnverified())
        ->append(HtmlElement::raw(file_get_contents("./public/ecommerce/html/login.html")))
    );
    HtmlRoot::append(HtmlElement::Javascript("/public/ecommerce/js/form_log.js"));
    HtmlRoot::end();
  }
}
