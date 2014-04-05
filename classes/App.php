<?php
	class App {
		
		private $baseDir;
		
		private $config;
		
		private $router;
		
		private $cache;
		
		// array of objects not used for every request, for example the database object
		private $optObjects;
		
		public function __construct($baseDir){
			$this->baseDir = $baseDir;
		}
		
		public function init(){
			$this->cache = new Cache($this->baseDir . "/cache");
		
			$this->config = new Config();
			$this->config->addConfigFile($this->baseDir . "/config/db.ini");
			
			$this->router = new Router($this);
			$this->router->addRouteFile($this->baseDir . "/config", "routes.json");
		}
		
		public function run(){
			$this->setupController();
		}
		
		private function setupController(){
			$requestUrl = new RelativeURL($_SERVER['REQUEST_URI']);
			
			// now try matching it against any route
			$relPath = $requestUrl->getPath(false);
			$route = $this->router->match($relPath);
			
			echo $route->getId();
		}
		
		public function getCache(){
			return $this->cache;
		}
	}