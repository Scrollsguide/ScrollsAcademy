<?php

	class Controller {

		private $app;

		// default caching rules, cache any page for 10 minutes with html content type
		private $cacheRules = array(
			"cache"       => true,
			"ttl"         => 600,
			"contentType" => "html",
			"statusCode"  => 200
		);

		public function __construct(App $app) {
			$this->app = $app;
		}

		/**
		 * @param $templatePath
		 * @param array $parameters
		 * @return HtmlResponse
		 */
		protected function render($templatePath, array $parameters = array()) {
			$twig = $this->app->get("twig");
			$template = $twig->loadTemplate($templatePath);

			$response = new HtmlResponse();
			$response->setContent($template->render($parameters));

			return $response;
		}

		/**
		 * @return App
		 */
		protected function getApp() {
			return $this->app;
		}

		/**
		 * @param $toUrl
		 * @param int $statusCode
		 * @return RedirectResponse
		 */
		protected function redirect($toUrl, $statusCode = 301) { // moved permanently for default
			$redirectResponse = new RedirectResponse($toUrl);
			$redirectResponse->setStatusCode($statusCode);

			return $redirectResponse;
		}

		public function setCacheRules(array $rules) {
			$this->cacheRules = Util::array_empty_merge($this->cacheRules, $rules);
		}

		public function getCacheRule($rule) {
			if (!isset($this->cacheRules[$rule])) {
				throw new Exception(sprintf("Default cache rule '%s' not set.", $rule));
			}

			return $this->cacheRules[$rule];
		}
	}