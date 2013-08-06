<?php

namespace Feader\Model;

class User{
	
	public $id;
	public $showSnippet = false;
	public $removeOnClose = false;
	public $entriesPerFeed = 50;
	public $viewOneEntryAtATime = true;
	
	
	public function __construct($userObj = null) {
		
		if (null != $userObj){
			$this->id = $userObj['id'];
			$this->showSnippet = ($userObj['showSnippet'] == 1);
			$this->removeOnClose = ($userObj['removeOnClose'] == 1);
			$this->entriesPerFeed = $userObj['entriesPerFeed'];
			$this->viewOneEntryAtATime = ($userObj['viewOneEntryAtATime'] == 1);
			
			
		}		
	}
	
	public static function getUser($id){
		$feedSvc = new \Feader\FeaderDataService();
		$userRow = $feedSvc->getUser($id);
		
		$user = new User($userRow);
		return $user;
	}
	
	public static function findUser($vendorId){
		
		$feedSvc = new \Feader\FeaderDataService();
		$userRow = $feedSvc->findUser($vendorId);
		
		$user = new User($userRow);
		
		if ($user->id == null && $feedSvc->getUserCount() <= 100){
			$userRow = $feedSvc->createGetUser($vendorId);
			$user = new User($userRow);
		}
		
		
		return $user;
	}
	
	
}