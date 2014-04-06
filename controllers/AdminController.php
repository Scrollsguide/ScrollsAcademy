<?php
	class AdminController extends Controller {
		
		private $user;
		
		private $session;
		
		public function __construct(){
			// don't cache the admin pages
			$this->setCacheRules(array(
				"cache" => false
			));
			
			// set up session
			$this->session = Session::getInstance();
			
			// check privileges
			$this->user = new User();
		}
		
		public function indexAction(){
			$response = new HtmlResponse();
			return $response;
		}		
	}