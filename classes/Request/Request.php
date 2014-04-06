<?php
	class Request {
		
		private $statusCode = 200; // default status code
		
		public function setStatusCode($statusCode){
			$this->statusCode = $statusCode;
		}
		
	}