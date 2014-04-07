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
		
		public function getURL(){
			return $this->url;
		}
		
		public function getRequestMethod(){
			return $this->requestMethod;
		}
		
	}	