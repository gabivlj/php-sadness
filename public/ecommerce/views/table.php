<?php

class Table
{
  function __construct($table, $type)
  {
    $this->table = $table;
    $this->type = $type;
  }


  function render()
  {
    $root = new HtmlElement('div', ['class' => 'mt-3 overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative'], []);
    if (count($this->table) == 0) {
      return $root;
    }
    $table = new HtmlElement('table', ['class' => 'border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative'], []);
    $keys = array_keys($this->table[0]);
    $elementsRow = array_map(function ($el) {
      return new HtmlElement('th', ['class' => 'py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100'], $el);
    }, $keys);
    $row = new HtmlElement('tr', [], $elementsRow);
    $thead = new HtmlElement('thead', [], [$row]);
    $table->append($thead);
    $root->append($table);
    $t = $this;
    $idx = 0;
    $trows = array_map(function ($el) use ($t, &$idx) {
      $idx += 1;
      return new HtmlElement('tr', [], array_map(function ($el) use ($t, $idx) {
        return new HtmlElement('td', ['class' => 'border-dashed border-t border-gray-200  '], [
          new HtmlElement(
            isset($t->table[$idx - 1]['id']) && $t->table[$idx - 1]['id'] === $el ? 'a' : 'span',
            [
              'href' => "/items/admin/{$t->type}/$el",
              'class' => 'text-gray-700 px-6 py-3 flex items-center'
            ],
            "$el"
          )
        ]);
      }, $el));
    }, $this->table);
    $body = new HtmlElement('tbody', [], $trows);
    $table->append($body);
    return $root;
  }
}
