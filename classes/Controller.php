<?php
	class Controller {
		
		private $app;
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		protected function render($templatePath, array $parameters){		
			$twig = $this->app->get("twig");
			$template = $twig->loadTemplate($templatePath);
			
			$response = new HtmlResponse();
			$response->setContent($template->render($parameters));
			return $response;
		}
		
		protected function getApp(){
			return $this->app;
		}
		
		protected function redirect($toUrl, $statusCode = 301){ // moved permanently for default
			$redirectResponse = new RedirectResponse($toUrl);
			$redirectResponse->setStatusCode($statusCode);
			
			return $redirectResponse;
		}
	
	}