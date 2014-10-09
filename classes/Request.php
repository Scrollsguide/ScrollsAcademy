<?php

	class Request {

		private $url;

		private $requestMethod;

		private $isAjax = false;

		private $params = array();

		public function __construct() {
			$this->url = new URL();

			$requestUri = $_SERVER['REQUEST_URI'];
			$scriptName = $_SERVER['SCRIPT_NAME'];

			// make sure the debugging paths route to the same controllers,
			// index_dev.php/path === /path
			if (Debug::started() && substr($requestUri, 0, strlen($scriptName)) === $scriptName) {
				$requestUri = substr($requestUri, strlen($scriptName));
			}

			$this->url->setPath($requestUri);
			$this->url->setHost($_SERVER['HTTP_HOST']);
			$this->url->setHTTPS(!empty($_SERVER['HTTPS']));

			$this->requestMethod = $_SERVER['REQUEST_METHOD'];
			$this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		}

		/**
		 * @return URL
		 */
		public function getURL() {
			return $this->url;
		}

		public function getRequestMethod() {
			return $this->requestMethod;
		}

		public function getFiles() {
			return $this->params["FILES"];
		}

		public function isAjax() {
			return $this->isAjax;
		}

		public function putParameter($param, $value, $method = "GET") {
			if (!isset($this->params[$method])) {
				$this->params[$method] = array();
			}

			$this->params[$method][$param] = $value;
		}

		public function getParameter($param, $default = "", $method = null) {
			if ($method === null) {
				$method = $this->getRequestMethod();
			}

			if (!isset($this->params[$method]) || !isset($this->params[$method][$param])) {
				return $default;
			}

			return $this->params[$method][$param];
		}

		/**
		 * @return Request
		 */
		public static function createFromServer() {
			$r = new Request();

			// insert POST, GET and FILES parameters
			foreach ($_GET as $key => $value) {
				$r->putParameter($key, $value, "GET");
			}
			unset($_GET);
			foreach ($_POST as $key => $value) {
				$r->putParameter($key, $value, "POST");
			}
			unset($_POST);
			foreach ($_FILES as $key => $value) {
				$r->putParameter($key, $value, "FILES");
			}
			unset($_FILES);

			return $r;
		}

	}	