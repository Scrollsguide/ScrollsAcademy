<?php
	class MySQLDriver extends CacheDriver {
	
		public function getApp(){
			return $this->app;
		}
	
		public function get($key){
			$conn = $this->getOption("connection");
			
			$stmt = $conn->prepare("SELECT object, expires 
						FROM caching 
						WHERE `key` = :key");
			$stmt->bindValue(":key", $key, PDO::PARAM_STR);
			
			$stmt->execute();
			
			if (($queryResult = $stmt->fetch(PDO::FETCH_ASSOC)) !== false){
				if ($queryResult['expires'] > time()){
					return $this->decode($queryResult['object']);
				}
			}
			
			return null;
		}
	
		public function set($key, $value, $ttl = 300){
			$setQuery = "`key` = :key, 
						object = :object,
						expires = :expires";
		
			$conn = $this->getOption("connection");
			
			$stmt = $conn->prepare("INSERT INTO caching 
						SET " . $setQuery . "
						ON DUPLICATE KEY UPDATE " . $setQuery);
			$stmt->bindValue(":key", $key, PDO::PARAM_STR);
			$stmt->bindValue(":object", $this->encode($value), PDO::PARAM_LOB);
			$stmt->bindValue(":expires", time() + $ttl, PDO::PARAM_INT);
			
			$stmt->execute();
		}
	
		public function delete($key){
		
		}
	
		public function clean(){
		
		}
		
		private function encode($object){
			return serialize($object);
		}
		
		private function decode($object){
			return unserialize($object);
		}
	
	}