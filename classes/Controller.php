<?php
	class Controller {
		
		private $app;
		
		// default caching rules, cache any page for 5 minutes
		private $cacheRules = array(
			"cache" => false,
			"ttl" => 300
		);
		
		public function __construct(App $app){
			$this->app = $app;
		}
		
		protected function render($templatePath, array $parameters = array()){		
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
		
		public function setCacheRules(array $rules){
			$this->cacheRules = Util::array_empty_merge($this->cacheRules, $rules);
		}
		
		public function getCacheRule($rule){
			if (!isset($this->cacheRules[$rule])){
				throw new Exception(sprintf("Default cache rule '%s' not set.", $rule));
			}
			
			return $this->cacheRules[$rule];
		}
	}