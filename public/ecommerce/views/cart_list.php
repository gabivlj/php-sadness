<?php

class CartList
{
  static $svgData = "M527.9 32H48.1C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48.1 48h479.8c26.6 0 48.1-21.5 48.1-48V80c0-26.5-21.5-48-48.1-48zM54.1 80h467.8c3.3 0 6 2.7 6 6v42H48.1V86c0-3.3 2.7-6 6-6zm467.8 352H54.1c-3.3 0-6-2.7-6-6V256h479.8v170c0 3.3-2.7 6-6 6zM192 332v40c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12zm192 0v40c0 6.6-5.4 12-12 12H236c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h136c6.6 0 12 5.4 12 12z";

  function __construct($products)
  {
    $this->products = $products;
  }

  function th($title, $class)
  {
    return new HtmlElement('th', ['class' => $class], $title);
  }

  function total_and_checkout()
  {
    $totalPrice = array_reduce($this->products, function ($prev, $now) {
      return $prev + ($now['price'] * $now['quantity']);
    }, 0);

    $total = new HtmlElement('div', ['class' => 'flex justify-between pt-4 border-b'], [
      new HtmlElement(
        'div',
        ['class' => 'lg:px-4 lg:py-2 m-2 text-lg lg:text-xl font-bold text-center text-gray-800'],
        'Total'
      ), new HtmlElement(
        'div',
        ['class' => 'lg:px-4 lg:py-2 m-2 lg:text-lg font-bold text-center text-gray-900'],
        "$totalPrice $"
      ),
    ]);
    $checkoutButton = new HtmlElement(
      'form',
      ['action' => "/shop/cart/fulfill", 'method' => 'POST'],
      [
        new HtmlElement(
          'button',
          [
            'class' =>
            "flex justify-center w-full px-10 py-3 mt-6 font-medium text-white uppercase bg-gray-800 
        rounded-full shadow item-center hover:bg-gray-700 focus:shadow-outline focus:outline-none",
            'type' => 'submit'
          ],
          [
            new HtmlElement('svg', ['class' => 'w-8', 'viewBox' => "0 0 576 512"], [
              new HtmlElement('path', ['d' => CartList::$svgData, 'fill' => 'currentColor'])
            ]),
            new HtmlElement('span', ['class' => 'ml-2 mt-5px'], 'Proceed to checkout')
          ]
        )
      ]
    );
    $root = new HtmlElement(
      'div',
      ['class' => 'lg:px-2 lg:w-1/2'],
      [new HtmlElement('div', ['class' => 'p-4'], [$total, $checkoutButton])]
    );
    return $root;
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
    $root = new HtmlElement('div', ['class' => 'xl:flex justify-center my-6'], [$secondRoot]);
    $root->append($this->total_and_checkout());
    return $root;
  }
}
