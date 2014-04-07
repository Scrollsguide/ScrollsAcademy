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
			$twig->addFunction(new Twig_SimpleFunction("fullPath", array($t, "fullPath")));
			$twig->addFunction(new Twig_SimpleFunction("currentRoute", array($t, "currentRoute")));
			$twig->addFunction(new Twig_SimpleFunction("categoryIcon", array($t, "categoryIcon")));
			$twig->addFunction(new Twig_SimpleFunction("imagePath", array($t, "imagePath")));
			$twig->addFunction(new Twig_SimpleFunction("categoryClass", array($t, "categoryClass")));
			
			// filters
			$twig->addFilter(new Twig_SimpleFilter("cut", array($t, "cut")));

			//set the timezone so the date helper can work properly
			$twig->getExtension('core')->setTimezone('Europe/Paris');
		}
		
		/**
		 * path() function in twig templates
		 * Usage: path("routeId", { "param1": "paramval" });
		 * routeParams is optional
		 */
		public function path($routeId, $routeParams = array()){
			return $this->app->getRouter()->generateUrl($routeId, $routeParams);
		}

		//todo - replace with proper get of domain
		public function fullPath($routeId, $routeParams = array()) {
			return 'http://siteddomain' . $this->path($routeId,$routeParams);
		}
		
		public function currentRoute(){
			return $this->app->getRoute();
		}

		public function imagePath($filename) {
			return '/assets/images/' . $filename;
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

		/*
			Generates the font-awesome icon for a corresponding category
		*/
		public function categoryIcon($category) {
			$category = strtolower($category);
			switch ($category) {
				case 'about':
					return 'exclamation';
				break;

				case 'beginner':
					return 'cogs';
				break;
				case 'intermediate':
					return 'star';
				break;
				case 'master':
					return 'trophy';
				break;

				case 'video':
					return 'video-camera';
				break;
				case 'strategy':
					return 'puzzle-piece';
				break;
				case 'decks':
					return 'inbox';
				break;
				case 'judgement':
					return 'flag';
				break;
				case 'ui':
					return 'laptop';
				break;
				case '404':
					return 'exclamation';
				break;
			}

			return ''; //no icon found
		}	
	}