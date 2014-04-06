<?php
	class TwigHelper {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public static function registerHelpers(App $app, Twig_Environment $twig){
			$t = new self($app);
			
			$twig->addFunction($t->getPathFunction());
			$twig->addFunction($t->getCurrentRouteFunction());
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
			return $this->app->getRouter()->generateUrl($routeId, $routeParams);
		}
		
		public function getCurrentRouteFunction(){
			return new Twig_SimpleFunction("currentRoute", array($this, "currentRoute"));
		}
		
		public function currentRoute(){
			return $this->app->getRoute();
		}
		
	}