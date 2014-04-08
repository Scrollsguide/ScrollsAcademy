<?php
	class Router {
		
		private $app;
		
		private $routes = array();
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public function match($relUrl){
			$requestMethod = $this->app->getRequest()->getRequestMethod();
			foreach ($this->routes as $key => $route){
				if (preg_match($route['match'], $relUrl, $match)){
					// route matches, check for additional parameters
					if ($route['method'] === $requestMethod){
						$r = new Route($key, $route);
						
						// since we are only interested in the url parts and
						// not the complete regex result,
						// shift the complete match out of the array
						array_shift($match);
						$r->set("urlMatch", $match);
						
						return $r;
					}
				}
			}
			
			return $this->getRoute("404");
		}

		/**
		 * @param $id
		 * @return bool|Route
		 */
		public function getRoute($id){
			foreach ($this->routes as $key => $route){
				if ($key == $id){ // not === for json_decode can return ints
					return new Route($key, $route);
				}
			}
			
			return false;
		}
		
		public function addRouteFile($basePath, $filename){
			// check whether route file has already been compiled before
			$cache = $this->app->getCache();
			
			$cachePath = "Routing/" . $filename . ".php";
			$routePath = $basePath . "/" . $filename;
			if ($this->checkCacheValidity($cache, $cachePath, $routePath)){
				// cache is still valid, use this routing file
				
				$this->parseRouteFile($cache->getPathForFile($cachePath));
			} else {
				// cache is not valid anymore, compile routes again
				if (!is_file($routePath)){
					throw new Exception(sprintf("Route file '%s%' not found.", $routePath));
				}
				
				// RouteCompiler adds routes as they are parsed using Router::addRoute
				$routeCompiler = new RouteCompiler($routePath, $this);
				$compiled = $routeCompiler->toPHP($routeCompiler->compile());
				
				// write compiled php code to cache so it can be loaded faster next time
				$cache->save($cachePath, $compiled);
			}
		}
		
		private function parseRouteFile($path){
			require_once $path;
			
			// add routes from the route file to this router
			foreach ($routes as $key => $route){
				$this->addRoute($key, $route);
			}
		}
		
		public function addRoute($key, $route){
			if (isset($this->routes[$key])){
				throw new Exception(sprintf("Route '%s' already defined.", $key));
			}
			$this->routes[$key] = $route;
		}
		
		private function checkCacheValidity($cache, $cachePath, $routePath){
			if ($cache->exists($cachePath)){ // file has been created before
				// check whether route file has been modified since
				return filemtime($cache->getPathForFile($cachePath)) > filemtime($routePath);
			}
			
			return false;
		}
		
		/**
		 * Generates an url given a path id and a parameter list
		 * routeParams is optional
		 */
		public function generateUrl($routeId, $routeParams = array()){
			$routeUrl = ""; // return value
			
			if ($routeId === null){
				throw new Exception("'Path' needs at least one argument.");
			}
			
			// get route
			$route = $this->getRoute($routeId);
			
			if ($route === false){ // route does not exist
				throw new Exception(sprintf("No route for path '%s'.", $routeId));
			}
			
			if (count($route->get("paramMap")) > 0){ // this route needs parameters
				$requiredParameters = $route->getParameterNames();
				
				// check whether all parameters are present
				foreach ($requiredParameters as $rqParam){
					if (!isset($routeParams[$rqParam])){
						throw new Exception(sprintf("Parameter '%s' not set for route '%s'.", $rqParam, $routeId));
					}					
				}
				
				// everything's there, insert parameters
				$routeUrl = $route->insertParameters($routeParams);
			} else {
				$routeUrl = $route->get("path");
			}
			return $routeUrl;
		}
		
	}
	
	class RouteCompiler {
	
		private $router;
	
		private $filePath;
		
		private $requiredFields = array("path", "action");
	
		public function __construct($path, Router $router){
			$this->router = $router;
			$this->filePath = $path;
		}
		
		public function compile(){
			$json = $this->loadFromFile();
			
			if (!isset($json['routes'])){
				throw new Exception(sprintf("Missing key 'routes' in route file '%s'.", $this->filePath));
			}
			
			$routes = $json['routes'];
			
			foreach ($routes as $key => $route){
				// no need to check for duplicate routes since json_decode 
				// will auto-remove duplicates
				$r = $this->compileIndividual($key, $route);
				$routes[$key] = $r;
				
				$this->router->addRoute($key, $r);
			}
			
			return $routes;
		}
		
		public function toPHP($compiled){
			$total = "<?php \$routes = %s;";
			
			$routes = sprintf($total, $this->compilePHP($compiled));
			
			return $routes;
		}
		
		/**
		 * Recursively write php arrays to string
		 */
		private function compilePHP($item){
			$total = "";
			
			if (is_array($item)){
				$total .= "array(";
			
				$i = 0;
				$len = count($item);
				foreach ($item as $key => $value){
					if ($i > 0){ // first item doesn't need leading comma
						$total .= ",";
					}
					
					if (is_numeric($key)){
						$total .= $key;
					} else {
						$total .= "\"" . $key . "\"";
					}
					
					$total .= " => " . $this->compilePHP($value);
					$i++;
				}
				$total .= ")";
			} else {
				$total = "\"" . $item . "\"";
			}
			
			return $total;
		}
		
		/**
		 * Rewrites JSON to include regex patterns in route
		 */
		private function compileIndividual($id, $route){
			$this->simpleValidityCheck($id, $route);
			
			$hasRequirements = isset($route['requirements']);
		
			$route['paramMap'] = array();
			if (preg_match_all("#{([a-z0-9]+)}#i", $route['path'], $matches, PREG_SET_ORDER)){
				$matchWith = $route['path'];
				
				foreach ($matches as $m){
					$paramName = $m[1];
					
					$route['paramMap'][] = $paramName;
					
					if ($hasRequirements && isset($route['requirements'][$paramName])){
						$requirement = $route['requirements'][$paramName];
					} else {
						$requirement = "[a-zA-Z0-9-]*?";
					}
					$matchWith = str_replace(Route::wrapParameter($paramName), "(" . $requirement . ")", $matchWith);
				}
				
				$route['match'] = $this->wrapRegexDelimiter($matchWith);
			} else {
				$route['match'] = $this->wrapRegexDelimiter($route['path']);
			}
			
			// default to the GET method
			$route['method'] = isset($route['method']) ? $route['method'] : "GET";
			
			return $route;
		}
		
		private function simpleValidityCheck($id, $route){
			foreach ($this->requiredFields as $r){
				if (!isset($route[$r])){
					throw new Exception(sprintf("Field '%s' not set for route '%s'.", $r, $id));
				}
			}
			
			if (strpos($route['action'], "::") === false){
				throw new Exception(sprintf("No action defined for route '%s'.", $id));
			}
			// passed all simple checks
		}
		
		/**
		 * Maps parameters to their positions in the url
		 * so we can later pass them as arguments to controllers
		 */
		private function mapParameters(&$route){
			$route['paramMap'] = array();
			if (preg_match_all("#{([a-z0-9]+)}#i", $route['path'], $matches, PREG_SET_ORDER)){
				$paramCount = 0;
				
				print_r($matches);
				foreach ($matches as $m){
					$paramName = $m[1];
					
					$route['paramMap'][] = $paramName;
				}
			}
		}
		
		private function wrapRegexDelimiter($str){
			$possible = array("#", "~", "&", "%", "@");
			
			$i = 0;
			while (strpos($str, $possible[$i]) !== false){
				$i++;
			}
			
			return sprintf('%2$s^%1$s$%2$s', $str, $possible[$i]);
		}
		
		private function loadFromFile(){
			return json_decode(file_get_contents($this->filePath), true);
		}
		
	}