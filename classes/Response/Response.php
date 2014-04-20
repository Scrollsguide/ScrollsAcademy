<?php
	class Response {
		
		private $statusCode = 200; // default status code
		
		public function __construct(){
			
		}
		
		public function process(){
			$this->outputHeaders();
			
			if ($this instanceof RedirectResponse){
				$this->setHeader("Location: " . $this->getTarget());
			}
		}
		
		public function setStatusCode($statusCode){
			$this->statusCode = $statusCode;
		}
		
		public function outputHeaders(){
			// add status code header
			$this->setHeader(StatusCode::getHeaderForStatuscode($this->statusCode));
		}
		
		public function setHeader($content){
			header($content);
		}

		public static function emptyResponse(){
			return new HtmlResponse();
		}
		
	}

	class ResponseContentTypes {

		public static $contentTypes = array(
			"css" => "text/css",
			"js" => "application/javascript"
		);

		public static function getContentType($t){
			return isset(self::$contentTypes[$t]) ? self::$contentTypes[$t] : $t;
		}

	}

	class StatusCode {
		
		public static $statusCodes = array(
			200 => "OK",
			301 => "Moved Permanently",
			302 => "Moved Temporarily",
			304 => "Not Modified",
			404 => "Not Found"
		);		
	
		public static function getHeaderForStatuscode($statusCode){
			if (!isset(self::$statusCodes[$statusCode])){
				throw new Exception(sprintf("Header for statuscode '%d' not defined in StatusCode class yet.", $statusCode));
			}
			return sprintf("%s %d %s", $_SERVER['SERVER_PROTOCOL'], $statusCode, self::$statusCodes[$statusCode]);
		}
	}