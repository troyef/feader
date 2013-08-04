<?php

require_once "dbHelper.php";

class FeaderDataService{
	
	private $_configuration = null;
	private $_db = null;
	
	public function __construct($configuration) {
		$this->_configuration = $configuration;
		$this->_db = new DBHelper($configuration);
	}
	
	public function GetFeedList(){
		
		$query = "Select *,UNIX_TIMESTAMP(last) AS epoch_time from feeds";

		$feeds = $this->_db->executeQueryConnection($query);

		$rows = array();
		while($r = mysqli_fetch_assoc($feeds)) {
			$r['epoch_time'] = $r['epoch_time'] * 1000;//prepare for js date obj
		    $rows[$r['id']] = $r;
		}
		$jsonRows = json_encode($rows);
		
		return $jsonRows;
		
		
	}
	
	public function loadFromFile($path){
		echo $path . "<br/><br/>";
		$xml = simplexml_load_file($path);
		
		foreach( $xml->body->outline as $outline ) {
			if (!isset($outline['xmlUrl'])){
				$this->addTag($outline['title']);
				echo '&nbsp;&nbsp;'.$outline['title']."<br/>";
				foreach( $outline->outline as $sub_outline ) {
					$feedId = $this->addFeed($sub_outline['xmlUrl'],$sub_outline['htmlUrl'],$sub_outline['title'],null);
					$this->addFeedTag($feedId,$outline['title']);
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$sub_outline['title']."<br/>";
				}
			} else {
				$this->addFeed($outline['xmlUrl'],$outline['htmlUrl'],$outline['title'],null);
				echo $outline['title']."<br/>";
			}
			
		} 
		
		

		//var_dump($xml->body);
		echo "<br/><br/>";

		//$json = json_encode($xml);
		
		//echo $json->body;
		//var_dump($json);
		
	}
	
	private function addTag($tagName){
		
		$this->_db->createConnection();
		$query = "SELECT * FROM tags WHERE name = '$tagName';";
		$result = $this->_db->executeQuery($query); 
		
		if (mysqli_num_rows($result) == 0){
			$insertQuery = "INSERT INTO tags SET name = '$tagName';";
			$this->_db->executeQuery($insertQuery); 
			$result = $this->_db->executeQuery($query);
		}
		$this->_db->closeConnection();
	

	}
	private function addFeed($feedUrl, $siteUrl = "",$name="",$lastUpdate = null){
		$this->_db->createConnection();
		$query = "SELECT * FROM feeds WHERE url = '$feedUrl';";
		$result = $this->_db->executeQuery($query); 
		$id = uniqid();
		
		if (mysqli_num_rows($result) == 0){
			
			$lastUpdate = ($lastUpdate == null) ? "DATE(NOW()) - INTERVAL 7 DAY" : $lastUpdate;
			
			$name = (strlen($name) > 50) ? substr($name,0,50) : $name; 
			
			$insertQuery = "INSERT INTO feeds (id,url,pageUrl,name,last) VALUES ('$id','$feedUrl' ,'$siteUrl' , \"$name\", $lastUpdate)";
			$result = $this->_db->executeQuery($insertQuery); 
		} else {
			$r = mysqli_fetch_assoc($result);
			$id = $r['id'];
		}
		$this->_db->closeConnection();
		
		return $id;
	}
	
	private function addFeedTag($feedId,$tagName){
		
		$this->_db->createConnection();
		
		$query = "SELECT * FROM tags WHERE name = '$tagName';";
		$result = $this->_db->executeQuery($query); 
		
		if (mysqli_num_rows($result) == 0){
			$insertQuery = "INSERT INTO tags SET name = '$tagName';";
			$this->_db->executeQuery($insertQuery); 
			$result = $this->_db->executeQuery($query);
		}
		$r = mysqli_fetch_assoc($result);
		$tag_id = $r['id'];
		
		$query = "SELECT * FROM feed_tag WHERE feed_id = '$feedId' AND tag_id = '$tag_id';";
		$result = $this->_db->executeQuery($query); 
		
		if (mysqli_num_rows($result) == 0){
			$insertQuery = "INSERT INTO feed_tag (feed_id,tag_id) VALUES ('$feedId','$tag_id');";
			$this->_db->executeQuery($insertQuery); 
			$result = $this->_db->executeQuery($query);
		}
		
		
		$this->_db->closeConnection();
	

	}
	
}