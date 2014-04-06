<?php
	class Controller {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		protected function render($templatePath, array $parameters){		
			$twig = $this->app->get("twig");
			$template = $twig->loadTemplate($templatePath);
			
			return $template->render($parameters);
		}
		
		protected function getApp(){
			return $this->app;
		}
		
		protected function redirect($toUrl, $statusCode = 302){
			header("Location: " . $toUrl);
		}
	
	}