<?php
	class Controller {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		public function render($templatePath, array $parameters){		
			$twig = $this->app->get("twig");
			$template = $twig->loadTemplate($templatePath);
			
			return $template->render($parameters);
		}
	
	}