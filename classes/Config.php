<?php
	class Config {

		const PDO_HOST = "pdo_host";
		const PDO_PORT = "pdo_port";
		const PDO_USER = "pdo_user";
		const PDO_PASS = "pdo_pass";
		const PDO_DB = "pdo_db";

		const CDN_DOMAIN = "cdn_domain";

		// array containing all config values
		private $c = array();
	
		public function __construct(){
		}
		
		public function addConfigFile($path){
			if (!is_array($this->c)){
				$this->c = array();
			}
			$newConfig = parse_ini_file($path);
			
			// merge with existing conf
			$this->c = Util::array_empty_merge($this->c, $newConfig);
		}
		
		/**
		 * Returns whether or not a key exists in the current configuration
		 */
		public function exists($key){
			return isset($this->c[$key]);
		}
		
		public function set($key, $value){
			if ($value === null){
				throw new Exception(sprintf("Attempting to set null value for '%s'.", $key));
			}
			$this->c[$key] = $value;
		}
		
		public function get($key){
			if (!$this->exists($key)){
				throw new Exception(sprintf("'%s' not set in config.", $key));
			}
			return $this->c[$key];
		}
		
	}