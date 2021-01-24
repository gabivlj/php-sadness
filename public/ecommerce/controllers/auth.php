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

  public function try_fill_user()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      $headers = getallheaders();
      if (!isset($headers['X-USER'])) return;
      $user_email = $headers['X-USER'];
      $user_password = $headers['X-PASSWORD'];
      $users = (new Model('external_users'))
        ->Select('*')
        ->Where(['password_hashed=' => $user_password])
        ->And(['email=' => $user_email])
        ->Do();
      if ($users) {
        $this->user = ['id' => $users[0]['email'], 'email' => $users[0]['email'], 'username' => $users[0]['email'], 'json' => true];
      }
      return;
    }
    $id = $_SESSION['id'];
    $this->user = User::getById($id);
    $this->user['json'] = false;
  }

  public function fill_user()
  {
    session_start();
    if (!isset($_SESSION['id']) || isset($headers['X-USER'])) {
      $headers = getallheaders();
      if (!isset($headers['X-USER'])) {
        App::set_response_header('location', '/sign_up/login');
        $this->stop();
        return;
      }
      $user_email = $headers['X-USER'];
      $user_password = $headers['X-PASSWORD'];
      $users = (new Model('external_users'))->Select('*')->Where(['password_hashed=' => $user_password])->And(['email=' => $user_email])->Do();
      if ($users) {
        $this->user = ['id' => $users[0]['email'], 'email' => $users[0]['email'], 'username' => $users[0]['email'], 'json' => true];
        return;
      } else {
        App::json(['error' => 'Unauthorized']);
        $this->stop();
        return;
      }
    }
    $id = $_SESSION['id'];
    $this->user = User::getById($id);
    $this->user['json'] = false;
  }

  function render_view($view)
  {
    HtmlRoot::prep([]);
    $tailwindCSS = file_get_contents("./public/portfolio/css/main.css");
    HtmlRoot::$head->append(
      HtmlElement::Style($tailwindCSS)
    );
    require_once './public/ecommerce/views/navbar.php';
    $navbar = null;
    if ($this->user === null) {
      $navbar = navBarUnverified();
    } else {
      $navbar = navBarVerified(
        $this->user['username'],
        isset($this->user['admin']) && $this->user['admin']
      );
    }
    HtmlRoot::append(
      HtmlElement::Body()
        ->append($navbar)
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
    $navbar = null;
    if ($this->user === null) {
      $navbar = navBarUnverified();
    } else {
      $navbar = navBarVerified($this->user['username'], isset($this->user['admin'])
        && $this->user['admin']);
    }
    HtmlRoot::append(
      HtmlElement::Body()
        ->append($navbar)
        ->append(HtmlElement::raw(file_get_contents($html_path)))
    );
    HtmlRoot::end();
  }
}
