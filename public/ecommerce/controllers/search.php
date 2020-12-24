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
    $ins->get("/", ['fill_user', 'find_products']);
  }

  function find_products_html()
  {
    $type = App::$uri_params['type'];
    $this->render("./public/ecommerce/html/products_$type.html");
  }

  function order_by_and_pagination(QueryOptionNonInsert $query, $queryParams)
  {
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

  function find_products_by_type()
  {
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
    $query->InnerJoin('items', ['items.id_ext=' => new Name("$type.id")]);
    $this->order_by_and_pagination($query, $queryParams);
    $rows = [];
    $doneWhere = false;
    foreach ($queryParams as $att => $val) {
      if (!isset(Search::$search_params[$att])) {
        continue;
      }
      if (!$doneWhere) {
        $query->Where(["$type.$att LIKE " => "%$val%"]);
        $doneWhere = true;
      } else {
        $query->And(["$type.$att LIKE " => "%$val%"]);
      }
    }
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
