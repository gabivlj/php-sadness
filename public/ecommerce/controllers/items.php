<?php

class Items extends Controller
{
  static $instance;

  static $available_types = [
    'albums' => true,
    'headset' => true,
    'players' => true,
    'artists' => false,
    'users' => false,
    'images' => false,
  ];
  static $available_image_types = [
    'albums' => true,
    'headset' => true,
    'players' => true,
    'artists' => true,
    'users' => false,
    'images' => false,
  ];
  static $user = [];

  static function init()
  {
    $ins = new Items("/items");
    Items::$instance = $ins;
    $ins->get("/admin/:type", ['fill_admin', 'get_items']);
    $ins->get("/admin/:type/:id", ['fill_admin', 'get_item']);
    $ins->post("/admin/update/:type/:id", ['fill_admin', 'update_item']);
    $ins->post("/admin/delete/:type/:id", ['fill_admin', 'delete_item']);
    $ins->post("/admin/:type", ['fill_admin', 'post_item']);
  }

  function delete_item()
  {
    // QueryOptions::$DEBUG_QUERIES = true;
    $uriParams = App::$uri_params;
    $id = $uriParams['id'];
    $type = $uriParams['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    if (Items::$available_types[$type]) {
      $model = new Model("items");
      $ok = $model
        ->Delete()
        ->Where(['id_ext=' => $id])
        ->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    if (Items::$available_image_types[$type]) {
      $model = new Model("image");
      $ok = $model
        ->Delete()
        ->Where(['item_id=' => $id])
        ->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    $model = new Model($type);
    $ok = $model
      ->Delete()
      ->Where(['id=' => $id])
      ->Do();
    if (!$ok) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    App::set_response_header('location', "/items/admin/$type");
  }

  function update_item()
  {
    unset($_POST['id']);
    $uriParams = App::$uri_params;
    $id = $uriParams['id'];
    $type = $uriParams['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $itemKeys = [];
    foreach ($_POST as $key => $val) {
      if (trim($val) === '') {
        unset($_POST[$key]);
        continue;
      }
      if ($key === 'price' || $key === 'quantity') {
        unset($_POST[$key]);
        $itemKeys[$key] = intval($val);
      }
    }

    if (count($_POST) > 0) {
      $model = new Model($type);
      $ok = $model
        ->Update()
        ->Where(['id=' => $id])
        ->Set($_POST)
        ->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    if (count($itemKeys) > 0) {
      $model = new Model('items');
      $ok = $model
        ->Update()
        ->Where(['id_ext=' => $id])
        ->Set($itemKeys)
        ->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    $file = App::get_file('file');
    if ($file != null && $file['size'] != 0) {
      if ($file['size'] > 10000000) {
        App::json(['success' => false, 'message' => 'file too big...']);
        return;
      }
      $fileId = UUID::v4();
      move_uploaded_file(
        $file["tmp_name"],
        "./public/ecommerce/files/$fileId.png"
      );
      $model = new Model("image");
      $ok = $model->Create(['table_id' => $type, 'item_id' => $id, 'id' => $fileId])->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }

      return;
    }
    App::set_response_header('location', "/items/admin/$type/$id");
  }

  function get_item()
  {
    require_once './public/ecommerce/views/table.php';
    require_once './public/ecommerce/views/form.php';
    require_once './public/ecommerce/views/delete_button.php';
    $uriParams = App::$uri_params;
    $id = $uriParams['id'];
    $type = $uriParams['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $model = new Model($type);
    $query = $model
      ->Select('*')
      ->Where(['id=' => $id])
      ->Limit(1);
    // check if the type supports items
    if (Items::$available_types[$type]) {
      $query->InnerJoin('items', ['items.id_ext=' => new Name("$type.id")]);
    }
    $root = new HtmlElement(
      'div',
      ['class' => 'container'],
      []
    );
    $rows = $query->Do();
    // Map the values to something readable for Form class
    $columnTypes = $this->column_types($type);
    if ($type !== 'users') {
      $form = new Form(array_merge(
        Items::$available_types[$type] ? ['price', 'quantity'] : [],
        $columnTypes
      ), "/items/admin/update/$type/$id");
      $root->append($form->render('Update Item'));
    }
    if (Items::$available_image_types[$type]) {
      $images = (new Model('image'))
        ->Select('*')
        ->Where(['item_id=' => $rows[0]['id']])
        ->And(['table_id=' => $type])
        ->Do();
      foreach ($images as $image) {
        $root->append(new HtmlElement(
          'img',
          ['src' => "/public/ecommerce/files/{$image['id']}.png", 'style' => 'width:20%'],
          ''
        ));
      }
    }
    $root->append((new Table($rows, $type))->render());
    $root->append((new DeleteButton())->render("/items/admin/delete/$type/$id"));
    $this->render_view($root);
  }

  function post_item()
  {
    $params = App::$uri_params;
    $type = $params['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $file = App::get_file('file');
    if ($file == null) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    if ($file['size'] > 10000000) {
      App::json(['success' => false, 'message' => 'file too big...']);
      return;
    }
    $fileId = UUID::v4();
    move_uploaded_file(
      $file["tmp_name"],
      "./public/ecommerce/files/$fileId.png"
    );
    $id = UUID::v4();
    $model = new Model($type);
    $data = $_POST;
    $itemSpecificData = [];
    if (Items::$available_types[$type]) {
      $itemSpecificData['price'] = $_POST['price'];
      $itemSpecificData['quantity'] = $_POST['quantity'];
      unset($data['price']);
      unset($data['quantity']);
    }
    $data['id'] = $id;
    $ok = $model
      ->Create($data)
      ->Do();
    if (!$ok) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $model = new Model("image");
    $ok = $model->Create(['table_id' => $type, 'item_id' => $id, 'id' => $fileId])->Do();
    if (!$ok) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    if (Items::$available_types[$type]) {
      $model = new Model('items');
      $ok = $model->Create([
        'id_ext' => $id,
        'quantity' => intval($itemSpecificData['quantity']),
        'price' => intval($itemSpecificData['price']),
        'type' => $type
      ])->Do();
      if (!$ok) {
        $this->render("./public/ecommerce/html/not_found.html");
        return;
      }
    }
    App::set_response_header('location', "/items/admin/$type");
  }

  function get_items()
  {
    $params = App::$uri_params;
    $type = $params['type'];
    if (!isset(Items::$available_types[$type])) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    $itemsType = new Model($type);
    $rows = [];
    if (Items::$available_types[$type]) {
      $rows = $itemsType
        ->Select('*')
        ->InnerJoin('items', ['items.id_ext=' => new Name("$type.id")])
        ->Do();
      // this means that it's not an item that should be bought
    } else {
      $rows = $itemsType
        ->Select('*')
        ->Do();
    }
    if ($rows === false) {
      $this->render("./public/ecommerce/html/not_found.html");
      return;
    }
    require_once './public/ecommerce/views/table.php';
    require_once './public/ecommerce/views/form.php';
    // Map the values to something readable for Form class
    $columnTypes = $this->column_types($type);
    // Render form and tell it to post to /items/admin/$type
    $form = null;
    // Don't render a form for users
    if ($type !== 'users') {
      $form = new Form(array_merge(
        Items::$available_types[$type] ? ['price', 'quantity'] : [],
        $columnTypes
      ), "/items/admin/$type");
    }
    // Render the rows that we got of the available items
    $table = new Table($rows, $type);
    // Put it all on a root
    $root = new HtmlElement(
      'div',
      ['class' => 'container'],
      [$table->render(), $form ? $form->render() : '']
    );
    $this->render_view($root);
  }

  function column_types($type)
  {
    $columns = new Model('INFORMATION_SCHEMA.COLUMNS');
    $columnTypesSchema = $columns
      ->Select('COLUMN_NAME')
      ->Where(['TABLE_NAME=' => new Name("N'$type'")])
      ->Do();
    // Map the values to something readable for Form class
    $columnTypes = array_map(function ($el) {
      return $el['COLUMN_NAME'];
    }, $columnTypesSchema);
    return $columnTypes;
  }

  function fill_admin()
  {
    session_start();
    if (!isset($_SESSION['id'])) {
      App::set_response_header('location', '/sign_up/login');
      $this->stop();
      return;
    }
    $id = $_SESSION['id'];
    Items::$user = User::getById($id);
    if (!Items::$user || !Items::$user['admin']) {
      App::set_response_header('location', '/home');
      $this->stop();
      return;
    }
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
        ->append(navBarVerified(Items::$user['username']))
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
        ->append(navBarVerified(Items::$user['username']))
        ->append(HtmlElement::raw(file_get_contents($html_path)))
    );
    HtmlRoot::end();
  }
}
