# SpeedrunRecordsParser
## About
Parses the JSON records of [SpeedRunIGT](https://github.com/RedLime/SpeedRunIGT) and displays them as a decent website.

Uses PHP and [water.css](https://watercss.kognise.dev/) in dark mode.

Currently shows all runs with an `enter_end` time. Runs get sorted by completion and achieved time, meaning slower completed runs show up higher than faster uncompleted runs.

![screenshot](https://raw.githubusercontent.com/reclaimingmytime/SpeedrunRecordsParser/main/screenshot.png)

## Installation

To use, copy the file `config.example.php` to `config.php` and change the path. Uses `/home/username/speedrunigt/` by default. If you are unsure what this directory is, you can find it in Minecraft under `Options --> SpeedRunIGT Settings (icon next to FOV slider) --> Records --> Open Records Directory`
