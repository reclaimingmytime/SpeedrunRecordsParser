<?php
include("config.php");

function convertTime($ms) {
  return floor($ms/60000) . ':' . str_pad(floor(($ms%60000)/1000), 2, '0', STR_PAD_LEFT) . '.' . str_pad(floor($ms%1000), 3, '0', STR_PAD_LEFT);
}

function parseJSON($igtPath) {
  $runs = [];
  foreach(glob($igtPath . "/records/*.json") as $filename) {
    $file = file_get_contents($filename);
    $array = json_decode($file, true);

    if($array["is_completed"] || in_array("enter_end", array_column($array["timelines"], "name"))) {
      $runs[] = $array;
    }
  }
  return $runs;
}

function sortRuns(&$runs) {
  $completedRunSort = array_column($runs, 'is_completed');
  $igtSort = array_column($runs, 'final_igt'); 
  array_multisort($completedRunSort, SORT_DESC, $igtSort, SORT_ASC, $runs);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SpeedrunIGT Records</title>
    <link rel="stylesheet" href="water_dark.css">
  </head>
  <body>
    <h1>SpeedrunIGT Records</h1>

    <?php
    $runs = parseJSON($igtPath);
    sortRuns($runs);
    
    foreach($runs as $run) {
     ?>
     <div style="margin: 1rem 0;">
        <table>
          <tr>
            <th>Info</th>
            <th>IGT</th>
            <th>RTA</th>
          </tr>
          <tr>
            <td>is_completed</td>
            <td><?= $run["is_completed"] ? "true" : "false" ?></td>
            <td>-</td>
          </tr>
          <tr>
            <td><strong>final_igt</strong></td>
            <td><strong><?= convertTime($run["final_igt"]) ?></strong></td>
            <td>-</td>
          </tr>
          <?php
            foreach($run["timelines"] as $timeline) { ?>
              <tr>
                <td><?= $timeline["name"]; ?></td>
                <td><?= convertTime($timeline["igt"]); ?></td>
                <td><?= convertTime($timeline["rta"]); ?></td>
              </tr>
            <?php }
          ?>
        </table>
        </div>
        <hr>
      <?php } ?>
  </body>
</html>
