
<?php

/**
 * Contains a hardcoded string that a Query won't try to use param binding `?`
 * 
 * Useful for SQL programming, please don't pass here User depending input.
 */
class Name
{
  public $name;
  function __construct($query)
  {
    $this->name = $query;
  }
}

class QueryOptions
{
  /**
   * Put this to true to get prints on the webpage of how cool your SQL queries look!
   * 
   * This is dangerous on production, please be careful.
   */
  static $DEBUG_QUERIES = false;
}

/**
 * Contains the (?, ?) statement and the key values to fill with
 */
class SafeQuery
{
  public $stmt;
  public $keys;
  function __construct($stmt, $keys)
  {
    $this->stmt = $stmt;
    $this->keys = $keys;
  }
}

function processValueType($value)
{
  $type = gettype($value);
  if ($type == "string") return "?";
  if ($type == "integer" || $type == "float") return "?";
  if (get_class($value) == 'Name') {
    return $value->name;
  }
  throw new Exception("ERROR: Unknown type :(");
}

function getSqlType($value)
{
  if (gettype($value) == "string") {
    return "s";
  }
  if (gettype($value) == "integer") {
    return "i";
  }
  if (gettype($value) == "double") {
    return "d";
  }

  // [UNDEFINED BEHAVIOUR] tell the user to use another type
  throw new Exception("[ERROR] Unkown type " . gettype($value) . "; consider using string, number or a double");
  return 's';
}

function concat($arr, $arr2)
{
  $arrRes = [];
  foreach ($arr as $_ => $val) {
    array_push($arrRes, $val);
  }
  foreach ($arr2 as $_ => $val) {
    array_push($arrRes, $val);
  }
  return $arrRes;
}

class Where
{
  public $query;
  function keyValue($keyValue)
  {
    if (gettype($keyValue) == "object" && get_class($keyValue) == "Where") {
      $this->query = concat($this->query, $keyValue->query);
      return "($keyValue->cond)";
    }
    foreach ($keyValue as $key => $valueUnmodified) {
      $value = processValueType($valueUnmodified);
      if ($value == "?") {
        array_push($this->query, [getSqlType($valueUnmodified), $valueUnmodified]);
      }
      return "$key$value";
    }
  }

  function __construct($start)
  {
    $this->query = [];
    $this->cond = $this->keyValue($start);
  }

  /**
   * And. Can be passed a Where class as well.
   */
  function And($more)
  {
    $this->cond .= "&&" . $this->keyValue($more);
    return $this;
  }

  /**
   * Or. Can be passed a Where class as well.
   */
  function Or($more)
  {
    $this->cond .= "||" . $this->keyValue($more);
    return $this;
  }
}

function reduce($arr, $cb, $start)
{
  foreach ($arr as $key => $value) {
    $start = $cb($start, $key, $value);
  }
  return $start;
}

class QueryOptionInsert
{
  private $parent;
  private $params;

  function __construct($parent, $model, $object)
  {
    $this->parent = $parent;
    $this->model = $model;
    $this->object = $object;
    $this->params = [];
  }

  /**
   * Finish your INSERT
   */
  function Do()
  {
    // Create the keys string (key1, key2...)
    $valKeys = reduce($this->object, function ($prev, $key, $value) {
      if (strlen($prev) == 1) return "$prev`$key`";
      return "$prev, `$key`";
    }, '(');
    // Create (?, ?...) string and fills the params array
    $valValues = reduce($this->object, function ($prev, $key, $originalValue) {
      $value = processValueType($originalValue);
      if ($value == "?")
        $this->params[] = [getSqlType($originalValue), $originalValue];
      if (strlen($prev) == 1) return "$prev$value";;
      return "$prev, $value";
    }, '(');
    $valKeys .= ')';
    $valValues .= ')';
    $str = "INSERT INTO {$this->model} $valKeys VALUES $valValues";
    if (QueryOptions::$DEBUG_QUERIES) {
      echo $str;
    }
    return $this->parent->processQuery($str, $this->params);
  }

  /**
   * Return the Id of your INSERT
   */
  static function LastId()
  {
    return Model::$sqli->insert_id;
  }
}

class QueryOptionSelect
{
  public  $stmt;

  protected Where $where;

  function __construct($parent, $stmt, $tables = [], $returns = '*')
  {
    $this->stmt = $stmt;
    $this->limit = null;
    $this->where = null;
    $this->join = null;
    $this->tables = $tables;
    $this->parent = $parent;
    $this->returns = $returns;
    $this->orderBy = '';
    $this->params = [];
  }

  /**
   * Does a Where condition
   * 
   * $innerOr = new Where(["cool=" => 3]);
   * $innerOr->Or(["cool=" => 3]);
   * You can do cool stuff like $stmt->Where(["something="=> 2])->And($innerOr);
   * 
   * If you use this on Joins dynamic params won't work and you have to use new Name
   * ("hardcore_value")
   */
  function Where($condition)
  {
    $this->where = new Where($condition);
    return $this;
  }


