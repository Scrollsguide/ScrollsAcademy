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
			$this->classloader->addDirectory("controllers");
			$this->classloader->addDirectory("extensions");
			
			// set up cache
			$this->cache = new Cache($this->baseDir . "/cache");
			
			// read config files
			$this->config = new Config();
			$this->config->addConfigFile($this->baseDir . "/config/db.ini");
			
			// set up router
			$this->router = new Router($this);
			$this->router->addRouteFile($this->baseDir . "/config", "routes.json");
			$this->router->addRouteFile($this->baseDir . "/config", "admin_routes.json");
		}
		
		public function run(){
			// retrieve matching route, if any
			$this->route = $this->matchRoute();
			
			// check cache here, load controller if cache cannot be used
			$cacheKey = "Pages/" . RouteHelper::getCacheKey($this->getRoute());
			$usedCache = false;
			if ($this->getCache()->exists($cacheKey)){
				$contentFromCache = $this->getCache()->load($cacheKey);
				if ($contentFromCache !== false){ // successfully loaded from cache
					$usedCache = true;
					
					echo $contentFromCache;
				}
			}
			
			if (!$usedCache){
				$this->setupTemplateEngine();
				$this->setupController();
				
				// returns instance of Response class
				$response = $this->runAction();
				
				// check response type and process
				if (!($response instanceof Response)){
					throw new Exception(sprintf("Action '%s' should return Response.", $this->route->getActionName()));
				}
				
				$response->process();
				
				// cache the response if necessary
				// and output the page if it has content
				if ($response instanceof ContentResponse){
					$this->tryCache($response, $cacheKey);
					
					echo $response->getContent();
				}
			}
		}
		
		private function setupTemplateEngine(){
			$this->getClassloader()->tryRequire($this->getBaseDir() . "/libs/Twig/lib/Twig/Autoloader.php");
			Twig_Autoloader::register();
			
			$loader = new Twig_Loader_Filesystem($this->getBaseDir() . "/views");
			
			// set up cache for twig
			$this->getCache()->prepareDirectory($this->getCache()->getPathForFile("TwigViews"));
			$twig = new Twig_Environment($loader, array(
				"cache" => $this->getCache()->getPathForFile("TwigViews"),
				"auto_reload" => true
			));
			
			// add extensions
			TwigHelper::registerHelpers($this, $twig);
			
			$this->put("twig", $twig);
		}
		
		private function setupController(){
			$this->controller = $this->getControllerForRoute($this->route);
		}
		
		private function runAction(){
			return call_user_func_array(array($this->controller, $this->route->getActionName()), $this->route->getUrlParameters());
		}
		
		private function tryCache(ContentResponse $response, $location){
			$this->getCache()->save($location, $response->getContent());
		}
		
		private function matchRoute(){
			$requestUrl = new RelativeURL($_SERVER['REQUEST_URI']);
			
			// now try matching it against any route
			// exlude get parameters, pass false
			$relPath = $requestUrl->getPath(false);
			return $this->router->match($relPath);
		}
		
		private function getControllerForRoute(Route $route){
			// load the controller
			// the classloader checks whether the class exists or not
			$fullControllerName = $route->getControllerName();
			$this->classloader->loadClass($fullControllerName);
			$controller = new $fullControllerName($this);
			
			// now check whether the action is available in the controller
			$fullActionName = $route->getActionname();
			if (!method_exists($controller, $fullActionName)){
				throw new Exception(sprintf("Action '%s' not found in '%s'.", $fullActionName, $fullControllerName));
			}
			
			// everything seems to be in order, return controller
			return $controller;
		}
		
		public function getBaseDir(){
			return $this->baseDir;
		}
		
		public function getRouter(){
			return $this->router;
		}
		
		public function getRoute(){
			return $this->route;
		}
		
		public function getClassloader(){
			return $this->classloader;
		}
		
		public function getTwig(){
			return $this->twig;
		}
		
		public function getCache(){
			return $this->cache;
		}
		
		public function put($key, $obj){
			$this->optObjects[$key] = $obj;
		}
		
		public function get($obj){
			return $this->optObjects[$obj];
		}
	}