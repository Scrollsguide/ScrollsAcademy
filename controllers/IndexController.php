<?php
	class IndexController extends Controller {
		
		public function indexAction(){
			return $this->redirectTest();
			/*
			return $this->render("index.html", array(
				"data" => "twigthing"
			));*/
		}
		
		public function redirectTest(){
			$toUrl = $this->getApp()->getRouter()->generateUrl("categories", array("categoryType" => "beginner"));
			
			return $this->redirect($toUrl);
		}
		
	}