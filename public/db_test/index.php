<?php

require './express-php/db/db.php';

class Handlers extends Controller
{
  function test()
  {
    HtmlRoot::prep([]);
    HtmlRoot::append(HtmlElement::Body()->append(new HtmlElement("div", [], ['hey'])));
    $model = new Model("user");
    $users = $model
      ->Select('user.username')
      ->Where(['createdAt>' => 1])
      ->Limit(3)
      ->OrderBy("user.createdAt DESC")
      ->Do();
    $model
      ->Create([
        'username' => 'epicgamer',
        'password' => '123456',
        'id' => 'randomstuff'
      ])
      ->Do();

    var_dump(print_r($users), true);
    HtmlRoot::end();
  }
}

$db_test_controller = new Handlers("/db_test");

$db_test_controller->get("/", ['test']);
