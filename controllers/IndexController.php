<?php
	class IndexController extends Controller {
		
		public function indexAction(){
			return $this->render("index.html", array(
				"data" => "twigthing"
			));
		}
		
		public function redirectTest(){
			$toUrl = $this->getApp()->getRouter()->generateUrl("categories", array("categoryType" => "beginner"));
			
			return $this->redirect($toUrl);
		}
		
		public function p404Action(){
			return $this->render("404.html");
		}
	}