<?php
	class Request {
		
		private $app;
		
		private $url;
		
		public function __construct(App $app){
			$this->app = $app;
			
			$this->url = new URL();
			$this->url->setPath($_SERVER['REQUEST_URI']);
			$this->url->setHost($_SERVER['HTTP_HOST']);
			$this->url->setHTTPS(!empty($_SERVER['HTTPS']));
		}
		
		public function getURL(){
			return $this->url;
		}
		
	}	