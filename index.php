<?php
include("config.php");

function convertTime($ms) {
  return floor($ms/60000) . ':' . str_pad(floor(($ms%60000)/1000), 2, '0', STR_PAD_LEFT) . '.' . str_pad(floor($ms%1000), 3, '0', STR_PAD_LEFT);
}

function convertDate($ms) {
  return date("Y-m-d H:i", $ms / 1000);
}

function hasEnterEnd($timelines) {
  return in_array("enter_end", array_column($timelines, "name"));
}

function parseJSON($igtPath) {
  $stats = [];

  $stats["playTime"] = 0;
  $stats["bestTime"] = 0;
  $stats["worstTime"] = 0;

  $stats["completedRuns"] = 0;
  $stats["enterEndRuns"] = 0;
  $stats["totalRuns"] = 0;

  foreach(glob($igtPath . "/records/*.json") as $filename) {
    $file = file_get_contents($filename);
    $record = json_decode($file, true);

    // Add counter stats
    $stats["totalRuns"]++;

    if($record["is_completed"]) {
      $stats["completedRuns"]++;
    }
    if(!$record["is_completed"] && hasEnterEnd($record["timelines"])) {
      $stats["enterEndRuns"]++;
    }

    // Add timer stats
    $stats["playTime"] += $record["final_rta"];
    if($record["is_completed"] && (!$stats["bestTime"] || $record["final_igt"] < $stats["bestTime"])) {
      $stats["bestTime"] = $record["final_igt"];
    }
    if($record["is_completed"] && $record["final_igt"] > $stats["worstTime"]) {
      $stats["worstTime"] = $record["final_igt"];
    }

    // Add run record
    if($record["is_completed"] || hasEnterEnd($record["timelines"])) {
      $stats["runs"][] = $record;
    }
  }
  return $stats;
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
    $stats = parseJSON($igtPath);
    sortRuns($stats["runs"]);
    ?>
    <p>Total Playtime: <?= number_format($stats["playTime"] / 3600000, 2) ?> hours | Completed Runs: <?= $stats["completedRuns"] ?> | Enter End Runs: <?= $stats["enterEndRuns"] ?> | Total Runs (including resets): <?= $stats["totalRuns"] ?><br>
    IGT Fastest Run: <?= convertTime($stats["bestTime"]) ?> | IGT Slowest Run: <?= convertTime($stats["worstTime"]) ?></p><hr>
    
    <?php
    foreach($stats["runs"] as $run) {
     ?>
      <div style="margin: 1rem 0;">
        <table>
        <caption style="margin-bottom: 0.5rem;">IGT: <strong><?= convertTime($run["final_igt"]) ?></strong> | <?= $run["is_completed"] ? "Completed" : "<strong>Not</strong> Completed" ?> | Date: <?= convertDate($run["date"]); ?></caption>
          <tr>
            <th>Info</th>
            <th>IGT</th>
            <th>RTA</th>
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
