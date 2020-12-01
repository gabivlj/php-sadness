<?php

require './express-php/db/db.php';

class Handlers extends Controller
{
  function test()
  {
    // Prints the queries that we are making on debug
    QueryOptions::$DEBUG_QUERIES = true;

    // Nothing to prepare for, just raw html 
    HtmlRoot::prep([]);

    // Just initialize html
    $div = HtmlElement::Body()->append(new HtmlElement("div", [], []));
    // append div for later
    HtmlRoot::append($div);

    // Get most recent users and that have a student status
    $whereJoin = new Where(['students.name=' => new Name('user.username')]);
    $model = new Model(["user"]);
    $users = $model
      ->Select('user.username, user.createdAt')
      // as we can see, you can add where inside a where condition and 
      // the ORM will parse it as a (...whereConditions && (...whereConditions))
      // so you can chain
      ->Join("students", $whereJoin->And($whereJoin))
      ->Where(['user.createdAt>' => 1])
      // ->And(['students.name=' => new Name('user.username')])
      ->Limit(3)
      ->OrderBy("user.createdAt DESC")
      ->Do();

    // Map the elements to html
    $user_elements = array_map(function ($user) {
      return new HtmlElement('li', [], ["{$user['username']} created at {$user['createdAt']}"]);
    }, $users);

    // Return list
    $div->append(new HtmlElement('ul', [], $user_elements));
    $div->append(new HtmlElement('input', [
      'id' => 'username_input',
      'placeholder' => 'Username'
    ], ['']));
    $div->append(new HtmlElement('input', [
      'id' => 'password_input',
      'placeholder' => 'Password',
      'type' => 'password'
    ], ['']));
    $div->append(new HtmlElement('button', ['id' => 'submit'], ['Submit user']));
    HtmlRoot::append(HtmlElement::Javascript('./public/db_test/js/form.js'));
    HtmlRoot::end();
  }

  /**
   * @param username Username to create
   * @param password Password to use
   */
  function post_user()
  {
    $body = App::body(true);
    $model = new Model("user");
    // var_dump($body['username']);
    $ok = $model->Create([
      'username' => $body['username'],
      'password' => $body['password'],
      'id' => $this->create_id(),
    ])->Do();
    $studentsModel = new Model('students');
    if ($ok === false) {
      App::status_code(400);
      App::json(['success' => false, 'data' => null]);
      return;
    }
    $ok = $studentsModel->Create([
      'name' => $body['username'],
    ])->Do();
    if ($ok === false) {
      App::status_code(400);
      App::json(['success' => false, 'data' => null]);
      return;
    }
    App::status_code(200);
    App::json([
      'success' => true,
      'data' => ['username' => $body['username']]
    ]);
  }

  function create_id(): string
  {
    require './express-php/uuid.php';
    return UUID::v4();
  }
}

$db_test_controller = new Handlers("/db_test");

$db_test_controller->get("/", ['test']);
$db_test_controller->post("/", ['post_user']);
