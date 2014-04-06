<?php
	class AdminController extends Controller {
		
		public function __construct(){
			// don't cache the admin pages
			$this->setCacheRules(array(
				"cache" => false
			));			
		}
		
		public function indexAction(){
			$response = new HtmlResponse();
			return $response;
		}		
	}