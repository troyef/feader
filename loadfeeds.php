<?php

require_once "lib/config.php";
require_once "lib/feaderDataService.php";

$feedSvc = new FeaderDataService($CFG);

//$jsonRows = $feedSvc->GetFeedList();

//var_dump($jsonRows);
$path = "subscriptions.xml";
$feedSvc->loadFromFile($path);




