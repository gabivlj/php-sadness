<?php
require_once './public/ecommerce/controllers/auth.php';
class Search extends Auth
{
  static $selection_params = "items.id_ext as id, items.price as price, items.quantity as quantity, items.type as type, image.id as image_id";
  static $instance;
  static $search_params = [
    'name' => true,
    'weight' => true,
    'cable_length' => true,
    'headset_type' => true,
    'bluetooth' => true,
    'brand' => true,
    'rpm' => true,
    'price' => true,
    'quantity' => true,
    'genre' => true,
  ];

  static function init()
  {
    $ins = new Search("/search");
    Search::$instance = $ins;
    $ins->get("/:type/json", ['fill_user', 'find_products_by_type']);
    $ins->get("/:type", ['fill_user', 'find_products_html']);
    $ins->get("/", ['try_fill_user', 'find_products']);
    $ins->get("/json", ['find_products_general_json']);
  }

  function find_products()
  {
    $this->render("./public/ecommerce/html/home.html");
  }

  function find_products_general_json()
  {
    $this->search_general();
  }


  function find_products_html()
  {
    $type = App::$uri_params['type'];
    $this->render("./public/ecommerce/html/products_$type.html");
  }

  function order_by_and_pagination(QueryOptionNonInsert $query, $queryParams, Where $where)
  {
    $priceMin = 0;
    $priceMax = 10000;
    if (isset($queryParams['price_min'])) {
      $priceMin = intval($queryParams['price_min']);
      if (!$priceMin) {
        $priceMin = 0;
      }
    }
    if (isset($queryParams['price_max'])) {
      $priceMax = intval($queryParams['price_max']);
      if (!$priceMax) {
        $priceMax = 10000;
      }
    }
    $where->And(['price>' => $priceMin]);
    $where->And(['price<' => $priceMax]);
    if (!isset($queryParams['order_by'])) {
      $query->OrderBy("items.id_ai DESC");
    } else if ($queryParams['order_by'] === 'price_asc') {
      $query->OrderBy("items.price DESC");
    } else if ($queryParams['order_by'] === 'price_asc') {
      $query->OrderBy("items.price ASC");
    } else {
      $query->OrderBy("items.id_ai DESC");
    }
    $limit = isset($queryParams['limit']) ? $queryParams['limit'] : 10;
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $query->Limit(($page - 1) * $limit, $limit);
    $query->InnerJoin('image', ['image.item_id=' => new Name('items.id_ext')]);
  }

  public function search_general()
  {
    $queryParams = App::query_params();
    $model = new Model('items');
    $params = Search::$selection_params;
    $query = $model->Select("$params, headset.name as name_headset, albums.name as name_albums, players.name as name_players");
    $query->LOJoin('albums', ['albums.id=' => new Name('items.id_ext')]);
    $query->LOJoin('headset', ['headset.id=' => new Name('items.id_ext')]);
    $query->LOJoin('players', ['players.id=' => new Name('items.id_ext')]);
    // Dummy Where so we can do readable buildup on it
    $where = new Where();
    if (isset($queryParams['name'])) {
      $name = "%{$queryParams['name']}%";
      $where->And(['albums.name LIKE ' => $name])
        ->Or(['headset.name LIKE ' => $name])
        ->Or(['players.name LIKE ' => $name]);
    }
    $this->order_by_and_pagination($query, $queryParams, $where);
    $query->Where($where);
    $rows = $query->Do();
    if (!$rows) {
      App::json(['items' => []]);
    } else {
      $rows = array_map(function ($el) {
        $el['name'] = $el["name_{$el['type']}"];
        return $el;
      }, $rows);
      App::json(['items' => $rows]);
    }
  }

  function find_products_by_type()
  {
    // QueryOptions::$DEBUG_QUERIES = true;
    $uriParams = App::$uri_params;
    $type = $uriParams['type'];
    if (!isset(Items::$available_types[$type])) {
      App::json(['items' => []]);
      return;
    }
    // QueryOptions::$DEBUG_QUERIES = true;
    $queryParams = App::query_params();
    $model = new Model($uriParams['type']);
    $params = Search::$selection_params;
    if ($type === 'albums') {
      $query = $model->Select("$params, albums.name as name, artists.name as artist_name");
    } else {
      $query = $model->Select("$params, $type.name as name");
    }
    // Dummy Where
    $where = new Where(['items.id_ext=' => new Name('items.id_ext')]);
    $query->InnerJoin('items', ['items.id_ext=' => new Name("$type.id")]);
    $this->order_by_and_pagination($query, $queryParams, $where);
    $rows = [];
    $doneWhere = false;
    foreach ($queryParams as $att => $val) {
      if (!isset(Search::$search_params[$att])) {
        continue;
      }
      $where->And(["$type.$att LIKE " => "%$val%"]);
    }
    $query->Where($where);
    if ($type === "albums" && isset($queryParams['artist'])) {
      $query->In(
        'albums.artist_id',
        (new Model('artists'))
          ->Select('id')
          ->Where(['artists.name LIKE ' => "%{$queryParams['artist']}%"])
      );
    }
    if ($type === 'albums') {
      $query->InnerJoin('artists', ['artists.id=' => new Name('albums.artist_id')]);
    }
    $rows = $query->Do();
    if (!$rows) {
      App::json(['items' => []]);
    } else {
      App::json(['items' => $rows]);
    }
  }

  function search_albums(QueryOptionNonInsert $query, $queryParams)
  {
  }
}
