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
			$router = $this->app->getRouter();
			
			return $router->generateUrl($routeId, $routeParams);
		}
		
	}