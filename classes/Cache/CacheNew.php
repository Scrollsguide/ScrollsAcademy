<?php
	class CacheNew {
	
		private $driver;
	
		public function __construct(App $app, $driver = null){
			if ($driver === null){
				$driver = $app->getConfig()->get("cache_driver");
			}
			
			$this->initDriver($driver, $app);
		}
		
		private function initDriver($driver, App $app){
			$driverName = $driver . "Driver";
			
			if ($driver === "MySQL"){
				$this->driver = new $driverName(array(
					"connection" => $app->get("database")->getConnection()
				));
			} else {
				$this->driver = new $driverName();
			}
		}
		
		public function get($key){
			return $this->driver->get($key);
		}
		
		public function set($key, $value, $ttl = 300){
			return $this->driver->set($key, $value, $ttl);
		}
	
	}