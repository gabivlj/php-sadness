<?php

class DeleteButton
{
  function __construct()
  {
  }

  function render($path)
  {
    $form = new HtmlElement(
      'form',
      [
        'enctype' => 'multipart/form-data',
        "action" => "{$path}",
        "method" => "POST",
        'class' => 'mt-10'
      ]
    );
    $form->append(new HtmlElement('input', [
      'type' => 'submit',
      'value' => 'Delete Item',
      'class' => 'm-3 p-3 bg-red-500 text-gray-200 cursor-pointer'
    ]));
    return $form;
  }
}
