<?php

	class BaseController extends Controller {

		protected function render($templatePath, array $parameters = array()) {
			// add default parameters for every page
			$parameters['title'] = $this->getPageTitle($parameters);

			$twig = $this->getApp()->get("twig");
			$template = $twig->loadTemplate($templatePath);

			$response = new HtmlResponse();
			$response->setContent($template->render($parameters));

			return $response;
		}

		private function getPageTitle($parameters = array()) {
			if (isset($parameters['title'])) {
				return $parameters['title'] . " - Scrolls Academy";
			}

			return "Scrolls Academy";
		}

	}