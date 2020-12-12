<?php

require_once './public/ecommerce/views/navbar.php';

class Verification
{
  static function view($email, $_)
  {
    $nav = navBarUnverified();
    $body = HtmlElement::Body();
    $body->append($nav);
    $body->append(
      new HtmlElement(
        'h1',
        ['class' => 'm-4 text-xl', 'id' => 'message'],
        "Hold on one second $email while we verify your account... You will be redirected when we finish! If there is an error we will notify you."
      )
    );
    HtmlRoot::append(HtmlElement::Javascript('/public/ecommerce/js/verification_req.js'));
    HtmlRoot::append($body);
  }
}
