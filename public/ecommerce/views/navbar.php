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
    ['link' => '/home', 'text' => 'Home'],
    ['link' => '/sign_up/login', 'text' => 'Login'],
    ['link' => '/sign_up/register', 'text' => 'Register'],
    ['link' => '/products', 'text' => 'All Products'],
    ['link' => '/products?type=cds', 'text' => 'CDs/Vynils'],
    ['link' => '/products?type=headset', 'text' => 'Headsets'],
    ['link' => '/products?type=player', 'text' => 'Players'],
  ]);
}

function navBarVerified($username)
{
  return nav([
    ['link' => '/home', 'text' => 'Home'],
    ['link' => '/products', 'text' => 'All Products'],
    ['link' => '/products?type=cds', 'text' => 'CDs/Vynils'],
    ['link' => '/products?type=headsets', 'text' => 'Headsets'],
    ['link' => '/products?type=players', 'text' => 'Players'],
    ['link' => '/sign_up/logout', 'text' => 'Logout'],
    ['link' => "/user/$username", 'text' => $username],
  ]);
}