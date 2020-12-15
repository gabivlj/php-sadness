<?php

class CartList
{
  function __construct($products)
  {
    $this->products = $products;
  }

  function th($title, $class)
  {
    return new HtmlElement('th', ['class' => $class], $title);
  }

  /*
  Example model
 {
    ["item_id"]=>
    string(36) "0c8ef4bc-8697-4d36-80ee-8e72b4f432d7"
    ["cart_item_id"]=>
    int(14)
    ["album_name" | "headset_name" ...]=>
    string(14) "Gabis el disco"
    ["image_id"]=>
    string(36) "41f71ae7-470d-4fc2-8f4c-c8bcbd4c079e"
    ["quantity"]=>
    int(2)
    ["type"]=>
    string(6) "albums"
  }
  */
  function get_item_view($item)
  {
    $item['name'] = $item["{$item['type']}_name"];
    $tds = [];

    $tds[] =  new HtmlElement('td', [], [new HtmlElement(
      'a',
      ['href' => "/shop/{$item['type']}/{$item['item_id']}"],
      [new HtmlElement(
        'img',
        [
          'class' => 'w-20 rounded',
          'src' => "/public/ecommerce/files/{$item['image_id']}.png"
        ],
        []
      )]
    )]);
    $tds[] = new HtmlElement('td', [], [
      new HtmlElement('a', ['href' => "/shop/{$item['type']}/{$item['item_id']}"], [
        new HtmlElement('p', ['class' => 'mb-2 md:ml-4'], "{$item['name']}"),
        new HtmlElement('small', ['class' => 'text-gray-700 mb-2 md:ml-4 m-1'], "Type: ({$item['type']})"),
        new HtmlElement(
          'form',
          ['action' => "/shop/cart/remove/{$item['item_id']}", 'method' => 'POST'],
          [new HtmlElement(
            'button',
            ['type' => 'submit', 'class' => "text-gray-700 md:ml-4 hover:text-gray-500"],
            [new HtmlElement('small', [], '(Remove Item)')]
          )]
        )
      ])
    ]);
    $tds[] = new HtmlElement('td', ['class' => 'justify-center md:justify-end md:flex mt-6'], [
      new HtmlElement('a', ['href' => "/shop/{$item['type']}/{$item['item_id']}"], [
        new HtmlElement(
          'div',
          ['class' => 'w-full font-semibold text-gray-700 outline-none focus:outline-none hover:text-black focus:text-black'],
          "{$item['quantity']}"
        )
      ])
    ]);
    $priceFloat = doubleval($item['price']);
    $tds[] = new HtmlElement(
      'td',
      ['class' => 'hidden text-right md:table-cell'],
      [new HtmlElement(
        'span',
        ['class' => "text-sm lg:text-base font-medium"],
        "$priceFloat $"
      )]
    );
    $totalPrice = $item['price'] * $item['quantity'];
    $tds[] = new HtmlElement(
      'td',
      ['class' => 'hidden text-right md:table-cell'],
      [new HtmlElement(
        'span',
        ['class' => "text-sm lg:text-base font-medium"],
        "$totalPrice $"
      )]
    );
    $trowBody = new HtmlElement('tr', [], $tds);
    return $trowBody;
  }

  function render()
  {
    $tbody = new HtmlElement('tbody', [], []);
    foreach ($this->products as $item) {
      $tbody->append($this->get_item_view($item));
    }
    $trow = new HtmlElement('tr', ['class' => 'h-12 uppercase'], []);
    $trow->append($this->th("", 'hidden md:table-cell'));
    $trow->append($this->th("Product", 'text-left'));
    $trow->append($this->th("Quantity", 'lg:text-right text-left pl-5 lg:pl-0'));
    $trow->append($this->th("Unit Price", 'hidden text-right md:table-cell'));
    $trow->append($this->th("Total Price", 'text-right'));
    $thead = new HtmlElement('thead', [], [$trow]);
    // LOTS OF STYLES
    $table = new HtmlElement('table', ['class' => 'w-full text-sm lg:text-base'], [$thead, $tbody]);
    $secondRoot = new HtmlElement('div', ['class' => 'flex flex-col w-full p-8 text-gray-800 bg-white shadow-lg pin-r pin-y md:w-4/5 lg:w-4/5'], []);
    $secondRoot->append(new HtmlElement('div', ['class' => 'flex-1'], [$table]));
    $root = new HtmlElement('div', ['class' => 'flex justify-center my-6'], [$secondRoot]);
    return $root;
  }
}
