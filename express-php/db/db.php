
<?php

function processValueType($value)
{
  $type = gettype($value);
  if ($type == "string") return "'$value'";
  if ($type == "integer" || $type == "float") return $value;
  throw new Exception("ERROR: Unknown type :(");
}

class Where
{

  function keyValue($keyValue)
  {
    foreach ($keyValue as $key => $value) {
      $value = processValueType($value);
      return "$key$value";
    }
  }

  function __construct($start)
  {

    $this->cond = $this->keyValue($start);
  }

  function And($more)
  {
    $this->cond .= "&&" . $this->keyValue($more);
  }

  function Or($more)
  {
    $this->cond .= "||" . $this->keyValue($more);
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

  function __construct($parent, $model, $object)
  {
    $this->parent = $parent;
    $this->model = $model;
    $this->object = $object;
  }

  function Do()
  {
    $valKeys = reduce($this->object, function ($prev, $key, $value) {
      if (strlen($prev) == 1) return "$prev`$key`";
      return "$prev, `$key`";
    }, '(');
    $valValues = reduce($this->object, function ($prev, $key, $value) {
      $value = processValueType($value);
      if (strlen($prev) == 1) return "$prev$value";;
      return "$prev,$value";
    }, '(');
    $valKeys .= ')';
    $valValues .= ')';
    $str = "INSERT INTO {$this->model} $valKeys VALUES $valValues";
    echo $str;

    return $this->parent->processQuery($str);;
  }

  function LastId()
  {
    return Model::$sqli->insert_id;
  }
}

class QueryOptionSelect
{
  public  $stmt;
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
  }

  function Where($condition)
  {
    $this->where = new Where($condition);
    return $this;
  }

  function Limit($number)
  {
    $this->limit = $number;
    return $this;
  }

  function Join()
  {
    return $this;
  }

  function Do()
  {
    if ($this->stmt == "SELECT") return $this->DoSelect();
    return "";
  }

  function And($condition)
  {
    $this->where->And($condition);
    return $this;
  }

  function Or($condition)
  {
    $this->where->Or($condition);
    return $this;
  }

  function OrderBy($orderBy)
  {
    $this->orderBy = "ORDER BY $orderBy";
    return $this;
  }

  private function DoSelect()
  {
    $joinedTables = join(", ", $this->tables);
    $where = $this->getWhere();
    $limit = $this->getLimit();
    $str = "{$this->stmt} {$this->returns} FROM {$joinedTables} {$where} {$this->orderBy} $limit";
    echo $str;
    return $this->parent->processQuery($str);
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
      return "WHERE {$this->where->cond}";
    }
    return "";
  }
}

class Model
{

  static public $sqli = null;

  function __construct($name)
  {
    if (Model::$sqli == null)
      Model::$sqli = mysqli_connect("192.168.64.2", "gabi", "123456", "apiTest");
    $this->name = $name;
  }

  function Select($returns = '*')
  {
    return new QueryOptionSelect($this, "SELECT", [$this->name], $returns);
  }

  function Create($object)
  {
    return new QueryOptionInsert($this, $this->name, $object);
  }

  static function forEachRow($res, $callback)
  {
    if (!$res) return false;
    if ($res === true) return true;
    for ($row = $res->fetch_assoc(); $row != null; $row = mysqli_fetch_assoc($res)) {
      $callback($row);
    }
    return true;
  }

  function processQuery(string $query)
  {
    $rows = [];
    $res = Model::$sqli->query($query);
    $ok = Model::forEachRow($res, function ($row) use (&$rows) {
      array_push($rows, $row);
    });
    if (!$ok) return false;
    return $rows;
  }
}
