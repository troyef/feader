<?php

namespace Feader;

class GFeedService {
	
	private static $feedSvcUrl = "https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=";
	
	public static function LoadFeed($url,$num = 10){
		
		$app = \Slim\Slim::getInstance();
		$req = $app->request();
		
		$svcUrl = self::$feedSvcUrl . urlencode($url). "&userip=" . $_SERVER['REMOTE_ADDR'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $svcUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $req->getUrl().$req->getPath());
		$body = curl_exec($ch);
		curl_close($ch);

		return json_decode($body);
		
		/*
		$file = fopen($svcUrl, "r") 
			or exit("Unable to load feed!");
		$jsonStr = "";
		while(!feof($file))
		  {
		      $jsonStr .= fgets($file);
		  }
		fclose($file);

		return json_decode($jsonStr);
		*/
	}
	
	
	
	
	
	
	
}