  /**
   * 
   * equivalent to `..stmt.. LIMIT $number`
   */
  function Limit(int $number)
  {
    $this->limit = $number;
    return $this;
  }

  /**
   * Does a Join with the Where parameters you pass
   * 
   * @@ WARNING: Be careful, you cannot do procedural bind params with JOINs... At least not at the * @@ moment, so if you are a hardcore guy and wanna use manual strings, use Name class
   */
  function Join($toJoin, $where)
  {
    $where = new Where($where);
    $this->join = new SafeQuery("JOIN $toJoin ON {$where->cond}", $where->query);
    return $this;
  }

  /**
   * Finishes the query
   */
  function Do()
  {
    if ($this->stmt == "SELECT") return $this->DoSelect();
    return "";
  }

  /**
   * And. Can be passed a Where class as well.
   */
  function And(array $condition)
  {
    $this->where->And($condition);
    return $this;
  }

  /**
   * Or. Can be passed a Where class as well.
   */
  function Or(array $condition)
  {
    $this->where->Or($condition);
    return $this;
  }

  /**
   * Pass the SQL stmt to do an orderBy like `table.createdAt DESC`
   */
  function OrderBy(string $orderBy)
  {
    $this->orderBy = "ORDER BY $orderBy";
    return $this;
  }

  private function DoSelect()
  {
    $joinedTables = join(", ", $this->tables);
    // Order here matters, where goes later on
    $join = $this->getJoin();

    // Now get the where query and params
    $where = $this->getWhere();

    $limit = $this->getLimit();

    $str = "{$this->stmt} {$this->returns} FROM {$joinedTables} {$join} {$where} {$this->orderBy} $limit";
    if (QueryOptions::$DEBUG_QUERIES) {
      echo $str;
    }
    return $this->parent->processQuery($str, $this->params);
  }

  private function getJoin()
  {
    if ($this->join === null) {
      return '';
    }
    // Concatenate current procedural params with the join ones !!!
    $this->params = concat($this->params, $this->join->keys);
    return "{$this->join->stmt}";
  }

  private function getLimit()
  {
    if ($this->limit === null) {
      return '';
    }
    return "LIMIT {$this->limit}";
  }

  private function getWhere()
  {
    if ($this->where != null) {
      $this->params = concat($this->params, $this->where->query);
      return "WHERE {$this->where->cond}";
    }
    return "";
  }
}

class Model
{

  static public $sqli = null;

  /**
   * Initializes a Model API related to the name (or names if doing SELECT of various tables)
   *
   * Names can be an array or a string.
   */
  function __construct($name)
  {
    if (Model::$sqli == null)
      Model::$sqli = mysqli_connect("192.168.64.2", "gabi", "123456", "apiTest");
    $this->name = $name;
  }

  /**
   * Does a standard SELECT on the desired table
   * 
   * You can pass a String with comma divided values with the values that you want or
   * an array of strings.
   */
  function Select($returns = '*')
  {
    return new QueryOptionSelect(
      $this,
      "SELECT",
      gettype($this->name) === 'string' ? [$this->name] : $this->name,
      $returns
    );
  }

  /**
   * Creates an object in the desired Model
   * 
   * Take in mind that you should pass only a string to the model name when doing inserts.
   * 
   * This is equivalent to doing INSERT ... at the desired table name
   */
  function Create(array $object)
  {
    return new QueryOptionInsert($this, $this->name, $object);
  }

  /**
   * forEachRow maps a $row to the desired callback return value
   * 
   */
  static function forEachRow($res, callable $callback)
  {
    if (!$res) return false;
    if ($res === true) return true;
    for ($row = $res->fetch_assoc(); $row != null; $row = mysqli_fetch_assoc($res)) {
      $callback($row);
    }
    return true;
  }

  function processQuery(string $query, $params)
  {
    try {
      $rows = [];
      $stmt = Model::$sqli->prepare($query);

      $s = "";
      $arr = [];
      foreach ($params as $_ => $value) {
        $s .= $value[0];
        $arr[] = $value[1];
      }
      $params = array_merge([$s], $arr);
      call_user_func_array(array($stmt, 'bind_param'), refValues($params));
      // TODO : HANDLE ERROR AND RETURN A CLASS Result CONTAINING EVERYTHING
      $okExecute = $stmt->execute();
      if (!$okExecute) {
        return $okExecute;
      }
      $res = $stmt->get_result();
      if ($res === true || $res === false) {
        // $res returns false on successful insert.
        return $res || $okExecute;
      }
      $ok = Model::forEachRow($res, function ($row) use (&$rows) {
        array_push($rows, $row);
      });
      if (!$ok) return false;
      return $rows;
    } catch (Exception $_) {
      echo "EXCEPTION";
      return false;
    }
  }
}

function refValues($arr)
{
  if (strnatcmp(phpversion(), '5.3') >= 0) //Reference is required for PHP 5.3+
  {
    $refs = array();
    foreach ($arr as $key => $value)
      $refs[$key] = &$arr[$key];
    return $refs;
  }
  return $arr;
}
