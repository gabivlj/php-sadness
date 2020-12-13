<?php
class Form
{
  function __construct($keys, $path)
  {
    $this->keys = $keys;
    $this->path = $path;
  }

  function render($buttonTitle = 'Create Item')
  {
    $form = new HtmlElement(
      'form',
      [
        'enctype' => 'multipart/form-data',
        "action" => "{$this->path}",
        "method" => "POST",
        'class' => 'mt-10'
      ]
    );
    foreach ($this->keys as $input) {
      if ($input == 'id') continue;
      $form->append(new HtmlElement(
        'input',
        ['type' => 'text', 'name' => $input, 'placeholder' => $input, 'class' => 'input shadow appearance-none border rounded py-2 px-3 m-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mt-2'],
        []
      ));
    }
    $form->append(new HtmlElement('input', ['type' => 'file', 'name' => 'file']));
    $form->append(new HtmlElement('input', ['type' => 'submit', 'value' => $buttonTitle, 'class' => 'm-3 p-3']));
    return $form;
  }
}
