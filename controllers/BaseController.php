<?php

	class BaseController extends Controller {
	
		private $cache;
	
		public function __construct(App $app) {
			parent::__construct($app);
			
			$this->setCacheRules(array(
				"cache" => false
			));
			
			$this->cache = new CacheNew($app, "MySQL");
		}
		
		protected function getCache(){
			return $this->cache;
		}
		
		protected function render($templatePath, array $parameters = array(), $statusCode = 200) {
			// add default parameters for every page
			$parameters['title'] = $this->getPageTitle($parameters);

			$twig = $this->getApp()->get("twig");
			$template = $twig->loadTemplate($templatePath);

			$response = new HtmlResponse();
			$response->setStatusCode($statusCode);
			$response->setContent($template->render($parameters));

			return $response;
		}
		
		// redirects to admin login page
		protected function toLogin() {
			$loginRoute = $this->getApp()->getRouter()->getRoute("login");

			return new RedirectResponse($loginRoute->get("path"));
		}

		public function p404(){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			//get the random guides for the recommended
			$recommendedGuides = array();
			$recommendedGuides[] = $guideRepository->findRandomByCategory("beginner");
			$recommendedGuides[] = $guideRepository->findRandomByCategory("intermediate");
			$recommendedGuides[] = $guideRepository->findRandomByCategory("master");

			// remove empty guides
			$recommendedGuides = array_filter($recommendedGuides, function($e){ return $e !== false; });

			return $this->render("404.html", array(
				'title' => 'Page not found',
				'recommendedGuides' => $recommendedGuides
			), 404);
		}

		private function getPageTitle($parameters = array()) {
			if (isset($parameters['title'])) {
				return $parameters['title'] . " - Scrolls Academy";
			}

			return "Scrolls Academy";
		}

	}