<?php


$file = fopen("https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=" . urlencode("http://feeds.feedburner.com/GeekPolitics"), "r") or exit("Unable to open file!");
$jsonStr = "";
while(!feof($file))
  {
      $jsonStr .= fgets($file);
  }
fclose($file);

$respObj = json_decode($jsonStr);

echo $jsonStr;


