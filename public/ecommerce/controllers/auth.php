<?php

class Auth extends Controller
{

  public $user = null;

  public function fill_admin()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      App::set_response_header('location', '/sign_up/login');
      $this->stop();
      return;
    }
    $id = $_SESSION['id'];
    $this->user = User::getById($id);
    if (!$this->user || !$this->user['admin']) {
      App::set_response_header('location', '/home');
      $this->stop();
      return;
    }
  }

  public function fill_user()
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


  function render_view($view)
  {
    HtmlRoot::prep([]);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style($tailwindCSS)
    );
    require_once './public/ecommerce/views/navbar.php';
    HtmlRoot::append(
      HtmlElement::Body()
        ->append(navBarVerified($this->user['username'], isset($this->user['admin'])
          && $this->user['admin']))
        ->append($view)
    );
    HtmlRoot::end();
  }

  function render($html_path)
  {
    HtmlRoot::prep([]);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style($tailwindCSS)
    );
    require_once './public/ecommerce/views/navbar.php';
    HtmlRoot::append(
      HtmlElement::Body()
        ->append(navBarVerified($this->user['username'], isset($this->user['admin'])
          && $this->user['admin']))
        ->append(HtmlElement::raw(file_get_contents($html_path)))
    );
    HtmlRoot::end();
  }
}
