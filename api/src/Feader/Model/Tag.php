<?php

namespace Feader\Model;

class Tag{
	
	private $_id;
	private $_name;
	
	public function __construct($tagObj = null) {
		if (null != $tagObj){
			$this->_id = $tagObj->id;
			$this->_name = $tagObj->name;
		}		
	}
	
	public static function getFeed($id){
		$feedSvc = new \Feader\FeaderDataService($CFG);
		$feedRow = $feedSvc->getFeed($id);
		
		$feed = new FeedModel($feedRow);
		return $feed;
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