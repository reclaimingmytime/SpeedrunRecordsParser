<?php
include("config.php");

function convertTime($ms) {
  return floor($ms/60000) . ':' . str_pad(floor(($ms%60000)/1000), 2, '0', STR_PAD_LEFT) . '.' . str_pad(floor($ms%1000), 3, '0', STR_PAD_LEFT);
}

function convertDate($ms) {
  return date("Y-m-d H:i", $ms / 1000);
}

function millisecondsToHours($time) {
  return number_format($time / 3600000, 2);
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

  $files = glob($igtPath . "/records/*.json");
  if(!$files) {
    return [];
  }

  foreach($files as $filename) {
    $file = file_get_contents($filename);
    $record = json_decode($file, true);

    // Add counter stats
    $stats["totalRuns"]++;

    if($record["is_completed"] && !isset($record["is_cheat_allowed"])) {
      $stats["completedRuns"]++;
    }
    if(!$record["is_completed"] && hasEnterEnd($record["timelines"]) && !isset($record["is_cheat_allowed"])) {
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

$stats = parseJSON($igtPath);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SpeedrunIGT Records</title>
    <link rel="stylesheet" href="water_dark.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⛏</text></svg>">
  </head>
  <body>
    <h1>SpeedrunIGT Records</h1>

    <?php
    if(!isset($stats["runs"])) {
      echo "<p>No completed runs found.</p></body></html>";
      exit;
    }

    if(count($stats["runs"]) > 1) {
      sortRuns($stats["runs"]);
    }
    ?>
    <table style="margin: 1rem 0;">
        <tr>
            <td>Total Playtime</td>
            <td><?= millisecondsToHours($stats["playTime"]) ?> hours</td>
        </tr>
        <tr>
            <td>Completed Runs</td>
            <td><?= $stats["completedRuns"] ?></td>
        </tr>
        <tr>
            <td>Enter End Runs</td>
            <td><?= $stats["enterEndRuns"] ?></td>
        </tr>
        <tr>
            <td>Total Runs (including resets)</td>
            <td><?= $stats["totalRuns"] ?></td>
        </tr>
        <tr>
            <td>IGT Fastest Run</td>
            <td><?= convertTime($stats["bestTime"]) ?></td>
        </tr>
        <tr>
            <td>IGT Slowest Run</td>
            <td><?= convertTime($stats["worstTime"]) ?></td>
        </tr>
    </table>
    <hr>
    
    <?php
    foreach($stats["runs"] as $run) {
     ?>
      <div style="margin: 1rem 0;">
        <?php if(isset($run["is_cheat_allowed"])) echo "<p style='font-weight: bold;'>Test run (activated cheats)</p>"; ?>

        <table>
        <caption style="margin-bottom: 0.5rem;">IGT: <strong><?= convertTime($run["final_igt"]) ?></strong> | <?= $run["is_completed"] ? "Completed" : "<strong>Not</strong> Completed" ?> | Date: <?= convertDate($run["date"]); ?><br><?= $run["mc_version"] ?> | <?= $run["run_type"] ?></caption>
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
