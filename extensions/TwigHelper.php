<?php
	class TwigHelper {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public static function registerHelpers(App $app, Twig_Environment $twig){
			$t = new self($app);
			
			// functions
			$twig->addFunction(new Twig_SimpleFunction("path", array($t, "path")));
			$twig->addFunction(new Twig_SimpleFunction("currentRoute", array($t, "currentRoute")));
			
			// filters
			$twig->addFilter(new Twig_SimpleFilter("cut", array($t, "cut")));
		}
		
		/**
		 * path() function in twig templates
		 * Usage: path("routeId", { "param1": "paramval" });
		 * routeParams is optional
		 */
		public function path($routeId, $routeParams = array()){
			return $this->app->getRouter()->generateUrl($routeId, $routeParams);
		}
		
		public function currentRoute(){
			return $this->app->getRoute();
		}
		
		public function cut($str, $length = 30, $toSpace = true, $last = "..."){
			if (strlen($str) <= $length){
				return $str;
			}
			
			if ($toSpace){
				if (($break = strpos($str, " ", $length)) !== false){
					$length = $break;
				}
			}
			
			return rtrim(substr($str, 0, $length)) . $last;
		}
		
	}