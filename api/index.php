<?php
require 'vendor/autoload.php';
require '../Config.php';


$app = new \Slim\Slim();


$app->get('/test', function () use ($app) {
    
	echo $_SERVER['REMOTE_ADDR']."<br/>";
	
	$req = $app->request();
	echo $req->getUrl().$req->getPath()."<br/>";

	//echo json_encode(\Feader\PieService::LoadFeed("http://feeds.feedburner.com/GeekPolitics"));
	echo \Feader\PieService::LoadFeed("http://feeds.feedburner.com/GeekPolitics");

});

$app->get('/checkUser', function () use ($app) {
    $req = $app->request();
	$vendorId = $req->params('vendorid');
	error_log("vendorId: ".$vendorId, 0);
	
	$userObj = \Feader\Model\User::findUser($vendorId);
	echo json_encode($userObj);

});


$app->get('/feeds', function () use ($app) {
	$req = $app->request();
	$userId = $req->params('userId');
    $feeds = \Feader\Model\Feed::getFeeds($userId);
	echo json_encode($feeds);
});

$app->get('/feed/:id', function ($id) {
    $feed = \Feader\Model\Feed::getFeed($id);
	echo json_encode($feed);

});

$app->get('/feed/:id/pubdate/:pubDate', function ($id,$pubDate) use ($app) {
	$feed = \Feader\Model\Feed::getFeed($id);
	$feed->UpdateFeedLastEntry($pubDate);
	echo json_encode(array('result' => 'success'));
});

$app->put('/feed/:id', function ($id) use ($app) {
	$feed = \Feader\Model\Feed::getFeed($id);
	
	$req = $app->request();
	$feedObj = json_decode($req->params('feed'));
	//var_dump($feedObj);
	if ($feed->UpdateFeed($feedObj))
		echo json_encode(array('result' => 'success'));	
});

$app->post('/feed', function () use ($CFG, $app) {
	
	$req = $app->request();
	$body = $req->getBody();
	parse_str($body);
	/*$userId = $req->params('userId');
	$feedUrl = $req->params('feedUrl') || "";
	$siteUrl = $req->params('siteUrl') || "";
	$name = $req->post('name') || "";
	$lastUpdate = $req->params('lastUpdate');*/
	
	$feedSvc = new \Feader\FeaderDataService($CFG);
	
	$id = $feedSvc->AddFeed($userId, $feedUrl, $siteUrl,$name,$lastUpdate);
	$feed = \Feader\Model\Feed::getFeed($id);
	echo json_encode($feed);
});

$app->post('/entry', function () use ($CFG, $app) {
	
	$req = $app->request();
	
	$feedId = $req->params('feedId');
	$pubDate = $req->params('pubDate');
	$state = $req->params('state');
	
	$feedSvc = new \Feader\FeaderDataService($CFG);
	
	$feedSvc->addFeedEntry($feedId, $pubDate, $state);
	echo json_encode(array('result' => 'success'));
});



//addFeed
//deleteFeed


$app->run();