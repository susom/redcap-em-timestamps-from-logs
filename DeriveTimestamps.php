<?php

use \Plugin as Plugin;
/** @var \Stanford\TimestampsfromLogs\TimestampsfromLogs $module */


echo "------- Scrape logs for survey timestamps -------";

Plugin::log("------- Starting scraping logs for timestamps -------");
Plugin::log($project_id, "DEBUG","PID");

$module->getTimestampsFromLogs($project_id);