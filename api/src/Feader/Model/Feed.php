<?php

namespace Feader\Model;

class Feed{
	
	public $id;
	public $url;
	public $siteUrl;
	public $name;
	public $lastEntry;
	
	public $saved_entries = array();
	public $read_entries = array();
	
	
	public function __construct($feedObj = null) {
		$this->lastEntry = (time() - (30 * 60 * 60 * 24)) * 1000; //default to 30 days ago, store in ms
								//js new Date().getTime()
		if (null != $feedObj){
			$this->id = $feedObj['id'];
			$this->url = $feedObj['url'];
			$this->siteUrl = $feedObj['siteUrl'];
			$this->name = $feedObj['name'];
			$this->lastEntry = ($feedObj['lastEntry'] != null) ? $feedObj['lastEntry'] : $this->lastEntry;
			
			$feedSvc = new \Feader\FeaderDataService();
			$feedEntries = $feedSvc->getFeedEntries($this->id);
			foreach ($feedEntries as $entry){
				if ($entry['state'] == 2){
					$this->saved_entries[] = $entry['pubDate'];
				} else 	if ($entry['state'] == 0){
					$this->read_entries[] = $entry['pubDate'];
				}
			}
		}		
	}
	
	public static function getFeed($id){
		$feedSvc = new \Feader\FeaderDataService();
		$feedRow = $feedSvc->getFeed($id);
		
		$feed = new Feed($feedRow);
		return $feed;
	}
	
	public static function getFeeds($userId){
		$feedSvc = new \Feader\FeaderDataService();
		$feedRows = $feedSvc->getAllFeeds($userId);
		
		$feeds = array();
		foreach($feedRows as $feed){
			$feeds[$feed['id']] = new Feed($feed);
		}
		return $feeds;
	}
	
	public function UpdateFeed($feedObj){
		$url = ($this->url == $feedObj->url) ? null : $feedObj->url;
		$siteUrl = ($this->siteUrl == $feedObj->siteUrl) ? null : $feedObj->siteUrl;
		$name = ($this->name == $feedObj->name) ? null : $feedObj->name;
		$lastEntry = ($this->lastEntry == $feedObj->lastEntry) ? null : $feedObj->lastEntry;
		
		$feedSvc = new \Feader\FeaderDataService();
		return $feedSvc->updateFeed($feedObj->id,$url,$siteUrl,$name,$lastEntry);
	}
	
	public function UpdateFeedLastEntry($lastEntry){
		$feedSvc = new \Feader\FeaderDataService();
		$feedSvc->updateFeedLastEntry($this->id,$lastEntry);
	}
	
	public function __get($property) {
		$property = "_".$property;
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}

	public function __set($property, $value) {
		$property = "_".$property;
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}

		return $this;
	}
	
	
}