<?php

class Items extends Controller
{
  static $instance;

  static $available_types = [
    'albums' => true,
    'headset' => true,
    'players' => true,
    'artists' => false,
    'users' => false
  ];
  static $user = [];

  static function init()
  {
    $ins = new Items("/items");
    Items::$instance = $ins;
    $ins->get("/admin/:type", ['fill_admin', 'get_items']);
    $ins->get("/admin/:type/:id", ['fill_admin', 'get_item']);
    $ins->post("/admin/:type", ['fill_admin', 'post_item']);
  }

  function get_item()
  {
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
    $ok = $model->Create(['table_id' => $type, 'item_id' => $id, 'id' => $id])->Do();
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

    // Proceduraly get the inputs that we need to create this type of 
    // items
    $columns = new Model('INFORMATION_SCHEMA.COLUMNS');
    $columnTypesSchema = $columns
      ->Select('COLUMN_NAME')
      ->Where(['TABLE_NAME=' => new Name("N'$type'")])
      ->Do();
    // Map the values to something readable for Form class
    $columnTypes = array_map(function ($el) {
      return $el['COLUMN_NAME'];
    }, $columnTypesSchema);
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
