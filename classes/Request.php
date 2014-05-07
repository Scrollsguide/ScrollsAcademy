<?php

	class Request {

		private $url;

		private $requestMethod;

		private $params = array();

		public function __construct() {
			$this->url = new URL();
			$this->url->setPath($_SERVER['REQUEST_URI']);
			$this->url->setHost($_SERVER['HTTP_HOST']);
			$this->url->setHTTPS(!empty($_SERVER['HTTPS']));

			$this->requestMethod = $_SERVER['REQUEST_METHOD'];
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

		public function putParameter($param, $value, $method = "GET") {
			if (!isset($this->params[$method])) {
				$this->params[$method] = array();
			}

			$this->params[$method][$param] = $value;
		}

		public function getParameter($param, $default = "", $method = null) {
			if ($method === null){
				$method = $this->getRequestMethod();
			}

			if (!isset($this->params[$method]) || !isset($this->params[$method][$param])){
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