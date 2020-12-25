<?php

function navButton($link, $text)
{
  return new HtmlElement(
    "a",
    ["class" => "inline-block", "href" => $link],
    [new HtmlElement(
      "button",
      ["class" => "text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-teal-500 hover:bg-white mt-4 lg:mt-0 ml-3 cursor-pointer "],
      [$text]
    )]
  );
}


function button($units)
{
  $buttons = [];
  foreach ($units as $unit) {
    array_push($buttons, navButton($unit['link'], $unit['text']));
  }
  return $buttons;
}

function nav($links)
{
  return new HtmlElement(
    "nav",
    ["class" => "flex items-center justify-between flex-wrap p-6 bg-teal-900"],
    [new HtmlElement(
      "div",
      ["class" => "w-full block flex-grow lg:flex lg:items-center lg:w-auto"],
      [new HtmlElement(
        "div",
        ["class" => "text-sm lg:flex-grow"],
        button($links)
      )]
    )]
  );
}

function navBarUnverified()
{
  return nav([
    ['link' => '/search', 'text' => 'Home'],
    ['link' => '/sign_up/login', 'text' => 'Login'],
    ['link' => '/sign_up/register', 'text' => 'Register'],
  ]);
}



function navBarVerified($username, $admin)
{
  $links = [
    ['link' => '/search', 'text' => 'Home'],
    ['link' => '/search/albums', 'text' => 'CDs/Vynils'],
    ['link' => '/search/headset', 'text' => 'Headsets'],
    ['link' => '/search/players', 'text' => 'Players'],
    ['link' => '/shop/cart', 'text' => 'Shopcart'],
    ['link' => '/sign_up/logout', 'text' => 'Logout'],
    ['link' => "/user/$username", 'text' => $username],
  ];
  require_once './public/ecommerce/controllers/items.php';
  if ($admin) {
    $links[] = ['link' => '/orders/admin', 'text' => 'OrdersDash'];
    $links[] = ['link' => '/items/admin/albums', 'text' => 'Albums Dash'];
    $links[] = ['link' => '/items/admin/headset', 'text' => 'Headset Dash'];
    $links[] = ['link' => '/items/admin/players', 'text' => 'Players Dash'];
    $links[] = ['link' => '/items/admin/users', 'text' => 'Users Dashboard'];
  }
  return nav($links);
}
