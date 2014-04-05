<?php
	class App {
		
		private $baseDir;
		
		private $config;
		
		private $router;
		
		private $cache;
		
		private $classloader;
		
		private $route;
		
		private $controller;
		
		// array of objects not used for every request, for example the database object
		private $optObjects;
		
		public function __construct($baseDir){
			$this->baseDir = $baseDir;
		}
		
		public function setClassloader(Autoloader $autoloader){
			$this->classloader = $autoloader;
		}
		
		public function init(){
			// add user controller directory to classloader
			$this->classloader->add("controllers");
			
			// set up cache
			$this->cache = new Cache($this->baseDir . "/cache");
			
			// read config files
			$this->config = new Config();
			$this->config->addConfigFile($this->baseDir . "/config/db.ini");
			
			// set up router
			$this->router = new Router($this);
			$this->router->addRouteFile($this->baseDir . "/config", "routes.json");
		}
		
		public function run(){
			// retrieve matching route, if any
			$this->matchRoute();
			
			$this->setupController();
			
			$this->runAction();
		}
		
		private function setupController(){
			$this->controller = $this->getControllerForRoute($this->route);
		}
		
		private function runAction(){
			call_user_func_array(array($this->controller, $this->route->getActionName()), $this->route->getUrlParameters());
		}
		
		private function matchRoute(){
			$requestUrl = new RelativeURL($_SERVER['REQUEST_URI']);
			
			// now try matching it against any route
			$relPath = $requestUrl->getPath(false);
			$this->route = $this->router->match($relPath);
			
			return $this->route;
		}
		
		private function getControllerForRoute(Route $route){
			// load the controller
			// the classloader checks whether the class exists or not
			$fullControllerName = $route->getControllerName();
			$this->classloader->loadClass($fullControllerName);
			$controller = new $fullControllerName();
			
			// now check whether the action is available in the controller
			$fullActionName = $route->getActionname();
			if (!method_exists($controller, $fullActionName)){
				throw new Exception(sprintf("Action '%s' not found in '%s'.", $fullActionName, $fullControllerName));
			}
			
			// everything seems to be in order, return controller
			return $controller;
		}
		
		public function getCache(){
			return $this->cache;
		}
	}