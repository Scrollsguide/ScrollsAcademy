<?php
	class TwigHelper {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public function getPathFunction(){
			return new Twig_SimpleFunction("path", array($this, "path"));
		}
		
		/**
		 * path() function in twig templates
		 * Usage: path("routeId", { "param1": "paramval" });
		 * routeParams is optional
		 */
		public function path($routeId, $routeParams = array()){
			$routeUrl = ""; // return value
			
			if ($routeId === null){
				throw new Exception("'Path' needs at least one argument.");
			}
			
			// get route from router
			$route = $this->app->getRouter()->getRoute($routeId);
			
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