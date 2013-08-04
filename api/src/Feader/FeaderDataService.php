<?php
namespace Feader;

class FeaderDataService{
	
	private $_configuration;
	private $_db = null;
	
	public function __construct() {
		global $CFG;
		$this->_configuration = $CFG;
		$this->_db = new \Feader\DBHelper($this->_configuration);
	}
	
	public function getUser($id){
		
		$query = "Select * from users where id = '$id';";

		$rows = $this->_db->executeQueryConnection($query);
		return mysqli_fetch_assoc($rows);
		
	}
	
	public function findUser($vendorId){
		
		$query = "Select u.* from users u inner join vendor_users vu on u.id = vu.user_id where vu.vendorId = '$vendorId';";
		$rows = $this->_db->executeQueryConnection($query);
		return mysqli_fetch_assoc($rows);
		
	}
	
	
	public function getAllFeeds($userId){
		
		$query = "Select * from feeds where user_id = '$userId';";

		$feeds = $this->_db->executeQueryConnection($query);

		$rows = array();
		while($r = mysqli_fetch_assoc($feeds)) {
			$rows[] = $r;
		}
		return $rows;
		
	}
	
	public function getFeed($id){
		
		$query = "Select * from feeds where id = '$id';";

		$rows = $this->_db->executeQueryConnection($query);
		return mysqli_fetch_assoc($rows);
		
	}
	
	public function updateFeed($id, $url = null, $siteUrl = null, $name=null, $lastEntry = null){
		$query = "UPDATE feeds SET ";

		$queryUpdates = array();
		if ($url != null)
			$queryUpdates[] = "url = '$url'";
		if ($siteUrl != null)
			$queryUpdates[] = "siteUrl = '$siteUrl'";
		if ($name != null)
			$queryUpdates[] = "name = '$name'";
		if ($lastEntry != null)
			$queryUpdates[] = "lastEntry = $lastEntry";
		
		if (count($queryUpdates) > 0){
			$query = "UPDATE feeds SET ".join(",", $queryUpdates). " WHERE id = '$id'";
			$this->_db->executeQueryConnection($query);
			return true;
		} else {
			return false;
		}
		
		
		
	}
	
	public function getFeedEntries($feedId){

		$query = "SELECT * FROM feed_entries WHERE feedId = '$feedId';";
		$results = $this->_db->executeQueryConnection($query); 
		
		$rows = array();
		while($r = mysqli_fetch_assoc($results)) {
			$rows[] = $r;
		}
		return $rows;
		
	}
	
	public function addFeedEntry($feedId, $pubDate, $state){
		$this->_db->createConnection();
		$query = "SELECT * FROM feed_entries WHERE feedId = '$feedId' and pubDate = $pubDate;";
		$result = $this->_db->executeQuery($query); 
		
		if (mysqli_num_rows($result) == 0){
			$insertQuery = "INSERT INTO feed_entries (feedId,pubDate,state) VALUES ('$feedId',$pubDate,$state);";
			$this->_db->executeQuery($insertQuery); 
		} else {
			$updateQuery = "UPDATE feed_entries set state = $state WHERE feedId = '$feedId' and pubDate = $pubDate;";
			$this->_db->executeQuery($updateQuery);
		}
		
		$this->_db->closeConnection();
	}
	
	public Function updateFeedLastEntry($feedId,$lastEntry){
		$this->_db->createConnection();
		
		$query = "UPDATE feeds SET lastEntry = $lastEntry WHERE id = '$feedId';";
		$this->_db->executeQuery($query);
		
		$query = "DELETE FROM feed_entries WHERE feedId = '$feedId' and ((pubDate < $lastEntry AND state < 1) || pubDate = $lastEntry);";
		$this->_db->executeQuery($query);
		
		$this->_db->closeConnection();
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
	public function addFeed($userId, $feedUrl, $siteUrl = "",$name="",$lastUpdate = null){
		$this->_db->createConnection();
		$query = "SELECT * FROM feeds WHERE url = '$feedUrl' and user_id = '$userId';";
		$result = $this->_db->executeQuery($query); 
		$id = uniqid();
		
		if (mysqli_num_rows($result) == 0){
			
			$lastUpdate = ($lastUpdate == null) ? (time() - (30 * 60 * 60 * 24)) * 1000 : $lastUpdate;
			
			$name = (strlen($name) > 50) ? substr($name,0,50) : $name; 
			
			$insertQuery = "INSERT INTO feeds (user_id,id,url,siteUrl,name,lastEntry) VALUES ('$userId','$id','$feedUrl' ,'$siteUrl' , \"$name\", $lastUpdate)";
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
}