<?php

class ItemView
{
  function __construct($item, $images)
  {
    $this->images = $images;
    $this->item = $item;
  }

  function render($removeButton = false)
  {
    $container = new HtmlElement('div', ['class' => 'container mt-4'], []);
    $title = new HtmlElement(
      'h1',
      ['class' => ' font-bold text-3xl text-gray-900'],
      $this->item['name']
    );
    $container->append($title);
    $flex = new HtmlElement(
      'div',
      ['class' => 'my-5 grid grid-cols-1 md:grid-cols-4 gap-4'],
      array_map(function ($el) {
        return new HtmlElement(
          'img',
          ['class' => 'w-full h-64 object-cover', 'src' => "/public/ecommerce/files/{$el['id']}.png"],
          []
        );
      }, $this->images)
    );
    $container->append($flex);
    $title = new HtmlElement(
      'h1',
      ['class' => ' font-bold text-3xl text-gray-900'],
      "Information"
    );
    $container->append($title);
    // echo "<pre>", var_dump($this->item), "</pre>";
    foreach ($this->item as $key => $value) {
      if (strpos($key, "id") !== false)
        continue;
      $container->append(
        new HtmlElement(
          'h1',
          ['class' => 'font-bold text-xl text-gray-900 mt-5'],
          [str_replace("_", " ", ucfirst($key))]
        )
      );
      $value = ucfirst($value);
      if ($key === "price") {
        $value = "$value $";
      }

      $container->append(
        new HtmlElement('h1', ['class' => 'text-xl text-gray-900 m-3'], "$value")
      );
    }
    $form = new HtmlElement(
      'form',
      [
        'enctype' => 'multipart/form-data',
        "action" => "/shop/cart/{$this->item['id']}",
        "method" => "POST",
        'class' => 'mt-10'
      ]
    );
    $form->append(new HtmlElement('input', [
      'type' => 'number',
      'name' => 'quantity',
      'value' => '1',
      'class' => 'm-3 p-3 bg-blue-200 text-gray-600'
    ]));
    $form->append(new HtmlElement('input', [
      'type' => 'submit',
      'value' => 'Add Item To Cart',
      'class' => 'm-3 p-3 bg-blue-500 text-gray-200 hover:text-gray-700 cursor-pointer hover:bg-blue-200'
    ]));
    $container->append($form);
    if ($removeButton) {
      $form = new HtmlElement(
        'form',
        [
          'enctype' => 'multipart/form-data',
          "action" => "/shop/cart/remove/{$this->item['id']}",
          "method" => "POST",
          'class' => ''
        ]
      );
      $form->append(new HtmlElement('input', [
        'type' => 'submit',
        'value' => 'Remove All Items From Cart',
        'class' => 'm-3 p-3 bg-red-500 text-gray-200 hover:text-gray-700 cursor-pointer hover:bg-red-200'
      ]));
      $container->append($form);
    }

    return $container;
  }
}
