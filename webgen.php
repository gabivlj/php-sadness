<!DOCTYPE html>
<html>

<head>
  <title>Image Gallery</title>
  <style type="text/css">
    .rh {
      text-align: center;
      background: grey;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php




  function random_color_part()
  {
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
  }

  function random_color()
  {
    return random_color_part() . random_color_part() . random_color_part();
  }


  function buttons($path, $col)
  {
    $nextCol = $col + 1;
    $prevCol = $col - 1;
    if ($prevCol != 0) {
      echo " <button onclick='window.location.href=`?path={$path}&col={$prevCol}`'>Remove COL </button>";
    }
    if ($nextCol != 8)
      echo "<button onclick='window.location.href=`?path={$path}&col={$nextCol}`'>Add COL </button>
    ";
  }

  $path = "./result";
  if (isset($_GET['path'])) {
    $path = $_GET['path'];
  }

  function table($path, $cols)
  {
    buttons($path, $cols);
    // $cols++;
    echo '<table align="center">';
    echo '<tr>';
    for ($i = 0; $i <= $cols; $i++) {
      echo "<th class='rh'>$i </th>";
    }
    echo '</tr>';

    $arr = array_merge(array_diff(scandir($path), ['..', '.']));
    $i = 0;
    $col = 0;
    foreach ($arr as $dir) {
      $spawnCol = $i % $cols == 0;
      if ($spawnCol) {
        if ($col > 0) {
          echo "</tr>";
        }
        $col++;
        echo "<tr>";
        echo "<td class='rh'>{$col}</td>";
      }
      $p = "$path/$dir";

      if (is_dir($p)) {
        echo "        
        <td>
        <a href='?path=$path/$dir&col=$cols'>
          <img src='./folder.png'></img>
          <p style='text-align:center'>{$dir}</p>
        </a>
      </td>";
      } else {
        $color = random_color();
        echo "        
        <td style='background-color:#{$color}'>
          <img src='{$p}'></img>
          <p>{$dir}</p>
        </td>";
      }
      $i++;
    }

    echo "</tr>";
    echo '</table>';
  }
  $col = 4;
  if (isset($_GET['col'])) {

    $col = intval($_GET['col']);
  }

  table($path, $col);

  ?>

</body>

<!-- <button onclick='window.location.href="?path=result2&col=2"'>Remove COL </button>
<button onclick='window.location.href="?path=result2&col=4"'>Add COL </button>

<tr>
  <td class='rh'>1</td>
  <td>
    <a href='?path=result2/0&col=3'>
      <img src='src/folder.png'></img>
      <p style="text-align:center">0</p>
    </a>
  </td>
  <td>
    <a href='?path=result2/1&col=3'>
      <img src='src/folder.png'></img>
      <p style="text-align:center">1</p>
    </a>
  </td>
  <td style="background-color:#77de2f">
    <img src='result2/10.png'></img>
    <p>10.png</p>
  </td>
</tr>
<tr>
  <td class='rh'>2</td>
  <td style="background-color:#d31aee">
    <img src='result2/2.png'></img>
    <p>2.png</p>
  </td>
  <td>
    <a href='?path=result2/3&col=3'>
      <img src='src/folder.png'></img>
      <p style="text-align:center">3</p>
    </a>
  </td>
  <td style="background-color:#4cf0a3">
    <img src='result2/4.png'></img>
    <p>4.png</p>
  </td>
</tr>
<tr>
  <td class='rh'>3</td>
  <td>
    <a href='?path=result2/5&col=3'>
      <img src='src/folder.png'></img>
      <p style="text-align:center">5</p>
    </a>
  </td>
  <td style="background-color:#e27fb4">
    <img src='result2/6.png'></img>
    <p>6.png</p>
  </td>
  <td style="background-color:#6eeac4">
    <img src='result2/7.png'></img>
    <p>7.png</p>
  </td>
</tr>
<tr>
  <td class='rh'>4</td>
  <td style="background-color:#598f8e">
    <img src='result2/8.png'></img>
    <p>8.png</p>
  </td>
  <td style="background-color:#dd8d28">
    <img src='result2/9.png'></img>
    <p>9.png</p>
  </td>
  </table> -->