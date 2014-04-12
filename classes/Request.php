<?php
	class Request {
		
		private $url;
		
		private $requestMethod;
		
		public function __construct(){			
		
			$this->url = new URL();
			$this->url->setPath($_SERVER['REQUEST_URI']);
			$this->url->setHost($_SERVER['HTTP_HOST']);
			$this->url->setHTTPS(!empty($_SERVER['HTTPS']));
			
			$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		}

		/**
		 * @return URL
		 */
		public function getURL(){
			return $this->url;
		}
		
		public function getRequestMethod(){
			return $this->requestMethod;
		}

		public function getFiles() {
			return $_FILES;
		}
		
		public function getParameter($param, $default = ""){
			if ($this->getRequestMethod() === "POST"){
				if (isset($_POST[$param])){
					return $_POST[$param];
				} else {
					return $default;
				}
			} else if ($this->getRequestMethod() === "GET"){
				if (isset($_GET[$param])){
					return $_GET[$param];
				} else {
					return $default;
				}
			}
		}
		
	}	