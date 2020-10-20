<?php

class Github
{
  static function showPieceOfCode($link)
  {
    echo "
    <script src='http://gist-it.appspot.com/{$link}'></script>
    ";
  }
}
